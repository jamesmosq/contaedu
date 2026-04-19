<?php

namespace App\Livewire\Tenant\ActivosFijos;

use App\Enums\FixedAssetCategory;
use App\Models\Tenant\FixedAsset;
use App\Services\FixedAssetService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Activos fijos')]
class Index extends Component
{
    // ── Formulario nuevo activo ──────────────────────────────────────────────
    public bool $showForm = false;

    public string $fa_code = '';

    public string $fa_name = '';

    public string $fa_desc = '';

    public string $fa_category = 'equipo_computo';

    public string $fa_date = '';

    public float $fa_cost = 0;

    public float $fa_salvage = 0;

    public int $fa_months = 36;

    public string $fa_notes = '';

    public string $fa_forma_pago = 'contado';

    // ── Depreciación ────────────────────────────────────────────────────────
    public string $dep_period = '';

    public bool $showDepResult = false;

    public array $depResult = [];

    public function mount(): void
    {
        $this->fa_date = now()->toDateString();
        $this->dep_period = now()->format('Y-m');
    }

    public function openForm(): void
    {
        $this->reset(['fa_name', 'fa_desc', 'fa_notes', 'fa_cost', 'fa_salvage', 'fa_forma_pago']);
        $this->fa_code = FixedAsset::nextCode();
        $this->fa_date = now()->toDateString();
        $this->fa_category = 'equipo_computo';
        $this->fa_months = 36;
        $this->showForm = true;
    }

    public function updatedFaCategory(): void
    {
        $cat = FixedAssetCategory::from($this->fa_category);
        $this->fa_months = $cat->vidaUtilMesesDefecto();
    }

    public function saveAsset(FixedAssetService $service): void
    {
        $this->validate([
            'fa_code' => ['required', 'string', 'max:20', Rule::unique('fixed_assets', 'code')],
            'fa_name' => ['required', 'string', 'max:200'],
            'fa_category' => ['required', 'in:'.implode(',', array_column(FixedAssetCategory::cases(), 'value'))],
            'fa_date' => ['required', 'date'],
            'fa_cost' => ['required', 'numeric', 'min:0.01'],
            'fa_salvage' => ['nullable', 'numeric', 'min:0'],
            'fa_months' => ['required', 'integer', 'min:1', 'max:600'],
            'fa_forma_pago' => ['required', 'in:contado,credito'],
        ]);

        $service->create([
            'code' => $this->fa_code,
            'name' => $this->fa_name,
            'description' => $this->fa_desc ?: null,
            'category' => $this->fa_category,
            'acquisition_date' => $this->fa_date,
            'cost' => $this->fa_cost,
            'salvage_value' => $this->fa_salvage ?: 0,
            'useful_life_months' => $this->fa_months,
            'notes' => $this->fa_notes ?: null,
            'forma_pago' => $this->fa_forma_pago,
        ]);

        $this->showForm = false;
        $this->dispatch('notify', type: 'success', message: 'Activo registrado correctamente.');
    }

    public function runDepreciation(FixedAssetService $service): void
    {
        $period = Carbon::createFromFormat('Y-m', $this->dep_period)->startOfMonth();
        $result = $service->runMonthlyDepreciation($period);

        $this->depResult = $result;
        $this->showDepResult = true;

        if ($result['registrados'] > 0) {
            $this->dispatch('notify', type: 'success', message: 'Depreciación registrada: $'.number_format($result['total_depreciacion'], 0, ',', '.'));
        } else {
            $this->dispatch('notify', type: 'info', message: 'No hay activos pendientes de depreciar en ese período.');
        }
    }

    public function retire(int $id, FixedAssetService $service): void
    {
        $asset = FixedAsset::findOrFail($id);
        $service->retire($asset);
        $this->dispatch('notify', type: 'success', message: 'Activo dado de baja.');
    }

    public function render(): mixed
    {
        $assets = FixedAsset::orderBy('acquisition_date')->get();
        $categories = FixedAssetCategory::cases();

        return view('livewire.tenant.activos-fijos.index', compact('assets', 'categories'))
            ->title('Activos Fijos');
    }
}
