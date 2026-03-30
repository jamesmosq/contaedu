<?php

namespace App\Services;

use App\Models\Central\Tenant;

class StudentActivityService
{
    /**
     * Registra actividad del estudiante actualizando last_activity_at en el tenant central.
     *
     * Solo actúa si hay un tenant inicializado (contexto de estudiante activo).
     * Se llama desde InvoiceService, PurchaseService y JournalEntryService
     * al confirmar documentos.
     */
    public static function record(): void
    {
        if (! tenancy()->initialized) {
            return;
        }

        $tenantId = tenancy()->tenant->getTenantKey();

        // Actualiza directamente en la BD central (Tenant siempre usa conexión central).
        Tenant::where('id', $tenantId)
            ->update(['last_activity_at' => now()]);
    }
}
