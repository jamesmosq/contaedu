<?php

namespace App\Livewire\Tenant\Terceros;

use App\Enums\ThirdType;
use App\Models\Tenant\Third;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.tenant')]
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

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'in:cc,nit,ce,pasaporte'],
            'document'      => ['required', 'string', 'max:20'],
            'name'          => ['required', 'string', 'max:150'],
            'type'          => ['required', 'in:cliente,proveedor,ambos'],
            'regimen'       => ['required', 'in:simplificado,comun'],
            'address'       => ['nullable', 'string', 'max:200'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'email'         => ['nullable', 'email', 'max:100'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'document_type', 'document', 'name', 'type', 'regimen', 'address', 'phone', 'email']);
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
        $this->type = $third->type->value;
        $this->regimen = $third->regimen;
        $this->address = $third->address ?? '';
        $this->phone = $third->phone ?? '';
        $this->email = $third->email ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        Third::updateOrCreate(
            ['id' => $this->editingId],
            [
                'document_type' => $this->document_type,
                'document'      => $this->document,
                'name'          => $this->name,
                'type'          => $this->type,
                'regimen'       => $this->regimen,
                'address'       => $this->address ?: null,
                'phone'         => $this->phone ?: null,
                'email'         => $this->email ?: null,
                'active'        => true,
            ]
        );

        $this->reset(['showForm', 'editingId', 'document', 'name', 'address', 'phone', 'email']);
    }

    public function delete(int $id): void
    {
        Third::findOrFail($id)->delete();
    }

    public function cancelForm(): void
    {
        $this->reset(['showForm', 'editingId', 'document', 'name', 'address', 'phone', 'email']);
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

        return view('livewire.tenant.terceros.index', compact('thirds'))
            ->title('Terceros');
    }
}
