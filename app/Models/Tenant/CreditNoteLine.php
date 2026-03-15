<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteLine extends Model
{
    protected $fillable = [
        'credit_note_id', 'invoice_line_id', 'description',
        'qty', 'unit_price', 'tax_rate',
        'line_subtotal', 'line_tax', 'line_total',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'float',
        'tax_rate' => 'integer',
        'line_subtotal' => 'float',
        'line_tax' => 'float',
        'line_total' => 'float',
    ];

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function invoiceLine(): BelongsTo
    {
        return $this->belongsTo(InvoiceLine::class);
    }
}
