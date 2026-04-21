<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReceiptItem extends Model
{
    protected $fillable = ['cash_receipt_id', 'invoice_id', 'fe_factura_id', 'amount_applied'];

    protected $casts = [
        'amount_applied' => 'float',
    ];

    public function cashReceipt(): BelongsTo
    {
        return $this->belongsTo(CashReceipt::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feFactura(): BelongsTo
    {
        return $this->belongsTo(FeFactura::class, 'fe_factura_id');
    }
}
