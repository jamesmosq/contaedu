<?php
namespace App\Models\Tenant;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model {
    use SoftDeletes;
    protected $fillable = ['third_id','date','total','notes','status','bank_account_id'];
    protected $casts = ['date'=>'date','status'=>PaymentStatus::class,'total'=>'float'];
    public function third(): BelongsTo { return $this->belongsTo(Third::class); }
    public function items(): HasMany { return $this->hasMany(PaymentItem::class); }
    public function isBorrador(): bool { return $this->status === PaymentStatus::Borrador; }
}
