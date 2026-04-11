<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortafolioItem extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'portafolio_items';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'descripcion',
        'tipo',
        'precio',
        'iva',
        'cuenta_ingreso_codigo',
        'cuenta_ingreso_nombre',
        'activo',
    ];

    protected $casts = [
        'precio' => 'float',
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActivo($query): mixed
    {
        return $query->where('activo', true);
    }

    public function scopeDelTenant($query, string $tenantId): mixed
    {
        return $query->where('tenant_id', $tenantId);
    }
}
