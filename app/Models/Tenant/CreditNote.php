<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id', 'date', 'reason',
        'subtotal', 'tax_amount', 'total', 'status',
    ];

    protected $casts = [
        'date' => 'date',
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'total' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CreditNoteLine::class);
    }

    public function fullReference(): string
    {
        return 'NC-'.str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }
}
