<?php

namespace App\Models\Central;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'teacher_id',
        'title',
        'instructions',
        'type',
        'monto_minimo',
        'cuenta_puc_requerida',
        'puntos',
        'active',
        'is_global',
        'cloned_from_id',
    ];

    protected function casts(): array
    {
        return [
            'monto_minimo' => 'decimal:2',
            'active' => 'boolean',
            'is_global' => 'boolean',
        ];
    }

    public function scopeGlobal(Builder $query): void
    {
        $query->where('is_global', true);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExerciseAssignment::class);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'factura_venta' => 'Factura de venta',
            'factura_compra' => 'Factura de compra',
            'asiento_manual' => 'Asiento contable',
            'registro_tercero' => 'Registro de tercero',
            'registro_producto' => 'Registro de producto',
            'pago_proveedor' => 'Pago a proveedor',
            default => $type,
        };
    }
}
