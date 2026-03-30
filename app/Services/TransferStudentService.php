<?php

namespace App\Services;

use App\Models\Central\StudentScore;
use App\Models\Central\Tenant;
use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Support\Facades\DB;

class TransferStudentService
{
    /**
     * Transfiere un estudiante a otro grupo con tres modos:
     *
     *  keep  — Solo cambia de grupo, conserva todos los datos.
     *  reset — Cambia de grupo y borra los datos transaccionales
     *          (facturas, compras, asientos) pero conserva PUC, config, terceros y productos.
     *  fresh — Cambia de grupo y recrea el schema desde cero (PUC sembrado de nuevo).
     *
     * En todos los modos las notas actuales quedan archivadas con el período anterior.
     */
    public function transfer(string $tenantId, int $newGroupId, string $mode): void
    {
        $tenant = Tenant::findOrFail($tenantId);

        $this->archiveCurrentScores($tenant);

        $tenant->update(['group_id' => $newGroupId]);

        match ($mode) {
            'reset' => $this->resetTransactionalData($tenant),
            'fresh' => $this->recreateSchema($tenant),
            default => null,
        };
    }

    /** Marca las notas vigentes con el período actual y las archiva. */
    private function archiveCurrentScores(Tenant $tenant): void
    {
        $period = $tenant->group?->period ?? now()->format('Y').'-'.ceil(now()->month / 6);

        StudentScore::where('tenant_id', $tenant->id)
            ->whereNull('archived_at')
            ->update([
                'period' => $period,
                'archived_at' => now(),
            ]);
    }

    /**
     * Borra datos transaccionales del tenant conservando PUC, configuración,
     * terceros, productos y configuraciones de retención.
     */
    private function resetTransactionalData(Tenant $tenant): void
    {
        $tenant->run(function () {
            DB::statement('
                TRUNCATE
                    fe_eventos_receptor,
                    fe_eventos,
                    fe_detalles_factura,
                    fe_notas_credito,
                    fe_facturas,
                    fe_resoluciones,
                    bank_reconciliation_items,
                    bank_reconciliations,
                    fixed_assets,
                    journal_lines,
                    journal_entries,
                    cash_receipt_items,
                    cash_receipts,
                    credit_note_lines,
                    credit_notes,
                    debit_notes,
                    invoice_lines,
                    invoices,
                    payment_items,
                    payments,
                    purchase_invoice_lines,
                    purchase_invoices,
                    purchase_order_lines,
                    purchase_orders,
                    company_summary
                RESTART IDENTITY CASCADE
            ');
        });
    }

    /** Elimina el schema completo y lo recrea con migraciones + PUC. */
    private function recreateSchema(Tenant $tenant): void
    {
        $schemaName = $tenant->tenancy_db_name;

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        DB::statement("DROP SCHEMA IF EXISTS \"{$schemaName}\" CASCADE");
        DB::statement("CREATE SCHEMA \"{$schemaName}\"");

        tenancy()->initialize($tenant);

        try {
            $migrator = app('migrator');
            $migrator->setConnection('tenant');

            if (! $migrator->repositoryExists()) {
                $migrator->getRepository()->createRepository();
            }

            $migrator->run(database_path('migrations/tenant'));
            app(TenantDatabaseSeeder::class)->run();
        } finally {
            tenancy()->end();
        }
    }
}
