<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CompanySummary extends Model
{
    protected $table = 'company_summary';

    public $timestamps = false;

    protected $fillable = [
        'total_facturas_venta',
        'monto_total_ventas',
        'total_facturas_compra',
        'monto_total_compras',
        'balance_cuadrado',
    ];

    protected function casts(): array
    {
        return [
            'monto_total_ventas' => 'float',
            'monto_total_compras' => 'float',
            'balance_cuadrado' => 'boolean',
        ];
    }

    /**
     * Recalcula el resumen completo desde las tablas del tenant.
     * Llamar dentro del contexto del tenant (tenancy ya inicializado).
     */
    public static function recalculate(): void
    {
        $ventas = Invoice::where('type', 'venta')->where('status', 'emitida');
        $compras = PurchaseInvoice::whereIn('status', ['pendiente', 'pagada']);
        $totalDebit = (float) JournalLine::sum('debit');
        $totalCredit = (float) JournalLine::sum('credit');

        self::updateOrCreate(['id' => 1], [
            'total_facturas_venta' => $ventas->count(),
            'monto_total_ventas' => (float) $ventas->sum('total'),
            'total_facturas_compra' => $compras->count(),
            'monto_total_compras' => (float) $compras->sum('total'),
            'balance_cuadrado' => abs($totalDebit - $totalCredit) < 0.02,
        ]);
    }
}
