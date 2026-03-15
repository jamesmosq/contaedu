<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model {
    protected $fillable = ['purchase_order_id','product_id','description','qty','unit_cost','line_total'];
    protected $casts = ['qty'=>'float','unit_cost'=>'float','line_total'=>'float'];
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
