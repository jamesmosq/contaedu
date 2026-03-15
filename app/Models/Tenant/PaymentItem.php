<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model {
    protected $fillable = ['payment_id','purchase_invoice_id','amount_applied'];
    protected $casts = ['amount_applied'=>'float'];
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function purchaseInvoice(): BelongsTo { return $this->belongsTo(PurchaseInvoice::class); }
}
