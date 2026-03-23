<?php

namespace App\Models\Tenant;

use App\Enums\EventoReceptorEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeEventoReceptor extends Model
{
    protected $table = 'fe_eventos_receptor';

    protected $fillable = [
        'factura_id',
        'tipo_evento',
        'cude_evento',
        'fecha_evento',
        'observaciones',
        'estado',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evento' => 'datetime',
            'tipo_evento' => EventoReceptorEnum::class,
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FeFactura::class, 'factura_id');
    }
}
