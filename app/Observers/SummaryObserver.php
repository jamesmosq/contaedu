<?php

namespace App\Observers;

use App\Models\Tenant\CompanySummary;

/**
 * Actualiza el resumen materializado de la empresa (company_summary) cada vez
 * que cambian facturas de venta, facturas de compra o líneas de asiento.
 *
 * El Observer se registra sobre Invoice, PurchaseInvoice y JournalLine.
 * Como estos modelos son de tenant, el contexto (search_path) ya está establecido
 * cuando el Observer se dispara, por lo que CompanySummary escribe en el schema correcto.
 */
class SummaryObserver
{
    public function saved(mixed $model): void
    {
        CompanySummary::recalculate();
    }

    public function deleted(mixed $model): void
    {
        CompanySummary::recalculate();
    }
}
