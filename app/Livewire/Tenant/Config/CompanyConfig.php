<?php

namespace App\Livewire\Tenant\Config;

use App\Models\Tenant\CompanyConfig as CompanyConfigModel;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class CompanyConfig extends Component
{
    public string $nit = '';
    public string $razon_social = '';
    public string $regimen = 'simplificado';
    public string $direccion = '';
    public string $telefono = '';
    public string $email = '';
    public string $prefijo_factura = 'FV';
    public string $resolucion_dian = '';
    public bool $saved = false;

    public function mount(): void
    {
        $config = CompanyConfigModel::first();
        if ($config) {
            $this->fill($config->only(['nit', 'razon_social', 'regimen', 'direccion', 'telefono', 'email', 'prefijo_factura', 'resolucion_dian']));
            $this->direccion = $config->direccion ?? '';
            $this->telefono = $config->telefono ?? '';
            $this->email = $config->email ?? '';
            $this->resolucion_dian = $config->resolucion_dian ?? '';
        } else {
            // Tomar datos de la empresa del tenant autenticado
            $student = auth('student')->user();
            $this->nit = $student->nit_empresa;
            $this->razon_social = $student->company_name;
        }
    }

    public function rules(): array
    {
        return [
            'nit'              => ['required', 'string', 'max:20'],
            'razon_social'     => ['required', 'string', 'max:150'],
            'regimen'          => ['required', 'in:simplificado,comun,gran_contribuyente'],
            'direccion'        => ['nullable', 'string', 'max:200'],
            'telefono'         => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:100'],
            'prefijo_factura'  => ['required', 'string', 'max:5'],
            'resolucion_dian'  => ['nullable', 'string', 'max:100'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        CompanyConfigModel::updateOrCreate(
            ['id' => CompanyConfigModel::first()?->id],
            [
                'nit'             => $this->nit,
                'razon_social'    => $this->razon_social,
                'regimen'         => $this->regimen,
                'direccion'       => $this->direccion ?: null,
                'telefono'        => $this->telefono ?: null,
                'email'           => $this->email ?: null,
                'prefijo_factura' => $this->prefijo_factura,
                'resolucion_dian' => $this->resolucion_dian ?: null,
            ]
        );

        $this->saved = true;
    }

    public function render(): mixed
    {
        return view('livewire.tenant.config.company-config')
            ->title('Configuración de empresa');
    }
}
