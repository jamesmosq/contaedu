<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceLine extends Model {
    protected $fillable = ['purchase_invoice_id','product_id','description','qty','unit_cost','tax_rate','line_subtotal','line_tax','line_total'];
    protected $casts = ['qty'=>'float','unit_cost'=>'float','tax_rate'=>'integer','line_subtotal'=>'float','line_tax'=>'float','line_total'=>'float'];
    public function purchaseInvoice(): BelongsTo { return $this->belongsTo(PurchaseInvoice::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
