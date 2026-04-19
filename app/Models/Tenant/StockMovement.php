<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'modo',
        'product_id',
        'tipo',
        'qty',
        'costo_unitario',
        'costo_total',
        'referencia_tipo',
        'referencia_id',
        'third_id',
        'saldo_qty',
        'saldo_valor',
        'fecha',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:4',
            'costo_unitario' => 'decimal:4',
            'costo_total' => 'decimal:2',
            'saldo_qty' => 'decimal:4',
            'saldo_valor' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function third(): BelongsTo
    {
        return $this->belongsTo(Third::class);
    }

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }
}
