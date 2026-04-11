<?php

namespace App\Services;

use App\Models\Central\IntercompanyInvoice;
use App\Models\Central\IntercompanyJournalEntry;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\Account;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use App\Services\BankService;
use Illuminate\Support\Facades\DB;

class IntercompanyService
{
    /** UVT 2025 — umbral mínimo para retención en la fuente (4 UVT). */
    const RETEFTE_THRESHOLD = 185108;
    const RETEFTE_RATE      = 0.035; // 3.5% servicios en general

    /**
     * Acepta una oferta interempresarial:
     * 1. Marca la factura como aceptada
     * 2. Crea asiento en la empresa del VENDEDOR (dentro de una transacción de BD)
     * 3. Crea asiento en la empresa del COMPRADOR (dentro de una transacción de BD)
     * 4. Registra el vínculo en intercompany_journal_entries (BD central)
     *
     * @throws \Throwable Si falla la contabilización en alguna de las dos empresas.
     */
    public function accept(IntercompanyInvoice $invoice, ?string $gastoCodigo = null): void
    {
        if (! $invoice->isPendiente()) {
            throw new \RuntimeException('Solo se pueden aceptar ofertas en estado pendiente.');
        }

        // Si no se pasa la cuenta, usar la que el comprador eligió al crear el pedido
        $gastoCodigo ??= $invoice->gasto_code_comprador ?? '5195';

        $sellerTenantId  = $invoice->seller_tenant_id;
        $buyerTenantId   = $invoice->buyer_tenant_id;
        $currentTenantId = tenancy()->tenant?->id;

        // Pre-cargar modelos centrales ANTES de cualquier switch de tenancy.
        // Después de tenancy()->initialize(), el search_path de PostgreSQL cambia
        // al schema del tenant y las tablas centrales (public.tenants, etc.) quedan inaccesibles.
        $sellerTenant  = CentralTenant::on('pgsql')->find($sellerTenantId);
        $buyerTenant   = CentralTenant::on('pgsql')->find($buyerTenantId);
        $currentTenant = $currentTenantId ? CentralTenant::on('pgsql')->find($currentTenantId) : null;

        try {
            // ── 1. Marcar como aceptada (BD central) ─────────────────────────
            $invoice->update([
                'status'      => 'aceptada',
                'accepted_at' => now(),
            ]);

            // ── 2. Asiento en la empresa del VENDEDOR ─────────────────────────
            tenancy()->end();
            tenancy()->initialize($sellerTenant);

            $sellerEntry = DB::transaction(fn () => $this->createSellerEntry($invoice, $buyerTenant->company_name));

            // Pago bancario en la empresa del VENDEDOR (si tiene cuenta principal activa)
            $sellerBankAccountId = null;
            $sellerBankName      = null;
            // Prioridad: cuenta marcada como receptora de pagos, luego cuenta principal
            $sellerBankAccount = BankAccount::where('activa', true)
                ->orderByDesc('recibe_pagos_negocios')
                ->orderByDesc('es_principal')
                ->first();
            if ($sellerBankAccount) {
                $sellerBankAccountId = $sellerBankAccount->id;
                $sellerBankName      = $sellerBankAccount->bank;
                DB::transaction(fn () => $this->registerSellerBankReceipt($invoice, $sellerBankAccount, $sellerEntry));
            }

            // Volver a central ANTES de escribir en tablas centrales
            tenancy()->end();
            IntercompanyJournalEntry::create([
                'intercompany_invoice_id' => $invoice->id,
                'party'                   => 'seller',
                'tenant_id'               => $sellerTenantId,
                'journal_entry_id'        => $sellerEntry->id,
            ]);

            // ── 3. Asiento en la empresa del COMPRADOR ────────────────────────
            tenancy()->initialize($buyerTenant);

            $buyerEntry = DB::transaction(fn () => $this->createBuyerEntry($invoice, $gastoCodigo, $sellerTenant->company_name));

            // Pago bancario en la empresa del COMPRADOR (si eligió pagar desde banco)
            if ($invoice->buyer_bank_account_id) {
                $buyerBankAccount = BankAccount::find($invoice->buyer_bank_account_id);
                if ($buyerBankAccount && $buyerBankAccount->activa && ! $buyerBankAccount->bloqueada) {
                    $comisionAch = BankService::costoAch(
                        $buyerBankAccount->bank,
                        $sellerBankName ?? $buyerBankAccount->bank
                    );
                    DB::transaction(fn () => $this->registerBuyerBankPayment(
                        $invoice, $buyerBankAccount, $buyerEntry, $comisionAch, $sellerBankName
                    ));
                    // Guardar costos bancarios en la factura central
                    $gmf = BankService::calcularGmf('pago_proveedor', (float) $invoice->total);
                    $invoice->update([
                        'seller_bank_account_id' => $sellerBankAccountId,
                        'seller_bank'            => $sellerBankName,
                        'gmf_total'              => $gmf,
                        'comision_ach'           => $comisionAch,
                    ]);
                }
            } elseif ($sellerBankAccountId) {
                // Solo actualizar el banco del vendedor aunque el comprador no pagó por banco
                $invoice->update([
                    'seller_bank_account_id' => $sellerBankAccountId,
                    'seller_bank'            => $sellerBankName,
                ]);
            }

            // Volver a central ANTES de escribir en tablas centrales
            tenancy()->end();
            IntercompanyJournalEntry::create([
                'intercompany_invoice_id' => $invoice->id,
                'party'                   => 'buyer',
                'tenant_id'               => $buyerTenantId,
                'journal_entry_id'        => $buyerEntry->id,
            ]);

            // Restaurar el tenant original al terminar
            if ($currentTenant) {
                tenancy()->initialize($currentTenant);
            }

        } catch (\Throwable $e) {
            // Revertir estado de la factura y limpiar vínculos parciales
            try { tenancy()->end(); } catch (\Throwable) {}
            $invoice->update(['status' => 'pendiente', 'accepted_at' => null]);
            IntercompanyJournalEntry::where('intercompany_invoice_id', $invoice->id)->delete();

            try {
                if ($currentTenant) {
                    tenancy()->initialize($currentTenant);
                }
            } catch (\Throwable) {}

            throw $e;
        }
    }

    /**
     * Anula una transacción ya aceptada (solo docente/coordinador).
     * Elimina los asientos en ambas empresas usando los vínculos registrados.
     *
     * @throws \Throwable
     */
    public function annul(IntercompanyInvoice $invoice, string $motivo, int $userId): void
    {
        if (! $invoice->isAceptada()) {
            throw new \RuntimeException('Solo se pueden anular transacciones aceptadas.');
        }

        $currentTenantId = tenancy()->tenant?->id;

        // Pre-cargar tenants centrales antes de cualquier switch
        $currentTenant = $currentTenantId ? CentralTenant::on('pgsql')->find($currentTenantId) : null;

        try {
            $links = IntercompanyJournalEntry::where('intercompany_invoice_id', $invoice->id)->get();

            // Pre-cargar modelos de tenant para los links (evitar queries en contexto equivocado)
            $tenantModels = [];
            foreach ($links as $link) {
                $tenantModels[$link->tenant_id] = CentralTenant::on('pgsql')->find($link->tenant_id);
            }

            foreach ($links as $link) {
                tenancy()->end();
                tenancy()->initialize($tenantModels[$link->tenant_id]);

                DB::transaction(function () use ($link) {
                    $entry = JournalEntry::find($link->journal_entry_id);
                    if ($entry) {
                        JournalLine::where('journal_entry_id', $entry->id)->delete();
                        $entry->delete();
                    }
                });
            }

            // Fallback: si no había vínculos, buscar por document_type/document_id
            if ($links->isEmpty()) {
                $fallbackTenants = [
                    CentralTenant::on('pgsql')->find($invoice->seller_tenant_id),
                    CentralTenant::on('pgsql')->find($invoice->buyer_tenant_id),
                ];
                foreach ($fallbackTenants as $ft) {
                    if (! $ft) continue;
                    tenancy()->end();
                    tenancy()->initialize($ft);
                    DB::transaction(fn () => $this->deleteIntercompanyEntry($invoice->id));
                }
            }

            // Limpiar vínculos y marcar como anulada (BD central) — siempre fuera de tenant context
            tenancy()->end();
            IntercompanyJournalEntry::where('intercompany_invoice_id', $invoice->id)->delete();

            $invoice->update([
                'status'           => 'anulada',
                'anulada_by'       => $userId,
                'anulada_at'       => now(),
                'anulacion_motivo' => $motivo,
            ]);

            if ($currentTenant) {
                tenancy()->initialize($currentTenant);
            }

        } catch (\Throwable $e) {
            try {
                tenancy()->end();
                if ($currentTenant) {
                    tenancy()->initialize($currentTenant);
                }
            } catch (\Throwable) {}

            throw $e;
        }
    }

    /**
     * Rechaza una oferta. Solo actualiza el estado — sin asientos contables.
     */
    public function reject(IntercompanyInvoice $invoice, string $motivo): void
    {
        if (! $invoice->isPendiente()) {
            throw new \RuntimeException('Solo se pueden rechazar ofertas en estado pendiente.');
        }

        $invoice->update([
            'status'         => 'rechazada',
            'rechazo_motivo' => $motivo,
        ]);
    }

    // ── Fallback: eliminar asiento por document_type/document_id ─────────────

    private function deleteIntercompanyEntry(int $invoiceId): void
    {
        $entry = JournalEntry::where('document_type', 'intercompany')
            ->where('document_id', $invoiceId)
            ->first();

        if ($entry) {
            JournalLine::where('journal_entry_id', $entry->id)->delete();
            $entry->delete();
        }
    }

    // ── Asiento VENDEDOR ─────────────────────────────────────────────────────

    private function createSellerEntry(IntercompanyInvoice $invoice, string $buyerName): JournalEntry
    {
        $entry = JournalEntry::create([
            'date'           => now()->toDateString(),
            'reference'      => $invoice->consecutive,
            'description'    => "Venta interempresarial {$invoice->consecutive} a {$buyerName}",
            'document_type'  => 'intercompany',
            'document_id'    => $invoice->id,
            'auto_generated' => true,
        ]);

        $lines = [];

        // Acumular ingresos por cuenta (puede haber ítems con distintas cuentas)
        $ingresosPorCuenta = [];
        foreach ($invoice->items as $item) {
            $codigo = $item->cuenta_ingreso_codigo ?: '4135';
            $ingresosPorCuenta[$codigo] = ($ingresosPorCuenta[$codigo] ?? 0) + (float) $item->subtotal;
        }

        // DR 1305 — CxC clientes
        // Neto a cobrar = subtotal + IVA − retenciones sufridas
        $bruto = (float) $invoice->subtotal + (float) $invoice->iva;
        $totalRetenciones = (float) $invoice->retencion_fuente
                          + (float) $invoice->retencion_iva
                          + (float) $invoice->retencion_ica;
        $neto = $bruto - $totalRetenciones;

        $ar = $this->accountId('1305');
        if ($ar && $neto > 0) {
            $lines[] = ['account_id' => $ar, 'debit' => $neto, 'credit' => 0,
                'description' => 'CxC cliente interempresarial'];
        }

        // DR 1355 — Anticipo de impuestos (retención en la fuente sufrida)
        if ((float) $invoice->retencion_fuente > 0) {
            $anticipo = $this->accountId('1355');
            if ($anticipo) {
                $lines[] = ['account_id' => $anticipo, 'debit' => (float) $invoice->retencion_fuente,
                    'credit' => 0, 'description' => 'Retención en la fuente sufrida'];
            }
        }

        // DR 1355 — Retención IVA sufrida (si aplica)
        if ((float) $invoice->retencion_iva > 0) {
            $anticipoIva = $this->accountId('1355');
            if ($anticipoIva) {
                $lines[] = ['account_id' => $anticipoIva, 'debit' => (float) $invoice->retencion_iva,
                    'credit' => 0, 'description' => 'Retención IVA sufrida'];
            }
        }

        // DR 1355 — Retención ICA sufrida (si aplica)
        if ((float) $invoice->retencion_ica > 0) {
            $anticipoIca = $this->accountId('1355');
            if ($anticipoIca) {
                $lines[] = ['account_id' => $anticipoIca, 'debit' => (float) $invoice->retencion_ica,
                    'credit' => 0, 'description' => 'Retención ICA sufrida'];
            }
        }

        // CR 4xxx — Ingresos (agrupados por cuenta)
        foreach ($ingresosPorCuenta as $codigo => $monto) {
            $accId = $this->accountId($codigo) ?? $this->accountId('4135');
            if ($accId) {
                $lines[] = ['account_id' => $accId, 'debit' => 0, 'credit' => $monto,
                    'description' => 'Ingresos venta interempresarial'];
            }
        }

        // CR 2408 — IVA generado
        if ((float) $invoice->iva > 0) {
            $vat = $this->accountId('2408');
            if ($vat) {
                $lines[] = ['account_id' => $vat, 'debit' => 0, 'credit' => (float) $invoice->iva,
                    'description' => 'IVA generado venta interempresarial'];
            }
        }

        $this->saveLines($entry, $lines);

        return $entry;
    }

    // ── Asiento COMPRADOR ─────────────────────────────────────────────────────

    private function createBuyerEntry(IntercompanyInvoice $invoice, string $gastoCodigo, string $sellerName): JournalEntry
    {
        $entry = JournalEntry::create([
            'date'           => now()->toDateString(),
            'reference'      => $invoice->consecutive,
            'description'    => "Compra interempresarial {$invoice->consecutive} de {$sellerName}",
            'document_type'  => 'intercompany',
            'document_id'    => $invoice->id,
            'auto_generated' => true,
        ]);

        $lines = [];

        $bruto = (float) $invoice->subtotal + (float) $invoice->iva;
        $totalRetenciones = (float) $invoice->retencion_fuente
                          + (float) $invoice->retencion_iva
                          + (float) $invoice->retencion_ica;
        $neto = $bruto - $totalRetenciones;

        // DR cuenta gasto/activo elegida por el comprador
        $gastoId = $this->accountId($gastoCodigo) ?? $this->accountId('5195');
        if ($gastoId) {
            $lines[] = ['account_id' => $gastoId, 'debit' => (float) $invoice->subtotal,
                'credit' => 0, 'description' => 'Compra interempresarial'];
        }

        // DR 240810 — IVA descontable (intenta auxiliar específico, luego cuenta padre)
        if ((float) $invoice->iva > 0) {
            $vatDescontable = $this->accountId('240810') ?? $this->accountId('2408');
            if ($vatDescontable) {
                $lines[] = ['account_id' => $vatDescontable, 'debit' => (float) $invoice->iva,
                    'credit' => 0, 'description' => 'IVA descontable compra interempresarial'];
            }
        }

        // CR 2205 — Proveedores (neto a pagar)
        $suppliers = $this->accountId('2205');
        if ($suppliers) {
            $lines[] = ['account_id' => $suppliers, 'debit' => 0, 'credit' => $neto,
                'description' => 'CxP proveedor interempresarial'];
        }

        // CR 2365 — Retención en la fuente practicada
        if ((float) $invoice->retencion_fuente > 0) {
            $reteFte = $this->accountId('2365');
            if ($reteFte) {
                $lines[] = ['account_id' => $reteFte, 'debit' => 0,
                    'credit' => (float) $invoice->retencion_fuente,
                    'description' => 'Retención en la fuente practicada'];
            }
        }

        // CR 2367 — Retención IVA practicada (si aplica)
        if ((float) $invoice->retencion_iva > 0) {
            $reteIva = $this->accountId('2367') ?? $this->accountId('2365');
            if ($reteIva) {
                $lines[] = ['account_id' => $reteIva, 'debit' => 0,
                    'credit' => (float) $invoice->retencion_iva,
                    'description' => 'Retención IVA practicada'];
            }
        }

        // CR 2372 — Retención ICA practicada (si aplica)
        if ((float) $invoice->retencion_ica > 0) {
            $reteIca = $this->accountId('2372') ?? $this->accountId('2365');
            if ($reteIca) {
                $lines[] = ['account_id' => $reteIca, 'debit' => 0,
                    'credit' => (float) $invoice->retencion_ica,
                    'description' => 'Retención ICA practicada'];
            }
        }

        $this->saveLines($entry, $lines);

        return $entry;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function saveLines(JournalEntry $entry, array $lines): void
    {
        foreach ($lines as $line) {
            JournalLine::create(array_merge($line, ['journal_entry_id' => $entry->id]));
        }
    }

    private function accountId(string $code): ?int
    {
        return Account::where('code', $code)->value('id');
    }

    // ── Pago bancario COMPRADOR ───────────────────────────────────────────────

    /**
     * Registra el pago del comprador desde su cuenta bancaria.
     * DR 2205 Proveedores | DR GMF | DR Comisión ACH | CR 1110 Bancos
     */
    private function registerBuyerBankPayment(
        IntercompanyInvoice $invoice,
        BankAccount $cuenta,
        JournalEntry $buyerEntry,
        float $comisionAch,
        ?string $sellerBank
    ): void {
        $total = (float) $invoice->total;
        $gmf   = BankService::calcularGmf('pago_proveedor', $total);
        $totalCargo = $total + $gmf + $comisionAch;

        // Asiento de pago bancario
        $payEntry = JournalEntry::create([
            'date'           => now()->toDateString(),
            'reference'      => $invoice->consecutive . '-PAG',
            'description'    => "Pago {$invoice->consecutive} desde {$cuenta->nombreBanco()}",
            'document_type'  => 'pago_intercompany',
            'document_id'    => $invoice->id,
            'auto_generated' => true,
        ]);

        $proveedores = $this->accountId('2205');
        $bancos      = $this->accountId('1110');
        $gmfAccount  = $this->accountId('530520') ?? $this->accountId('5305');
        $achAccount  = $this->accountId('5305');

        if ($proveedores) {
            JournalLine::create(['journal_entry_id' => $payEntry->id, 'account_id' => $proveedores, 'debit' => $total,   'credit' => 0,            'description' => 'Pago proveedor interempresarial']);
        }
        if ($gmfAccount && $gmf > 0) {
            JournalLine::create(['journal_entry_id' => $payEntry->id, 'account_id' => $gmfAccount,  'debit' => $gmf,     'credit' => 0,            'description' => 'GMF 4x1000']);
        }
        if ($achAccount && $comisionAch > 0) {
            JournalLine::create(['journal_entry_id' => $payEntry->id, 'account_id' => $achAccount,  'debit' => $comisionAch, 'credit' => 0,        'description' => 'Comisión ACH transferencia']);
        }
        if ($bancos) {
            JournalLine::create(['journal_entry_id' => $payEntry->id, 'account_id' => $bancos,      'debit' => 0,        'credit' => $totalCargo,  'description' => "Salida {$cuenta->nombreBanco()}***{$cuenta->ultimosDigitos()}"]);
        }

        // Actualizar saldo de la cuenta
        $cuenta->decrement('saldo', $totalCargo);

        BankTransaction::create([
            'bank_account_id'        => $cuenta->id,
            'tipo'                   => 'pago_proveedor',
            'valor'                  => $total,
            'gmf'                    => $gmf,
            'comision'               => $comisionAch,
            'saldo_despues'          => $cuenta->fresh()->saldo,
            'descripcion'            => "Pago {$invoice->consecutive}",
            'banco_destino'          => $sellerBank,
            'journal_entry_id'       => $payEntry->id,
            'intercompany_invoice_id'=> $invoice->id,
            'fecha_transaccion'      => now()->toDateString(),
        ]);
    }

    // ── Cobro bancario VENDEDOR ───────────────────────────────────────────────

    /**
     * Registra el cobro del vendedor en su cuenta bancaria (si el comprador pagó por banco).
     * DR 1110 Bancos | CR 1305 Clientes
     */
    private function registerSellerBankReceipt(
        IntercompanyInvoice $invoice,
        BankAccount $cuenta,
        JournalEntry $sellerEntry
    ): void {
        // Solo registrar si el comprador eligió pagar por banco
        if (! $invoice->buyer_bank_account_id) {
            return;
        }

        $neto = (float) $invoice->total
            - (float) $invoice->retencion_fuente
            - (float) $invoice->retencion_iva
            - (float) $invoice->retencion_ica;

        if ($neto <= 0) {
            return;
        }

        $recEntry = JournalEntry::create([
            'date'           => now()->toDateString(),
            'reference'      => $invoice->consecutive . '-COB',
            'description'    => "Cobro {$invoice->consecutive} en {$cuenta->nombreBanco()}",
            'document_type'  => 'cobro_intercompany',
            'document_id'    => $invoice->id,
            'auto_generated' => true,
        ]);

        $bancos  = $this->accountId('1110');
        $clientes = $this->accountId('1305');

        if ($bancos) {
            JournalLine::create(['journal_entry_id' => $recEntry->id, 'account_id' => $bancos,   'debit' => $neto, 'credit' => 0,     'description' => "Cobro {$cuenta->nombreBanco()}***{$cuenta->ultimosDigitos()}"]);
        }
        if ($clientes) {
            JournalLine::create(['journal_entry_id' => $recEntry->id, 'account_id' => $clientes, 'debit' => 0,     'credit' => $neto, 'description' => 'Cobro cartera cliente interempresarial']);
        }

        // Actualizar saldo
        $cuenta->increment('saldo', $neto);

        BankTransaction::create([
            'bank_account_id'        => $cuenta->id,
            'tipo'                   => 'cobro_cliente',
            'valor'                  => $neto,
            'gmf'                    => 0,
            'comision'               => 0,
            'saldo_despues'          => $cuenta->fresh()->saldo,
            'descripcion'            => "Cobro {$invoice->consecutive}",
            'journal_entry_id'       => $recEntry->id,
            'intercompany_invoice_id'=> $invoice->id,
            'fecha_transaccion'      => now()->toDateString(),
        ]);
    }

    /** Calcula si aplica retención en la fuente y cuánto. */
    public static function calcularRetencion(float $subtotal): float
    {
        if ($subtotal < self::RETEFTE_THRESHOLD) {
            return 0;
        }

        return round($subtotal * self::RETEFTE_RATE, 2);
    }
}
