<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Account;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class PlanDeCuentas extends Component
{
    public bool $showForm = false;

    public string $search = '';

    // Form
    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'activo';

    public string $nature = 'debito';

    public ?int $parent_id = null;

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:activo,pasivo,patrimonio,ingreso,costo,gasto,orden'],
            'nature' => ['required', 'in:debito,credito'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
        ];
    }

    public function openForm(?int $parentId = null): void
    {
        $this->reset(['editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
        $this->parent_id = $parentId;

        if ($parentId) {
            $parent = Account::find($parentId);
            $this->type = $parent->type;
            $this->nature = $parent->nature;
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $level = $this->parent_id
            ? Account::find($this->parent_id)->level + 1
            : 1;

        Account::create([
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'nature' => $this->nature,
            'parent_id' => $this->parent_id,
            'level' => $level,
            'active' => true,
        ]);

        $this->reset(['showForm', 'editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
        $this->dispatch('notify', type: 'success', message: 'Cuenta guardada correctamente.');
    }

    public function cancelForm(): void
    {
        $this->reset(['showForm', 'editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
    }

    public function render(): mixed
    {
        $accounts = Account::query()
            ->when($this->search, fn ($q) => $q
                ->where('code', 'ilike', "%{$this->search}%")
                ->orWhere('name', 'ilike', "%{$this->search}%")
            )
            ->orderBy('code')
            ->get()
            ->groupBy('level');

        $parentAccounts = Account::where('level', '<', 4)->orderBy('code')->get();

        return view('livewire.tenant.cuentas.plan-de-cuentas', compact('accounts', 'parentAccounts'))
            ->title('Plan de Cuentas');
    }
}
