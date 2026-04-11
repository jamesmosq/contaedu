<?php

namespace App\Models\Tenant;

use App\Enums\ThirdType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Third extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_type',
        'document',
        'name',
        'type',
        'regimen',
        'address',
        'municipio_codigo',
        'phone',
        'email',
        'active',
        // Campos exclusivos de empleados
        'cargo',
        'salario_basico',
        'tipo_contrato',
        'procedimiento_retencion',
        'afp',
        'eps',
        'arl',
        'fecha_ingreso',
        'fecha_retiro',
        'activo_laboralmente',
    ];

    protected function casts(): array
    {
        return [
            'active'              => 'boolean',
            'activo_laboralmente' => 'boolean',
            'salario_basico'      => 'float',
            'fecha_ingreso'       => 'date',
            'fecha_retiro'        => 'date',
            'type'                => ThirdType::class,
        ];
    }

    public function esCliente(): bool
    {
        return $this->type === ThirdType::Cliente;
    }

    public function esProveedor(): bool
    {
        return in_array($this->type, [ThirdType::Proveedor, ThirdType::Ambos]);
    }

    public function esEmpleado(): bool
    {
        return $this->type === ThirdType::Empleado;
    }

    public function scopeClientes($query): mixed
    {
        return $query->where('type', ThirdType::Cliente->value);
    }

    public function scopeProveedores($query): mixed
    {
        return $query->whereIn('type', [ThirdType::Proveedor->value, ThirdType::Ambos->value]);
    }

    public function scopeEmpleados($query): mixed
    {
        return $query->where('type', ThirdType::Empleado->value);
    }
}
