<?php

namespace App\Services;

use App\Models\Central\IntercompanyInvoice;
use App\Models\Central\IntercompanyJournalEntry;
use App\Models\Central\Tenant as CentralTenant;
use App\Models\Tenant\Account;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
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
    public function accept(IntercompanyInvoice $invoice, string $gastoCodigo): void
    {
        if (! $invoice->isPendiente()) {
            throw new \RuntimeException('Solo se pueden aceptar ofertas en estado pendiente.');
        }

        $sellerTenantId  = $invoice->seller_tenant_id;
        $buyerTenantId   = $invoice->buyer_tenant_id;
        $currentTenantId = tenancy()->tenant?->id;

        try {
            // ── 1. Marcar como aceptada (BD central) ─────────────────────────
            $invoice->update([
                'status'      => 'aceptada',
                'accepted_at' => now(),
            ]);

            // ── 2. Asiento en la empresa del VENDEDOR ─────────────────────────
            tenancy()->end();
            tenancy()->initialize(CentralTenant::find($sellerTenantId));

            $sellerEntry = DB::transaction(fn () => $this->createSellerEntry($invoice));

            // Registrar vínculo en BD central
            IntercompanyJournalEntry::create([
                'intercompany_invoice_id' => $invoice->id,
                'party'                   => 'seller',
                'tenant_id'               => $sellerTenantId,
                'journal_entry_id'        => $sellerEntry->id,
            ]);

            // ── 3. Asiento en la empresa del COMPRADOR ────────────────────────
            tenancy()->end();
            tenancy()->initialize(CentralTenant::find($buyerTenantId));

            $buyerEntry = DB::transaction(fn () => $this->createBuyerEntry($invoice, $gastoCodigo));

            // Registrar vínculo en BD central
            IntercompanyJournalEntry::create([
                'intercompany_invoice_id' => $invoice->id,
                'party'                   => 'buyer',
                'tenant_id'               => $buyerTenantId,
                'journal_entry_id'        => $buyerEntry->id,
            ]);

        } catch (\Throwable $e) {
            // Revertir estado de la factura y limpiar vínculos parciales
            $invoice->update(['status' => 'pendiente', 'accepted_at' => null]);
            IntercompanyJournalEntry::where('intercompany_invoice_id', $invoice->id)->delete();

            try {
                tenancy()->end();
                if ($currentTenantId) {
                    tenancy()->initialize(CentralTenant::find($currentTenantId));
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

        try {
            $links = IntercompanyJournalEntry::where('intercompany_invoice_id', $invoice->id)->get();

            foreach ($links as $link) {
                tenancy()->end();
                tenancy()->initialize(CentralTenant::find($link->tenant_id));

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
                foreach ([$invoice->seller_tenant_id, $invoice->buyer_tenant_id] as $tenantId) {
                    tenancy()->end();
                    tenancy()->initialize(CentralTenant::find($tenantId));
                    DB::transaction(fn () => $this->deleteIntercompanyEntry($invoice->id));
                }
            }

            // Limpiar vínculos y marcar como anulada (BD central)
            tenancy()->end();
            IntercompanyJournalEntry::where('intercompany_invoice_id', $invoice->id)->delete();

            $invoice->update([
                'status'           => 'anulada',
                'anulada_by'       => $userId,
                'anulada_at'       => now(),
                'anulacion_motivo' => $motivo,
            ]);

            if ($currentTenantId) {
                tenancy()->initialize(CentralTenant::find($currentTenantId));
            }

        } catch (\Throwable $e) {
            try {
                tenancy()->end();
                if ($currentTenantId) {
                    tenancy()->initialize(CentralTenant::find($currentTenantId));
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

    private function createSellerEntry(IntercompanyInvoice $invoice): JournalEntry
    {
        $buyer = CentralTenant::find($invoice->buyer_tenant_id);

        $entry = JournalEntry::create([
            'date'           => now()->toDateString(),
            'reference'      => $invoice->consecutive,
            'description'    => "Venta interempresarial {$invoice->consecutive} a {$buyer->company_name}",
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

    private function createBuyerEntry(IntercompanyInvoice $invoice, string $gastoCodigo): JournalEntry
    {
        $seller = CentralTenant::find($invoice->seller_tenant_id);

        $entry = JournalEntry::create([
            'date'           => now()->toDateString(),
            'reference'      => $invoice->consecutive,
            'description'    => "Compra interempresarial {$invoice->consecutive} de {$seller->company_name}",
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

    /** Calcula si aplica retención en la fuente y cuánto. */
    public static function calcularRetencion(float $subtotal): float
    {
        if ($subtotal < self::RETEFTE_THRESHOLD) {
            return 0;
        }

        return round($subtotal * self::RETEFTE_RATE, 2);
    }
}
