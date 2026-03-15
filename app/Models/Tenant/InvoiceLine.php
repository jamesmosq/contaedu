<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'description', 'qty', 'unit_price',
        'discount_pct', 'tax_rate', 'line_subtotal', 'line_tax', 'line_total',
    ];

    protected $casts = [
        'qty'          => 'float',
        'unit_price'   => 'float',
        'discount_pct' => 'float',
        'tax_rate'     => 'integer',
        'line_subtotal'=> 'float',
        'line_tax'     => 'float',
        'line_total'   => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
