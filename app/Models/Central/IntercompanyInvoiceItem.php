<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntercompanyInvoiceItem extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'intercompany_invoice_items';

    protected $fillable = [
        'intercompany_invoice_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'iva',
        'porcentaje_iva',
        'cuenta_ingreso_codigo',
        'portafolio_item_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad'        => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal'        => 'decimal:2',
            'iva'             => 'decimal:2',
            'porcentaje_iva'  => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(IntercompanyInvoice::class, 'intercompany_invoice_id');
    }
}
