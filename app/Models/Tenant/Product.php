<?php

namespace App\Models\Tenant;

use App\Enums\ProductUnit;
use App\Enums\TaxRate;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'modo',
        'code',
        'name',
        'description',
        'unit',
        'sale_price',
        'cost_price',
        'stock_minimo',
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
            'stock_minimo' => 'decimal:2',
            'unit' => ProductUnit::class,
            'tax_rate' => TaxRate::class,
        ];
    }

    public function stockDecimals(): int
    {
        return in_array($this->unit->value, ['kg', 'lt', 'm', 'otro']) ? 2 : 0;
    }

    public function stockBajo(): bool
    {
        return $this->stock_minimo > 0 && $this->stockActual() <= $this->stock_minimo;
    }

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockActual(): float
    {
        return StockService::stockActual($this);
    }
}
