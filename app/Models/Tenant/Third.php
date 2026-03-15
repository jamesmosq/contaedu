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
        'phone',
        'email',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'type' => ThirdType::class,
        ];
    }

    public function scopeClientes($query): mixed
    {
        return $query->whereIn('type', [ThirdType::Cliente->value, ThirdType::Ambos->value]);
    }

    public function scopeProveedores($query): mixed
    {
        return $query->whereIn('type', [ThirdType::Proveedor->value, ThirdType::Ambos->value]);
    }
}
