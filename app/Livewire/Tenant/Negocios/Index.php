<?php

namespace App\Livewire\Tenant\Negocios;

use App\Models\Central\IntercompanyInvoice;
use App\Models\Central\IntercompanyInvoiceItem;
use App\Models\Central\PortafolioItem;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\Account;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\CompanyConfig;
use App\Services\IntercompanyService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Negocios')]
class Index extends Component
{
    // ── Tab activa ────────────────────────────────────────────────────────────
    public string $tab = 'comprar';

    // ── Formulario: nuevo pedido (buyer-initiated) ────────────────────────────
    public string $seller_id        = '';   // empresa vendedora seleccionada
    public string $concepto         = '';
    public string $gasto_code       = '5195';
    public bool   $aplica_retencion = false;
    public bool   $aplica_reteiva   = false;
    public bool   $aplica_reteica   = false;
    public array  $items            = [];
    public ?int   $buyer_bank_account_id = null;  // cuenta desde donde se paga (null = crédito/CxP)

    // ── Modal: confirmar venta (seller side) ─────────────────────────────────
    public bool  $showConfirmModal = false;
    public ?int  $confirmingId     = null;

    // ── Modal: rechazar ───────────────────────────────────────────────────────
    public bool   $showRejectModal = false;
    public ?int   $rejectingId     = null;
    public string $rechazo_motivo  = '';

    // ── Portafolio propio ─────────────────────────────────────────────────────
    public bool   $showPortafolioForm  = false;
    public ?int   $editingPortafolioId = null;
    public string $p_nombre            = '';
    public string $p_descripcion       = '';
    public string $p_tipo              = 'producto';
    public string $p_precio            = '';
    public string $p_iva               = '19';
    public string $p_cuenta_codigo     = '';
    public string $p_cuenta_nombre     = '';

    // ── Actualiza portafolio al cambiar vendedor ──────────────────────────────

    public function updatedSellerId(): void
    {
        $this->items = [];
    }

    // ── Agregar ítem del portafolio del vendedor al pedido ────────────────────

    public function addItemFromPortafolio(int $portafolioItemId): void
    {
        $item = PortafolioItem::find($portafolioItemId);
        if (! $item || $item->tenant_id !== $this->seller_id) return;

        // Si ya existe el ítem, incrementar cantidad
        foreach ($this->items as $idx => $existing) {
            if (($existing['portafolio_item_id'] ?? null) === $item->id) {
                $this->items[$idx]['cantidad']++;
                return;
            }
        }

        $this->items[] = [
            'portafolio_item_id'    => $item->id,
            'descripcion'           => $item->nombre,
            'cantidad'              => 1,
            'precio'                => $item->precio,
            'iva'                   => (int) $item->iva,
            'cuenta'                => $item->cuenta_ingreso_codigo,
        ];
    }

    public function removeItem(int $idx): void
    {
        array_splice($this->items, $idx, 1);
    }

    // ── Enviar pedido de compra ───────────────────────────────────────────────

    public function sendOrder(): void
    {
        $tenant = tenancy()->tenant;

        $this->validate([
            'seller_id'  => ['required', 'string', 'different:' . $tenant->id],
            'concepto'   => ['required', 'string', 'max:500'],
            'gasto_code' => ['required', 'string'],
            'items'      => ['required', 'array', 'min:1'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
        ], [
            'seller_id.required'  => 'Selecciona una empresa vendedora.',
            'seller_id.different' => 'No puedes comprarte a ti mismo.',
            'concepto.required'   => 'Escribe un concepto para el pedido.',
            'gasto_code.required' => 'Selecciona la cuenta de gasto.',
            'items.required'      => 'Agrega al menos un ítem del portafolio.',
            'items.min'           => 'Agrega al menos un ítem del portafolio.',
        ]);

        $seller = CentralTenant::on('pgsql')
            ->where('id', $this->seller_id)
            ->where('group_id', $tenant->group_id)
            ->where('active', true)
            ->firstOrFail();

        // Calcular totales
        $subtotal = 0;
        $totalIva = 0;
        $itemsData = [];

        foreach ($this->items as $item) {
            $cant   = (float) $item['cantidad'];
            $precio = (float) $item['precio'];
            $pctIva = (int)   $item['iva'];
            $sub    = round($cant * $precio, 2);
            $iva    = round($sub * $pctIva / 100, 2);

            $subtotal += $sub;
            $totalIva += $iva;

            $itemsData[] = [
                'portafolio_item_id'    => $item['portafolio_item_id'] ?? null,
                'descripcion'           => $item['descripcion'],
                'cantidad'              => $cant,
                'precio_unitario'       => $precio,
                'subtotal'              => $sub,
                'iva'                   => $iva,
                'porcentaje_iva'        => $pctIva,
                'cuenta_ingreso_codigo' => $item['cuenta'] ?: '4135',
            ];
        }

        $retefte = $this->aplica_retencion
            ? IntercompanyService::calcularRetencion($subtotal)
            : 0;

        $reteiva = ($this->aplica_reteiva && $totalIva > 0)
            ? round($totalIva * 0.15, 2)
            : 0;

        $reteica = $this->aplica_reteica
            ? round($subtotal * 0.004, 2)
            : 0;

        $total = $subtotal + $totalIva - $retefte - $reteiva - $reteica;

        // Validar saldo y límites si el comprador eligió pagar desde banco
        if ($this->buyer_bank_account_id) {
            $cuentaBanco = BankAccount::find($this->buyer_bank_account_id);
            if ($cuentaBanco) {
                // Límite diario Banco de Bogotá: $25.000.000
                if ($cuentaBanco->bank === 'banco_bogota') {
                    $gastoHoy = BankTransaction::where('bank_account_id', $cuentaBanco->id)
                        ->whereDate('fecha_transaccion', today())
                        ->whereIn('tipo', ['transferencia_salida', 'pago_proveedor', 'retiro', 'cheque'])
                        ->sum('valor');
                    if (($gastoHoy + $total) > 25_000_000) {
                        $this->dispatch('notify', type: 'error',
                            message: 'Este pago supera el límite diario de Banco de Bogotá ($25.000.000). Elige otra cuenta o paga a crédito.');
                        return;
                    }
                }
                // Saldo suficiente (GMF estimado; ACH se calcula exacto en IntercompanyService)
                $gmfEstim   = round($total * 0.004);
                $totalCargo = $total + $gmfEstim;
                if ($cuentaBanco->saldoDisponible() < $totalCargo) {
                    $this->dispatch('notify', type: 'error',
                        message: 'Saldo insuficiente en ' . $cuentaBanco->nombreBanco() . '. Disponible: $' . number_format($cuentaBanco->saldoDisponible(), 0, ',', '.'));
                    return;
                }
            }
        }

        $buyerBankData = null;
        if ($this->buyer_bank_account_id) {
            $cuentaBanco = BankAccount::find($this->buyer_bank_account_id);
            $buyerBankData = [
                'buyer_bank_account_id' => $this->buyer_bank_account_id,
                'buyer_bank'            => $cuentaBanco?->bank,
            ];
        }

        $invoice = IntercompanyInvoice::create(array_filter(array_merge([
            'seller_tenant_id'    => $seller->id,
            'buyer_tenant_id'     => $tenant->id,
            'group_id'            => $tenant->group_id,
            'consecutive'         => IntercompanyInvoice::nextConsecutive($seller->id),
            'status'              => 'pendiente',
            'subtotal'            => $subtotal,
            'iva'                 => $totalIva,
            'retencion_fuente'    => $retefte,
            'retencion_iva'       => $reteiva,
            'retencion_ica'       => $reteica,
            'total'               => $total,
            'concepto'            => $this->concepto,
            'gasto_code_comprador'=> $this->gasto_code,
        ], $buyerBankData ?? []), fn ($v) => $v !== null));

        foreach ($itemsData as $item) {
            IntercompanyInvoiceItem::create(array_merge($item, [
                'intercompany_invoice_id' => $invoice->id,
            ]));
        }

        $this->reset(['seller_id', 'concepto', 'gasto_code', 'aplica_retencion', 'aplica_reteiva', 'aplica_reteica', 'items', 'buyer_bank_account_id']);
        $this->gasto_code = '5195';
        $this->tab = 'mis_pedidos';

        $this->dispatch('notify', type: 'success',
            message: "Pedido {$invoice->consecutive} enviado a {$seller->company_name}. Esperando confirmación.");
    }

    // ── Confirmar venta (seller side — acepta el pedido de compra) ────────────

    public function openConfirm(int $id): void
    {
        $this->confirmingId    = $id;
        $this->showConfirmModal = true;
    }

    public function confirmSale(IntercompanyService $service): void
    {
        $invoice = IntercompanyInvoice::with('items')->findOrFail($this->confirmingId);

        if ($invoice->seller_tenant_id !== tenancy()->tenant->id) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para confirmar esta venta.');
            return;
        }

        $hadBankPayment = (bool) $invoice->buyer_bank_account_id;

        try {
            $service->accept($invoice); // usa gasto_code_comprador guardado en el invoice

            $this->showConfirmModal = false;
            $this->confirmingId     = null;

            $bankMsg = $hadBankPayment
                ? ' Saldo bancario del comprador descontado.'
                : ' Queda en 2205 Proveedores (crédito).';
            $this->dispatch('notify', type: 'success',
                message: "Venta {$invoice->consecutive} confirmada.{$bankMsg}");

        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error',
                message: 'Error al contabilizar: ' . $e->getMessage());
        }
    }

    // ── Rechazar (seller side) ────────────────────────────────────────────────

    public function openReject(int $id): void
    {
        $this->rejectingId    = $id;
        $this->rechazo_motivo = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(IntercompanyService $service): void
    {
        $this->validate([
            'rechazo_motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rechazo_motivo.required' => 'Escribe el motivo del rechazo.',
            'rechazo_motivo.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $invoice = IntercompanyInvoice::findOrFail($this->rejectingId);

        if ($invoice->seller_tenant_id !== tenancy()->tenant->id) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para rechazar este pedido.');
            return;
        }

        $service->reject($invoice, $this->rechazo_motivo);

        $this->showRejectModal = false;
        $this->rejectingId     = null;
        $this->dispatch('notify', type: 'success', message: "Pedido {$invoice->consecutive} rechazado.");
    }

    // ── Cancelar pedido propio (buyer cancels before seller confirms) ─────────

    public function cancelOrder(int $id): void
    {
        $invoice = IntercompanyInvoice::findOrFail($id);

        if ($invoice->buyer_tenant_id !== tenancy()->tenant->id) {
            $this->dispatch('notify', type: 'error', message: 'No puedes cancelar este pedido.');
            return;
        }

        if (! $invoice->isPendiente()) {
            $this->dispatch('notify', type: 'error', message: 'Solo se pueden cancelar pedidos pendientes.');
            return;
        }

        $invoice->items()->delete();
        $invoice->delete();

        $this->dispatch('notify', type: 'success', message: 'Pedido cancelado.');
    }

    // ── Cuenta receptora de pagos (portafolio) ───────────────────────────────

    public function setRecibePageos(int $id): void
    {
        BankAccount::where('activa', true)->update(['recibe_pagos_negocios' => false]);
        BankAccount::where('id', $id)->where('activa', true)->update(['recibe_pagos_negocios' => true]);
        $this->dispatch('notify', type: 'success', message: 'Cuenta receptora de pagos actualizada.');
    }

    // ── Portafolio propio CRUD ────────────────────────────────────────────────

    public function openPortafolioForm(?int $id = null): void
    {
        $this->resetPortafolioForm();

        if ($id) {
            $item = PortafolioItem::where('tenant_id', tenancy()->tenant->id)->findOrFail($id);
            $this->editingPortafolioId = $item->id;
            $this->p_nombre            = $item->nombre;
            $this->p_descripcion       = $item->descripcion ?? '';
            $this->p_tipo              = $item->tipo;
            $this->p_precio            = (string) $item->precio;
            $this->p_iva               = $item->iva;
            $this->p_cuenta_codigo     = $item->cuenta_ingreso_codigo;
            $this->p_cuenta_nombre     = $item->cuenta_ingreso_nombre;
        }

        $this->showPortafolioForm = true;
    }

    public function savePortafolioItem(): void
    {
        $this->validate([
            'p_nombre'        => ['required', 'string', 'max:200'],
            'p_tipo'          => ['required', 'in:producto,servicio'],
            'p_precio'        => ['required', 'numeric', 'min:0.01'],
            'p_iva'           => ['required', 'in:0,5,19'],
            'p_cuenta_codigo' => ['required', 'string'],
            'p_cuenta_nombre' => ['required', 'string'],
        ], [
            'p_nombre.required'        => 'El nombre es requerido.',
            'p_precio.required'        => 'El precio es requerido.',
            'p_precio.min'             => 'El precio debe ser mayor a 0.',
            'p_cuenta_codigo.required' => 'Selecciona una cuenta de ingreso.',
        ]);

        $tenantId = tenancy()->tenant->id;

        $data = [
            'tenant_id'            => $tenantId,
            'nombre'               => $this->p_nombre,
            'descripcion'          => $this->p_descripcion ?: null,
            'tipo'                 => $this->p_tipo,
            'precio'               => (float) $this->p_precio,
            'iva'                  => $this->p_iva,
            'cuenta_ingreso_codigo'=> $this->p_cuenta_codigo,
            'cuenta_ingreso_nombre'=> $this->p_cuenta_nombre,
        ];

        if ($this->editingPortafolioId) {
            PortafolioItem::where('tenant_id', $tenantId)->findOrFail($this->editingPortafolioId)->update($data);
            $msg = 'Ítem actualizado correctamente.';
        } else {
            PortafolioItem::create($data);
            $msg = 'Ítem agregado al portafolio.';
        }

        $this->showPortafolioForm = false;
        $this->resetPortafolioForm();
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function togglePortafolioActivo(int $id): void
    {
        $item = PortafolioItem::where('tenant_id', tenancy()->tenant->id)->findOrFail($id);
        $item->update(['activo' => ! $item->activo]);
        $this->dispatch('notify', type: 'success',
            message: $item->activo ? 'Ítem activado.' : 'Ítem desactivado.');
    }

    public function updatedPCuentaCodigo(string $value): void
    {
        if (! $value) {
            $this->p_cuenta_nombre = '';
            return;
        }
        $account = Account::where('code', $value)->first();
        $this->p_cuenta_nombre = $account?->name ?? '';
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render(): mixed
    {
        $tenant = tenancy()->tenant;

        // Compañeros del grupo (potenciales vendedores)
        $companeros = CentralTenant::on('pgsql')
            ->where('group_id', $tenant->group_id)
            ->where('id', '!=', $tenant->id)
            ->where('active', true)
            ->where('type', 'student')
            ->orderBy('company_name')
            ->get();

        // Portafolio del vendedor seleccionado
        $vendedorPortafolio = collect();
        if ($this->seller_id) {
            $vendedorPortafolio = PortafolioItem::where('tenant_id', $this->seller_id)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        }

        // Cuenta receptora del vendedor (cross-tenant lookup)
        $vendedorCuentaReceptora = null;
        if ($this->seller_id) {
            $sellerTenantForBank = CentralTenant::on('pgsql')->find($this->seller_id);
            if ($sellerTenantForBank) {
                $vendedorCuentaReceptora = $sellerTenantForBank->run(
                    fn () => BankAccount::where('activa', true)
                        ->orderByDesc('recibe_pagos_negocios')
                        ->orderByDesc('es_principal')
                        ->first(['id', 'bank', 'account_number', 'recibe_pagos_negocios', 'es_principal'])
                );
            }
        }

        // Mis pedidos enviados (yo como comprador)
        $misPedidos = IntercompanyInvoice::with(['seller', 'items'])
            ->where('buyer_tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        // Pedidos recibidos para confirmar (yo como vendedor)
        $recibidas = IntercompanyInvoice::with(['buyer', 'items'])
            ->where('seller_tenant_id', $tenant->id)
            ->where('status', 'pendiente')
            ->orderByDesc('created_at')
            ->get();

        // Historial (completados — yo como vendedor o comprador)
        $historial = IntercompanyInvoice::with(['seller', 'buyer'])
            ->where(function ($q) use ($tenant) {
                $q->where('seller_tenant_id', $tenant->id)
                  ->orWhere('buyer_tenant_id', $tenant->id);
            })
            ->whereIn('status', ['aceptada', 'rechazada', 'anulada'])
            ->orderByDesc('accepted_at')
            ->orderByDesc('updated_at')
            ->get();

        // Mi portafolio propio
        $miPortafolio = PortafolioItem::where('tenant_id', $tenant->id)
            ->orderBy('nombre')
            ->get();

        // Cuentas de ingreso para el formulario de portafolio
        $cuentasIngreso = Account::where('type', 'ingreso')
            ->where('level', '>=', 3)
            ->where('active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        // Cuentas de gasto/activo para el comprador
        $cuentasGasto = Account::whereIn('type', ['gasto', 'activo'])
            ->where('level', '>=', 3)
            ->where('active', true)
            ->whereRaw("left(code, 1) IN ('5', '1')")
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $recibidasCount = $recibidas->count();

        $config = CompanyConfig::first();
        $sectorSugerida = $this->cuentaSugeridaPorSector($config?->sector_empresarial ?? 'comercial');

        // ── Panel financiero (Parte B) ─────────────────────────────────────────
        // El simulador bancario lleva saldos en bank_accounts.saldo (no en 1110)
        $saldoBancos = (float) BankAccount::where('activa', true)->sum('saldo');

        // Cuentas bancarias del simulador (para panel de saldo y selector de pago)
        $cuentasBancarias = BankAccount::where('activa', true)
            ->orderByDesc('es_principal')
            ->get();

        $porCobrar = (float) DB::table('journal_lines')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('accounts.code', 'like', '1305%')
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as saldo')
            ->value('saldo');

        $porPagar = (float) DB::table('journal_lines')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('accounts.code', 'like', '2205%')
            ->selectRaw('COALESCE(SUM(credit) - SUM(debit), 0) as saldo')
            ->value('saldo');

        return view('livewire.tenant.negocios.index', compact(
            'companeros', 'vendedorPortafolio', 'vendedorCuentaReceptora',
            'misPedidos', 'recibidas', 'historial',
            'miPortafolio', 'cuentasIngreso', 'cuentasGasto',
            'recibidasCount', 'sectorSugerida',
            'saldoBancos', 'porCobrar', 'porPagar',
            'cuentasBancarias'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetPortafolioForm(): void
    {
        $this->editingPortafolioId = null;
        $this->p_nombre            = '';
        $this->p_descripcion       = '';
        $this->p_tipo              = 'producto';
        $this->p_precio            = '';
        $this->p_iva               = '19';
        $this->p_cuenta_codigo     = '';
        $this->p_cuenta_nombre     = '';
    }

    private function cuentaSugeridaPorSector(string $sector): array
    {
        return match ($sector) {
            'comercial'  => ['codigo' => '4135', 'nombre' => 'Comercio al por mayor y al por menor'],
            'servicios'  => ['codigo' => '4160', 'nombre' => 'Servicios'],
            'industrial' => ['codigo' => '4120', 'nombre' => 'Industrias manufactureras'],
            'avicola',
            'ganadera'   => ['codigo' => '4105', 'nombre' => 'Agricultura, ganadería, caza y silvicultura'],
            default      => ['codigo' => '4195', 'nombre' => 'Otros ingresos operacionales'],
        };
    }
}
