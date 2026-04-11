<?php
namespace App\Models\Tenant;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model {
    use SoftDeletes;
    protected $fillable = ['modo','third_id','date','expected_date','status','total','notes'];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }
    protected $casts = ['date'=>'date','expected_date'=>'date','status'=>PurchaseOrderStatus::class,'total'=>'float'];
    public function third(): BelongsTo { return $this->belongsTo(Third::class); }
    public function lines(): HasMany { return $this->hasMany(PurchaseOrderLine::class); }
    public function purchaseInvoices(): HasMany { return $this->hasMany(PurchaseInvoice::class); }
}
