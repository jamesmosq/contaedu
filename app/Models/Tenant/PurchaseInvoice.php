<?php

namespace App\Models\Tenant;

use App\Enums\ConceptoRetencion;
use App\Enums\PurchaseInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'modo', 'third_id', 'purchase_order_id', 'supplier_invoice_number',
        'date', 'due_date', 'status', 'subtotal', 'tax_amount', 'total', 'notes',
        // Retenciones
        'retencion_concepto', 'retefte_base', 'retefte_porcentaje',
        'retefte_valor', 'reteiva_valor', 'reteica_valor', 'total_retenciones',
    ];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'status' => PurchaseInvoiceStatus::class,
            'retencion_concepto' => ConceptoRetencion::class,
            'subtotal' => 'float',
            'tax_amount' => 'float',
            'total' => 'float',
            'retefte_base' => 'float',
            'retefte_porcentaje' => 'float',
            'retefte_valor' => 'float',
            'reteiva_valor' => 'float',
            'reteica_valor' => 'float',
            'total_retenciones' => 'float',
        ];
    }

    public function third(): BelongsTo
    {
        return $this->belongsTo(Third::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function isPendiente(): bool
    {
        return $this->status === PurchaseInvoiceStatus::Pendiente;
    }

    public function isPagada(): bool
    {
        return $this->status === PurchaseInvoiceStatus::Pagada;
    }

    public function tieneRetenciones(): bool
    {
        return ($this->total_retenciones ?? 0) > 0;
    }

    public function amountPaid(): float
    {
        return (float) $this->payments->sum('amount_applied');
    }

    /**
     * Saldo pendiente de pago.
     * El campo total ya incluye las retenciones descontadas al momento de confirmar.
     */
    public function balance(): float
    {
        return $this->total - $this->amountPaid();
    }
}
