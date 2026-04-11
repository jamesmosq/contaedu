<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntercompanyInvoice extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'intercompany_invoices';

    protected $fillable = [
        'seller_tenant_id',
        'buyer_tenant_id',
        'group_id',
        'consecutive',
        'status',
        'subtotal',
        'iva',
        'retencion_fuente',
        'retencion_iva',
        'retencion_ica',
        'total',
        'concepto',
        'gasto_code_comprador',
        'buyer_bank_account_id',
        'buyer_bank',
        'seller_bank_account_id',
        'seller_bank',
        'gmf_total',
        'comision_ach',
        'rechazo_motivo',
        'accepted_at',
        'anulada_by',
        'anulada_at',
        'anulacion_motivo',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'         => 'decimal:2',
            'iva'              => 'decimal:2',
            'retencion_fuente' => 'decimal:2',
            'retencion_iva'    => 'decimal:2',
            'retencion_ica'    => 'decimal:2',
            'total'            => 'decimal:2',
            'accepted_at'      => 'datetime',
            'anulada_at'       => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'seller_tenant_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'buyer_tenant_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(IntercompanyInvoiceItem::class);
    }

    public function isPendiente(): bool { return $this->status === 'pendiente'; }
    public function isAceptada(): bool  { return $this->status === 'aceptada'; }
    public function isRechazada(): bool { return $this->status === 'rechazada'; }
    public function isAnulada(): bool   { return $this->status === 'anulada'; }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'aceptada'  => 'Aceptada',
            'rechazada' => 'Rechazada',
            'anulada'   => 'Anulada',
            default     => $this->status,
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'pendiente' => 'bg-amber-100 text-amber-800',
            'aceptada'  => 'bg-green-100 text-green-800',
            'rechazada' => 'bg-red-100 text-red-800',
            'anulada'   => 'bg-slate-100 text-slate-600',
            default     => 'bg-slate-100 text-slate-700',
        };
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'anulada_by');
    }

    /** Genera el consecutivo para un vendedor dado. */
    public static function nextConsecutive(string $sellerTenantId): string
    {
        $count = self::where('seller_tenant_id', $sellerTenantId)->withTrashed()->count();

        return 'NI-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }
}
