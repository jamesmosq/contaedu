<?php

namespace App\Livewire\Tenant\Negocios;

use App\Models\Central\IntercompanyInvoice;
use App\Models\Central\IntercompanyInvoiceItem;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\Account;
use App\Services\IntercompanyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Negocios')]
class Index extends Component
{
    // ── Tab activa ────────────────────────────────────────────────────────────
    public string $tab = 'recibidas';

    // ── Formulario: nueva oferta ──────────────────────────────────────────────
    public bool   $showCreateForm   = false;
    public string $buyer_id         = '';
    public string $concepto         = '';
    public bool   $aplica_retencion = false;
    public bool   $aplica_reteiva   = false;
    public bool   $aplica_reteica   = false;
    public array  $items            = [];

    // ── Modal: aceptar oferta ─────────────────────────────────────────────────
    public bool   $showAcceptModal  = false;
    public ?int   $acceptingId      = null;
    public string $gasto_code       = '5195';

    // ── Modal: rechazar oferta ────────────────────────────────────────────────
    public bool   $showRejectModal  = false;
    public ?int   $rejectingId      = null;
    public string $rechazo_motivo   = '';

    public function mount(): void
    {
        $this->resetItems();
    }

    // ── Crear oferta ──────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->reset(['buyer_id', 'concepto', 'aplica_retencion', 'aplica_reteiva', 'aplica_reteica']);
        $this->resetItems();
        $this->showCreateForm = true;
        $this->tab = 'nueva';
    }

    public function addItem(): void
    {
        $this->items[] = ['descripcion' => '', 'cantidad' => 1, 'precio' => 0, 'iva' => 19, 'cuenta' => '4135'];
    }

    public function removeItem(int $idx): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $idx, 1);
        }
    }

    public function sendOffer(): void
    {
        $tenant = tenancy()->tenant;

        $this->validate([
            'buyer_id'  => ['required', 'string', 'different:' . $tenant->id],
            'concepto'  => ['required', 'string', 'max:500'],
            'items'     => ['required', 'array', 'min:1'],
            'items.*.descripcion' => ['required', 'string', 'max:255'],
            'items.*.cantidad'    => ['required', 'numeric', 'min:0.01'],
            'items.*.precio'      => ['required', 'numeric', 'min:0'],
            'items.*.iva'         => ['required', 'integer', 'in:0,5,19'],
            'items.*.cuenta'      => ['required', 'string'],
        ], [
            'buyer_id.required'  => 'Selecciona un compañero.',
            'buyer_id.different' => 'No puedes enviarte una oferta a ti mismo.',
            'concepto.required'  => 'Escribe un concepto para la oferta.',
            'items.required'     => 'Agrega al menos un ítem.',
            'items.min'          => 'Agrega al menos un ítem.',
        ]);

        // Verificar que el comprador es del mismo grupo
        $buyer = CentralTenant::on('pgsql')
            ->where('id', $this->buyer_id)
            ->where('group_id', $tenant->group_id)
            ->where('active', true)
            ->firstOrFail();

        // Calcular totales
        $subtotal = 0;
        $totalIva = 0;
        $itemsData = [];

        foreach ($this->items as $item) {
            $cant      = (float) $item['cantidad'];
            $precio    = (float) $item['precio'];
            $pctIva    = (int)   $item['iva'];
            $sub       = round($cant * $precio, 2);
            $iva       = round($sub * $pctIva / 100, 2);

            $subtotal  += $sub;
            $totalIva  += $iva;

            $itemsData[] = [
                'descripcion'         => $item['descripcion'],
                'cantidad'            => $cant,
                'precio_unitario'     => $precio,
                'subtotal'            => $sub,
                'iva'                 => $iva,
                'porcentaje_iva'      => $pctIva,
                'cuenta_ingreso_codigo' => $item['cuenta'] ?: '4135',
            ];
        }

        $retefte  = $this->aplica_retencion
            ? IntercompanyService::calcularRetencion($subtotal)
            : 0;

        // Retención IVA: 15% del IVA (aplica cuando el comprador es agente retenedor de IVA)
        $reteiva  = ($this->aplica_reteiva && $totalIva > 0)
            ? round($totalIva * 0.15, 2)
            : 0;

        // Retención ICA: 0.4% sobre el subtotal (tarifa mínima; el profesor puede ajustar)
        $reteica  = $this->aplica_reteica
            ? round($subtotal * 0.004, 2)
            : 0;

        $total = $subtotal + $totalIva - $retefte - $reteiva - $reteica;

        $invoice = IntercompanyInvoice::create([
            'seller_tenant_id' => $tenant->id,
            'buyer_tenant_id'  => $buyer->id,
            'group_id'         => $tenant->group_id,
            'consecutive'      => IntercompanyInvoice::nextConsecutive($tenant->id),
            'status'           => 'pendiente',
            'subtotal'         => $subtotal,
            'iva'              => $totalIva,
            'retencion_fuente' => $retefte,
            'retencion_iva'    => $reteiva,
            'retencion_ica'    => $reteica,
            'total'            => $total,
            'concepto'         => $this->concepto,
        ]);

        foreach ($itemsData as $item) {
            IntercompanyInvoiceItem::create(array_merge($item, [
                'intercompany_invoice_id' => $invoice->id,
            ]));
        }

        $this->showCreateForm = false;
        $this->tab = 'enviadas';
        $this->dispatch('notify', type: 'success',
            message: "Oferta {$invoice->consecutive} enviada a {$buyer->company_name}.");
    }

    // ── Aceptar oferta ────────────────────────────────────────────────────────

    public function openAccept(int $id): void
    {
        $this->acceptingId     = $id;
        $this->gasto_code      = '5195';
        $this->showAcceptModal = true;
    }

    public function confirmAccept(IntercompanyService $service): void
    {
        $this->validate([
            'gasto_code' => ['required', 'string'],
        ], [
            'gasto_code.required' => 'Selecciona la cuenta de gasto o activo.',
        ]);

        $invoice = IntercompanyInvoice::with('items')->findOrFail($this->acceptingId);

        // Verificar que el comprador es el tenant actual
        if ($invoice->buyer_tenant_id !== tenancy()->tenant->id) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para aceptar esta oferta.');
            return;
        }

        try {
            $service->accept($invoice, $this->gasto_code);

            $this->showAcceptModal = false;
            $this->acceptingId     = null;
            $this->dispatch('notify', type: 'success',
                message: "Oferta {$invoice->consecutive} aceptada. Asientos registrados en ambas empresas.");

        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error',
                message: 'Error al contabilizar: ' . $e->getMessage());
        }
    }

    // ── Rechazar oferta ───────────────────────────────────────────────────────

    public function openReject(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rechazo_motivo  = '';
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

        if ($invoice->buyer_tenant_id !== tenancy()->tenant->id) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para rechazar esta oferta.');
            return;
        }

        $service->reject($invoice, $this->rechazo_motivo);

        $this->showRejectModal = false;
        $this->rejectingId     = null;
        $this->dispatch('notify', type: 'success', message: "Oferta {$invoice->consecutive} rechazada.");
    }

    // ── Cancelar oferta enviada ───────────────────────────────────────────────

    public function cancelOffer(int $id): void
    {
        $invoice = IntercompanyInvoice::findOrFail($id);

        if ($invoice->seller_tenant_id !== tenancy()->tenant->id) {
            $this->dispatch('notify', type: 'error', message: 'No puedes cancelar esta oferta.');
            return;
        }

        if (! $invoice->isPendiente()) {
            $this->dispatch('notify', type: 'error', message: 'Solo se pueden cancelar ofertas pendientes.');
            return;
        }

        $invoice->items()->delete();
        $invoice->delete();

        $this->dispatch('notify', type: 'success', message: 'Oferta cancelada.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render(): mixed
    {
        $tenant = tenancy()->tenant;

        // Compañeros del mismo grupo (central DB — forzar conexión pgsql para evitar scope tenant)
        $companeros = CentralTenant::on('pgsql')
            ->where('group_id', $tenant->group_id)
            ->where('id', '!=', $tenant->id)
            ->where('active', true)
            ->where('type', 'student')
            ->orderBy('company_name')
            ->get();

        // Mis ofertas enviadas (como vendedor)
        $enviadas = IntercompanyInvoice::with(['buyer', 'items'])
            ->where('seller_tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->get();

        // Ofertas recibidas pendientes (como comprador)
        $recibidas = IntercompanyInvoice::with(['seller', 'items'])
            ->where('buyer_tenant_id', $tenant->id)
            ->where('status', 'pendiente')
            ->orderByDesc('created_at')
            ->get();

        // Historial (aceptadas / rechazadas como comprador o vendedor)
        $historial = IntercompanyInvoice::with(['seller', 'buyer'])
            ->where(function ($q) use ($tenant) {
                $q->where('seller_tenant_id', $tenant->id)
                  ->orWhere('buyer_tenant_id', $tenant->id);
            })
            ->whereIn('status', ['aceptada', 'rechazada'])
            ->orderByDesc('accepted_at')
            ->orderByDesc('updated_at')
            ->get();

        // Cuentas de ingreso del vendedor (PUC del tenant actual — para el formulario)
        $cuentasIngreso = Account::where('type', 'ingreso')
            ->where('level', '>=', 3)
            ->where('active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        // Cuentas de gasto/activo del comprador (para aceptar)
        $cuentasGasto = Account::whereIn('type', ['gasto', 'activo'])
            ->where('level', '>=', 3)
            ->where('active', true)
            ->whereRaw("left(code, 1) IN ('5', '1')")
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $recibidasCount = $recibidas->count();

        return view('livewire.tenant.negocios.index', compact(
            'companeros', 'enviadas', 'recibidas', 'historial',
            'cuentasIngreso', 'cuentasGasto', 'recibidasCount'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resetItems(): void
    {
        $this->items = [
            ['descripcion' => '', 'cantidad' => 1, 'precio' => 0, 'iva' => 19, 'cuenta' => '4135'],
        ];
    }
}
