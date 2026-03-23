<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeNotaCredito extends Model
{
    use SoftDeletes;

    protected $table = 'fe_notas_credito';

    protected $fillable = [
        'factura_origen_id',
        'resolucion_id',
        'numero_completo',
        'cude',
        'fecha_emision',
        'hora_emision',
        'codigo_concepto',
        'descripcion_concepto',
        'subtotal',
        'valor_iva',
        'total',
        'estado',
        'xml_nota',
        'xml_application_response',
        'fecha_validacion_dian',
        'mensaje_dian',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'hora_emision' => 'datetime:H:i:s',
            'fecha_validacion_dian' => 'datetime',
            'subtotal' => 'decimal:2',
            'valor_iva' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function facturaOrigen(): BelongsTo
    {
        return $this->belongsTo(FeFactura::class, 'factura_origen_id');
    }

    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(FeResolucion::class, 'resolucion_id');
    }
}
