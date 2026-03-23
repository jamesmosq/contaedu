<?php

namespace App\Services;

use App\Enums\FixedAssetStatus;
use App\Models\Tenant\FixedAsset;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FixedAssetService
{
    public function __construct(private AccountingService $accounting) {}

    /**
     * Crea un activo fijo en estado activo.
     *
     * @param  array{code?: string, name: string, category: string, acquisition_date: string, cost: float, salvage_value?: float, useful_life_months: int, description?: string, notes?: string}  $data
     */
    public function create(array $data): FixedAsset
    {
        return FixedAsset::create([
            'code' => $data['code'] ?? FixedAsset::nextCode(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'acquisition_date' => $data['acquisition_date'],
            'cost' => round((float) $data['cost'], 2),
            'salvage_value' => round((float) ($data['salvage_value'] ?? 0), 2),
            'useful_life_months' => (int) $data['useful_life_months'],
            'status' => FixedAssetStatus::Activo,
            'notes' => $data['notes'] ?? null,
            'accumulated_depreciation' => 0,
        ]);
    }

    /**
     * Calcula y registra la depreciación mensual de todos los activos activos.
     * Si ya se depreció en el período indicado, omite el activo.
     *
     * @return array{registrados: int, total_depreciacion: float, detalles: Collection}
     */
    public function runMonthlyDepreciation(Carbon $period): array
    {
        $assets = FixedAsset::where('status', FixedAssetStatus::Activo)->get();

        $registrados = 0;
        $totalDepreciacion = 0.0;
        $detalles = collect();

        foreach ($assets as $asset) {
            // Ya depreciado este período
            if ($asset->last_depreciation_date &&
                $asset->last_depreciation_date->format('Y-m') === $period->format('Y-m')) {
                $detalles->push([
                    'asset' => $asset->name,
                    'monto' => 0,
                    'estado' => 'ya_depreciado',
                ]);

                continue;
            }

            $cuota = min(
                $asset->monthlyDepreciation(),
                $asset->bookValue() - $asset->salvage_value
            );

            if ($cuota <= 0) {
                $asset->update(['status' => FixedAssetStatus::TotalmenteDepreciado]);
                $detalles->push([
                    'asset' => $asset->name,
                    'monto' => 0,
                    'estado' => 'completado',
                ]);

                continue;
            }

            DB::transaction(function () use ($asset, $cuota, $period) {
                $this->accounting->generateDepreciationEntry($asset, $cuota, $period);

                $nuevaAcum = round($asset->accumulated_depreciation + $cuota, 2);
                $depreciable = $asset->cost - $asset->salvage_value;

                $asset->update([
                    'accumulated_depreciation' => $nuevaAcum,
                    'last_depreciation_date' => $period->endOfMonth()->toDateString(),
                    'status' => $nuevaAcum >= $depreciable
                        ? FixedAssetStatus::TotalmenteDepreciado
                        : FixedAssetStatus::Activo,
                ]);
            });

            $registrados++;
            $totalDepreciacion += $cuota;
            $detalles->push([
                'asset' => $asset->name,
                'monto' => $cuota,
                'estado' => 'registrado',
            ]);
        }

        return [
            'registrados' => $registrados,
            'total_depreciacion' => round($totalDepreciacion, 2),
            'detalles' => $detalles,
        ];
    }

    /**
     * Da de baja un activo (retiro del servicio).
     */
    public function retire(FixedAsset $asset): FixedAsset
    {
        $asset->update(['status' => FixedAssetStatus::DadoDeBaja]);

        return $asset;
    }
}
