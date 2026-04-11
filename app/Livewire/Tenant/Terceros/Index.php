<?php

namespace App\Livewire\Tenant\Terceros;

use App\Models\Central\Municipio;
use App\Models\Tenant\Third;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
#[Title('Terceros')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public bool $showForm = false;

    public string $activeTab = 'basico'; // 'basico' | 'laboral'

    // Campos comunes
    public ?int $editingId = null;

    public string $document_type = 'nit';

    public string $document = '';

    public string $name = '';

    public string $type = 'cliente';

    public string $regimen = 'simplificado';

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public string $municipio_codigo = '';

    public string $municipioSearch = '';

    public string $municipioLabel = '';

    // Campos exclusivos de empleados
    public string $cargo = '';

    public float $salario_basico = 0;

    public string $tipo_contrato = 'indefinido';

    public string $procedimiento_retencion = '1';

    public string $afp = '';

    public string $eps = '';

    public string $arl = '';

    public string $fecha_ingreso = '';

    public string $fecha_retiro = '';

    public function rules(): array
    {
        $rules = [
            'document_type'  => ['required', 'in:cc,nit,ce,pasaporte'],
            'document'       => $this->document_type === 'nit'
                ? ['required', 'string', 'regex:/^\d{6,10}-\d{1}$/', 'max:20']
                : ['required', 'string', 'max:20'],
            'name'           => ['required', 'string', 'max:150'],
            'type'           => ['required', 'in:cliente,proveedor,empleado'],
            'regimen'        => ['required', 'in:simplificado,comun'],
            'address'        => ['nullable', 'string', 'max:200'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:100'],
            'municipio_codigo' => ['nullable', 'string', 'size:5'],
        ];

        if ($this->type === 'empleado') {
            $rules['cargo']                   = ['required', 'string', 'max:100'];
            $rules['salario_basico']           = ['required', 'numeric', 'min:0'];
            $rules['tipo_contrato']            = ['required', 'in:indefinido,fijo,obra_labor,prestacion_servicios'];
            $rules['procedimiento_retencion']  = ['required', 'in:1,2'];
            $rules['fecha_ingreso']            = ['required', 'date'];
            $rules['fecha_retiro']             = ['nullable', 'date', 'after:fecha_ingreso'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'document.regex' => 'El NIT debe incluir el dígito de verificación con guión. Ejemplo: 900123456-7',
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedType(): void
    {
        $this->activeTab = 'basico';

        // Limpiar campos laborales al cambiar a un tipo que no sea empleado
        if ($this->type !== 'empleado') {
            $this->cargo = '';
            $this->salario_basico = 0;
            $this->tipo_contrato = 'indefinido';
            $this->procedimiento_retencion = '1';
            $this->afp = '';
            $this->eps = '';
            $this->arl = '';
            $this->fecha_ingreso = '';
            $this->fecha_retiro = '';
        }
        // Empleado usa CC, no NIT
        if ($this->type === 'empleado' && $this->document_type === 'nit') {
            $this->document_type = 'cc';
        }
    }

    public function openCreate(): void
    {
        $this->resetAll();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $third = Third::findOrFail($id);
        $this->editingId        = $id;
        $this->document_type    = $third->document_type;
        $this->document         = $third->document;
        $this->name             = $third->name;
        $this->type             = in_array($third->type->value, ['cliente', 'proveedor', 'empleado'])
                                    ? $third->type->value
                                    : 'cliente';
        $this->regimen          = $third->regimen;
        $this->address          = $third->address ?? '';
        $this->phone            = $third->phone ?? '';
        $this->email            = $third->email ?? '';
        $this->municipio_codigo = $third->municipio_codigo ?? '';
        $this->municipioSearch  = '';

        if ($this->municipio_codigo) {
            $municipio = Municipio::find($this->municipio_codigo);
            $this->municipioLabel  = $municipio ? $municipio->label : '';
            $this->municipioSearch = $this->municipioLabel;
        } else {
            $this->municipioLabel = '';
        }

        // Campos de empleado
        $this->cargo                  = $third->cargo ?? '';
        $this->salario_basico         = (float) ($third->salario_basico ?? 0);
        $this->tipo_contrato          = $third->tipo_contrato ?? 'indefinido';
        $this->procedimiento_retencion = $third->procedimiento_retencion ?? '1';
        $this->afp                    = $third->afp ?? '';
        $this->eps                    = $third->eps ?? '';
        $this->arl                    = $third->arl ?? '';
        $this->fecha_ingreso          = $third->fecha_ingreso?->toDateString() ?? '';
        $this->fecha_retiro           = $third->fecha_retiro?->toDateString() ?? '';

        $this->showForm = true;
    }

    public function selectMunicipio(string $codigo, string $label): void
    {
        $this->municipio_codigo = $codigo;
        $this->municipioLabel   = $label;
        $this->municipioSearch  = $label;
    }

    public function updatedMunicipioSearch(): void
    {
        if ($this->municipioSearch !== $this->municipioLabel) {
            $this->municipio_codigo = '';
            $this->municipioLabel   = '';
        }
    }

    public function clearMunicipio(): void
    {
        $this->municipio_codigo = '';
        $this->municipioLabel   = '';
        $this->municipioSearch  = '';
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'document_type'    => $this->document_type,
            'document'         => $this->document,
            'name'             => $this->name,
            'type'             => $this->type,
            'regimen'          => $this->regimen,
            'address'          => $this->address ?: null,
            'phone'            => $this->phone ?: null,
            'email'            => $this->email ?: null,
            'municipio_codigo' => $this->municipio_codigo ?: null,
            'active'           => true,
        ];

        if ($this->type === 'empleado') {
            $data['cargo']                   = $this->cargo;
            $data['salario_basico']           = $this->salario_basico;
            $data['tipo_contrato']            = $this->tipo_contrato;
            $data['procedimiento_retencion']  = $this->procedimiento_retencion;
            $data['afp']                      = $this->afp ?: null;
            $data['eps']                      = $this->eps ?: null;
            $data['arl']                      = $this->arl ?: null;
            $data['fecha_ingreso']            = $this->fecha_ingreso ?: null;
            $data['fecha_retiro']             = $this->fecha_retiro ?: null;
            $data['activo_laboralmente']      = true;
        } else {
            // Limpiar campos laborales si se cambia el tipo
            $data['cargo']                   = null;
            $data['salario_basico']           = null;
            $data['tipo_contrato']            = null;
            $data['procedimiento_retencion']  = null;
            $data['afp']                      = null;
            $data['eps']                      = null;
            $data['arl']                      = null;
            $data['fecha_ingreso']            = null;
            $data['fecha_retiro']             = null;
        }

        Third::updateOrCreate(['id' => $this->editingId], $data);

        $label = $this->editingId ? 'actualizado' : 'guardado';
        $this->resetAll();
        $this->dispatch('notify', type: 'success', message: "Tercero {$label} correctamente.");
    }

    public function delete(int $id): void
    {
        Third::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Tercero eliminado.');
    }

    public function cancelForm(): void
    {
        $this->resetAll();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    private function resetAll(): void
    {
        $this->showForm  = false;
        $this->activeTab = 'basico';
        $this->reset([
            'editingId', 'document', 'name', 'address', 'phone', 'email',
            'municipio_codigo', 'municipioSearch', 'municipioLabel',
            'cargo', 'afp', 'eps', 'arl', 'fecha_ingreso', 'fecha_retiro',
        ]);
        $this->document_type           = 'nit';
        $this->type                    = 'cliente';
        $this->regimen                 = 'simplificado';
        $this->salario_basico          = 0;
        $this->tipo_contrato           = 'indefinido';
        $this->procedimiento_retencion = '1';
    }

    public function render(): mixed
    {
        $thirds = Third::query()
            ->when($this->search, fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('document', 'ilike', "%{$this->search}%")
            )
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->orderBy('name')
            ->paginate(15);

        $municipios = collect();
        if (strlen($this->municipioSearch) >= 2 && $this->municipioSearch !== $this->municipioLabel) {
            $municipios = Municipio::where('label', 'ilike', "%{$this->municipioSearch}%")
                ->orWhere('municipio', 'ilike', "%{$this->municipioSearch}%")
                ->orWhere('departamento', 'ilike', "%{$this->municipioSearch}%")
                ->limit(10)
                ->get(['codigo', 'municipio', 'departamento', 'label']);
        }

        $codigosEnUso = $thirds->pluck('municipio_codigo')->filter()->unique()->values();
        $municipioMap = $codigosEnUso->isNotEmpty()
            ? Municipio::whereIn('codigo', $codigosEnUso)->get(['codigo', 'municipio', 'departamento'])->keyBy('codigo')
            : collect();

        return view('livewire.tenant.terceros.index', compact('thirds', 'municipios', 'municipioMap'))
            ->title('Terceros');
    }
}
