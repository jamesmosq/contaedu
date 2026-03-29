<?php

namespace App\Livewire\Tenant\Config;

use App\Models\Central\CiiuCode;
use App\Models\Tenant\CompanyConfig as CompanyConfigModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Configuración')]
class CompanyConfig extends Component
{
    public string $nit = '';

    public string $razon_social = '';

    public string $regimen = 'simplificado';

    public string $ciiu_code = '';

    public string $ciiu_description = '';

    public string $direccion = '';

    public string $telefono = '';

    public string $email = '';

    public string $prefijo_factura = 'FV';

    public string $resolucion_dian = '';

    public function mount(): void
    {
        $config = CompanyConfigModel::first();
        if ($config) {
            $this->fill($config->only(['nit', 'razon_social', 'regimen', 'prefijo_factura']));
            $this->ciiu_code = $config->ciiu_code ?? '';
            $this->ciiu_description = $config->ciiu_description ?? '';
            $this->direccion = $config->direccion ?? '';
            $this->telefono = $config->telefono ?? '';
            $this->email = $config->email ?? '';
            $this->resolucion_dian = $config->resolucion_dian ?? '';
        } else {
            // Funciona en modo estudiante, demo y auditoría porque tenancy()->tenant
            // siempre está inicializado cuando se llega a este componente.
            $tenant = tenancy()->tenant;
            $this->nit = $tenant?->nit_empresa ?? '';
            $this->razon_social = $tenant?->company_name ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'nit' => ['required', 'string', 'max:20'],
            'razon_social' => ['required', 'string', 'max:150'],
            'regimen' => ['required', 'in:simplificado,comun,gran_contribuyente'],
            'ciiu_code' => ['nullable', 'string', 'max:6'],
            'ciiu_description' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'prefijo_factura' => ['required', 'string', 'max:5'],
            'resolucion_dian' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function updatedCiiuCode(string $value): void
    {
        if (! $value) {
            $this->ciiu_description = '';

            return;
        }

        $ciiu = CiiuCode::where('code', $value)->first();
        $this->ciiu_description = $ciiu?->name ?? '';
    }

    public function save(): void
    {
        $this->validate();

        CompanyConfigModel::updateOrCreate(
            ['id' => CompanyConfigModel::first()?->id],
            [
                'nit' => $this->nit,
                'razon_social' => $this->razon_social,
                'regimen' => $this->regimen,
                'ciiu_code' => $this->ciiu_code ?: null,
                'ciiu_description' => $this->ciiu_description ?: null,
                'direccion' => $this->direccion ?: null,
                'telefono' => $this->telefono ?: null,
                'email' => $this->email ?: null,
                'prefijo_factura' => $this->prefijo_factura,
                'resolucion_dian' => $this->resolucion_dian ?: null,
            ]
        );

        $this->dispatch('notify', type: 'success', message: 'Configuración guardada correctamente.');
    }

    public function render(): mixed
    {
        $ciiuCodes = CiiuCode::where('active', true)->orderBy('code')->get();

        return view('livewire.tenant.config.company-config', compact('ciiuCodes'))
            ->title('Configuración de empresa');
    }
}
