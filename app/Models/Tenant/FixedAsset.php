<?php

namespace App\Models\Tenant;

use App\Enums\FixedAssetCategory;
use App\Enums\FixedAssetStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'modo', 'code', 'name', 'description', 'category',
        'acquisition_date', 'cost', 'salvage_value',
        'useful_life_months', 'accumulated_depreciation',
        'last_depreciation_date', 'status', 'notes',
    ];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'last_depreciation_date' => 'date',
            'cost' => 'float',
            'salvage_value' => 'float',
            'accumulated_depreciation' => 'float',
            'category' => FixedAssetCategory::class,
            'status' => FixedAssetStatus::class,
        ];
    }

    /** Cuota de depreciación mensual (línea recta). */
    public function monthlyDepreciation(): float
    {
        return round(($this->cost - $this->salvage_value) / $this->useful_life_months, 2);
    }

    /** Valor en libros actual. */
    public function bookValue(): float
    {
        return max(0, $this->cost - $this->accumulated_depreciation);
    }

    /** Porcentaje de depreciación acumulada. */
    public function depreciationProgress(): float
    {
        $depreciable = $this->cost - $this->salvage_value;
        if ($depreciable <= 0) {
            return 100;
        }

        return min(100, round(($this->accumulated_depreciation / $depreciable) * 100, 1));
    }

    /** Meses depreciados. */
    public function monthsDepreciated(): int
    {
        $depreciable = $this->cost - $this->salvage_value;
        if ($depreciable <= 0 || $this->monthlyDepreciation() <= 0) {
            return $this->useful_life_months;
        }

        return (int) round($this->accumulated_depreciation / $this->monthlyDepreciation());
    }

    public function isActive(): bool
    {
        return $this->status === FixedAssetStatus::Activo;
    }

    public static function nextCode(): string
    {
        $last = static::withTrashed()->max('code');
        if (! $last) {
            return 'AF-001';
        }

        $num = (int) preg_replace('/[^0-9]/', '', $last) + 1;

        return 'AF-'.str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
