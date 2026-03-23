<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeDetalleFactura extends Model
{
    protected $table = 'fe_detalles_factura';

    protected $fillable = [
        'factura_id',
        'producto_id',
        'orden',
        'codigo_producto',
        'codigo_estandar',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'precio_unitario',
        'precio_referencia',
        'porcentaje_descuento',
        'valor_descuento',
        'porcentaje_iva',
        'valor_iva',
        'porcentaje_ica',
        'valor_ica',
        'porcentaje_inc',
        'valor_inc',
        'subtotal_linea',
        'total_linea',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'precio_unitario' => 'decimal:4',
            'precio_referencia' => 'decimal:4',
            'porcentaje_descuento' => 'decimal:2',
            'valor_descuento' => 'decimal:2',
            'porcentaje_iva' => 'decimal:2',
            'valor_iva' => 'decimal:2',
            'porcentaje_ica' => 'decimal:4',
            'valor_ica' => 'decimal:2',
            'porcentaje_inc' => 'decimal:2',
            'valor_inc' => 'decimal:2',
            'subtotal_linea' => 'decimal:2',
            'total_linea' => 'decimal:2',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FeFactura::class, 'factura_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
