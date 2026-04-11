<?php
namespace App\Models\Tenant;

use App\Enums\ReceiptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = ['modo', 'third_id', 'date', 'total', 'notes', 'status'];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }

    protected $casts = [
        'date'   => 'date',
        'status' => ReceiptStatus::class,
        'total'  => 'float',
    ];

    public function third(): BelongsTo
    {
        return $this->belongsTo(Third::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CashReceiptItem::class);
    }
}
