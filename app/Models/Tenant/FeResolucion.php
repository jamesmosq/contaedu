<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeResolucion extends Model
{
    use SoftDeletes;

    protected $table = 'fe_resoluciones';

    protected $fillable = [
        'numero_resolucion',
        'prefijo',
        'numero_desde',
        'numero_hasta',
        'numero_actual',
        'fecha_desde',
        'fecha_hasta',
        'clave_tecnica',
        'ambiente',
        'activa',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'numero_desde' => 'integer',
            'numero_hasta' => 'integer',
            'numero_actual' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(FeFactura::class, 'resolucion_id');
    }

    public function estaVigente(): bool
    {
        $hoy = now()->toDateString();

        return $this->activa
            && $this->fecha_desde->toDateString() <= $hoy
            && $this->fecha_hasta->toDateString() >= $hoy;
    }

    public function rangoDisponible(): int
    {
        return max(0, $this->numero_hasta - $this->numero_actual + 1);
    }

    public function rangoAgotado(): bool
    {
        return $this->numero_actual > $this->numero_hasta;
    }
}
