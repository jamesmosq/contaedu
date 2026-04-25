<?php

namespace App\Models\Tenant;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = ['modo', 'third_id', 'date', 'total', 'notes', 'status', 'medio_pago', 'bank_account_id'];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }

    protected $casts = ['date' => 'date', 'status' => PaymentStatus::class, 'total' => 'float'];

    public function third(): BelongsTo
    {
        return $this->belongsTo(Third::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function isBorrador(): bool
    {
        return $this->status === PaymentStatus::Borrador;
    }
}
