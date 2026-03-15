<?php

namespace App\Models\Tenant;

use App\Enums\ProductUnit;
use App\Enums\TaxRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'unit',
        'sale_price',
        'cost_price',
        'inventory_account_id',
        'revenue_account_id',
        'cogs_account_id',
        'tax_rate',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'unit' => ProductUnit::class,
            'tax_rate' => TaxRate::class,
        ];
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'inventory_account_id');
    }

    public function revenueAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'revenue_account_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }
}
