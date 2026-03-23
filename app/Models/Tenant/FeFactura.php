<?php

namespace App\Models\Tenant;

use App\Enums\EstadoFacturaEnum;
use App\Enums\EventoReceptorEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeFactura extends Model
{
    use SoftDeletes;

    protected $table = 'fe_facturas';

    protected $fillable = [
        'resolucion_id',
        'numero',
        'numero_completo',
        'cufe',
        'tipo_operacion',
        'fecha_emision',
        'hora_emision',
        'estado',
        'nit_emisor',
        'dv_emisor',
        'razon_social_emisor',
        'regimen_fiscal_emisor',
        'tipo_doc_adquirente',
        'num_doc_adquirente',
        'nombre_adquirente',
        'email_adquirente',
        'telefono_adquirente',
        'direccion_adquirente',
        'municipio_adquirente',
        'cliente_id',
        'subtotal',
        'total_descuentos',
        'base_iva',
        'valor_iva',
        'base_ica',
        'valor_ica',
        'base_inc',
        'valor_inc',
        'total_retenciones',
        'total',
        'medio_pago',
        'forma_pago',
        'fecha_vencimiento_pago',
        'xml_factura',
        'xml_application_response',
        'fecha_validacion_dian',
        'codigo_respuesta_dian',
        'mensaje_dian',
        'qr_data',
        'notas',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'hora_emision' => 'datetime:H:i:s',
            'fecha_vencimiento_pago' => 'date',
            'fecha_validacion_dian' => 'datetime',
            'estado' => EstadoFacturaEnum::class,
            'subtotal' => 'decimal:2',
            'total_descuentos' => 'decimal:2',
            'base_iva' => 'decimal:2',
            'valor_iva' => 'decimal:2',
            'base_ica' => 'decimal:2',
            'valor_ica' => 'decimal:2',
            'base_inc' => 'decimal:2',
            'valor_inc' => 'decimal:2',
            'total_retenciones' => 'decimal:2',
            'total' => 'decimal:2',
            'dv_emisor' => 'integer',
        ];
    }

    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(FeResolucion::class, 'resolucion_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Third::class, 'cliente_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FeDetalleFactura::class, 'factura_id')->orderBy('orden');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(FeEvento::class, 'factura_id')->orderBy('created_at');
    }

    public function eventosReceptor(): HasMany
    {
        return $this->hasMany(FeEventoReceptor::class, 'factura_id');
    }

    public function notasCredito(): HasMany
    {
        return $this->hasMany(FeNotaCredito::class, 'factura_origen_id');
    }

    public function tieneAceptacionExpresa(): bool
    {
        return $this->eventosReceptor()
            ->where('tipo_evento', EventoReceptorEnum::AceptacionExpresa->value)
            ->exists();
    }

    public function esBorrador(): bool
    {
        return $this->estado === EstadoFacturaEnum::Borrador;
    }

    public function esValidada(): bool
    {
        return $this->estado === EstadoFacturaEnum::Validada;
    }

    public function esAnulada(): bool
    {
        return $this->estado === EstadoFacturaEnum::Anulada;
    }
}
