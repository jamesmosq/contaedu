<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeEvento extends Model
{
    public $timestamps = false;

    protected $table = 'fe_eventos';

    protected $fillable = [
        'factura_id',
        'estado_anterior',
        'estado_nuevo',
        'origen',
        'descripcion',
        'metadata',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FeFactura::class, 'factura_id');
    }
}
