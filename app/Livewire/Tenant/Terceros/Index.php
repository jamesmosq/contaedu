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

    // Form fields
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

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'in:cc,nit,ce,pasaporte'],
            'document' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:cliente,proveedor'],
            'regimen' => ['required', 'in:simplificado,comun'],
            'address' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'municipio_codigo' => ['nullable', 'string', 'size:5'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'document_type', 'document', 'name', 'type', 'regimen', 'address', 'phone', 'email', 'municipio_codigo', 'municipioSearch', 'municipioLabel']);
        $this->document_type = 'nit';
        $this->type = 'cliente';
        $this->regimen = 'simplificado';
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $third = Third::findOrFail($id);
        $this->editingId = $id;
        $this->document_type = $third->document_type;
        $this->document = $third->document;
        $this->name = $third->name;
        $this->type = $third->type->value === 'ambos' ? 'cliente' : $third->type->value;
        $this->regimen = $third->regimen;
        $this->address = $third->address ?? '';
        $this->phone = $third->phone ?? '';
        $this->email = $third->email ?? '';
        $this->municipio_codigo = $third->municipio_codigo ?? '';
        $this->municipioSearch = '';

        if ($this->municipio_codigo) {
            $municipio = Municipio::find($this->municipio_codigo);
            $this->municipioLabel = $municipio ? $municipio->label : '';
            $this->municipioSearch = $this->municipioLabel;
        } else {
            $this->municipioLabel = '';
        }

        $this->showForm = true;
    }

    public function selectMunicipio(string $codigo, string $label): void
    {
        $this->municipio_codigo = $codigo;
        $this->municipioLabel = $label;
        $this->municipioSearch = $label;
    }

    public function clearMunicipio(): void
    {
        $this->municipio_codigo = '';
        $this->municipioLabel = '';
        $this->municipioSearch = '';
    }

    public function save(): void
    {
        $this->validate();

        Third::updateOrCreate(
            ['id' => $this->editingId],
            [
                'document_type' => $this->document_type,
                'document' => $this->document,
                'name' => $this->name,
                'type' => $this->type,
                'regimen' => $this->regimen,
                'address' => $this->address ?: null,
                'phone' => $this->phone ?: null,
                'email' => $this->email ?: null,
                'municipio_codigo' => $this->municipio_codigo ?: null,
                'active' => true,
            ]
        );

        $label = $this->editingId ? 'actualizado' : 'guardado';
        $this->reset(['showForm', 'editingId', 'document', 'name', 'address', 'phone', 'email', 'municipio_codigo', 'municipioSearch', 'municipioLabel']);
        $this->dispatch('notify', type: 'success', message: "Tercero {$label} correctamente.");
    }

    public function delete(int $id): void
    {
        Third::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Tercero eliminado.');
    }

    public function cancelForm(): void
    {
        $this->reset(['showForm', 'editingId', 'document', 'name', 'address', 'phone', 'email', 'municipio_codigo', 'municipioSearch', 'municipioLabel']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
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

        // Municipality autocomplete suggestions
        $municipios = collect();
        if (strlen($this->municipioSearch) >= 2 && $this->municipioSearch !== $this->municipioLabel) {
            $municipios = Municipio::where('label', 'ilike', "%{$this->municipioSearch}%")
                ->orWhere('municipio', 'ilike', "%{$this->municipioSearch}%")
                ->orWhere('departamento', 'ilike', "%{$this->municipioSearch}%")
                ->limit(10)
                ->get(['codigo', 'municipio', 'departamento', 'label']);
        }

        // Map of codigo -> label for table display
        $codigosEnUso = $thirds->pluck('municipio_codigo')->filter()->unique()->values();
        $municipioMap = $codigosEnUso->isNotEmpty()
            ? Municipio::whereIn('codigo', $codigosEnUso)->get(['codigo', 'municipio', 'departamento'])->keyBy('codigo')
            : collect();

        return view('livewire.tenant.terceros.index', compact('thirds', 'municipios', 'municipioMap'))
            ->title('Terceros');
    }
}
