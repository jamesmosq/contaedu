<?php
namespace App\Models\Tenant;
use App\Enums\PurchaseInvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model {
    use SoftDeletes;
    protected $fillable = ['third_id','purchase_order_id','supplier_invoice_number','date','due_date','status','subtotal','tax_amount','total','notes'];
    protected $casts = ['date'=>'date','due_date'=>'date','status'=>PurchaseInvoiceStatus::class,'subtotal'=>'float','tax_amount'=>'float','total'=>'float'];
    public function third(): BelongsTo { return $this->belongsTo(Third::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function lines(): HasMany { return $this->hasMany(PurchaseInvoiceLine::class); }
    public function payments(): HasMany { return $this->hasMany(PaymentItem::class); }
    public function isPendiente(): bool { return $this->status === PurchaseInvoiceStatus::Pendiente; }
    public function isPagada(): bool { return $this->status === PurchaseInvoiceStatus::Pagada; }
    public function amountPaid(): float { return (float) $this->payments->sum('amount_applied'); }
    public function balance(): float { return $this->total - $this->amountPaid(); }
}
