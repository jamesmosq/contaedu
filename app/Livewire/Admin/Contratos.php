<?php

namespace App\Livewire\Admin;

use App\Models\Central\Institution;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Contratos')]
class Contratos extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $contractStartsAt = '';

    public string $contractExpiresAt = '';

    public function openEdit(int $id): void
    {
        $inst = Institution::findOrFail($id);
        $this->editingId = $id;
        $this->contractStartsAt = $inst->contract_starts_at?->format('Y-m-d') ?? '';
        $this->contractExpiresAt = $inst->contract_expires_at?->format('Y-m-d') ?? '';
        $this->showForm = true;
    }

    public function saveContract(): void
    {
        $this->validate([
            'contractStartsAt' => ['nullable', 'date'],
            'contractExpiresAt' => ['nullable', 'date', 'after_or_equal:contractStartsAt'],
        ], [
            'contractExpiresAt.after_or_equal' => 'La fecha de fin debe ser posterior al inicio.',
        ]);

        $inst = Institution::findOrFail($this->editingId);

        $startsChanged = $this->contractStartsAt !== ($inst->contract_starts_at?->format('Y-m-d') ?? '');
        $expiresChanged = $this->contractExpiresAt !== ($inst->contract_expires_at?->format('Y-m-d') ?? '');

        $inst->update([
            'contract_starts_at' => $this->contractStartsAt ?: null,
            'contract_expires_at' => $this->contractExpiresAt ?: null,
            // Resetear flags de notificación si cambia la fecha de vencimiento
            'contract_notified_30d' => $expiresChanged ? false : $inst->contract_notified_30d,
            'contract_notified_15d' => $expiresChanged ? false : $inst->contract_notified_15d,
        ]);

        $this->showForm = false;
        $this->dispatch('notify', type: 'success', message: 'Contrato actualizado.');
    }

    public function toggleActive(int $id): void
    {
        $inst = Institution::findOrFail($id);
        $inst->update(['active' => ! $inst->active]);
        $label = $inst->active ? 'habilitada' : 'deshabilitada';
        $this->dispatch('notify', type: $inst->active ? 'success' : 'warning', message: "Institución {$label}.");
    }

    public function render(): mixed
    {
        $institutions = Institution::with('coordinator')
            ->withCount(['groups', 'groups as students_count' => fn ($q) => $q->join('tenants', 'tenants.group_id', '=', 'groups.id')])
            ->orderByRaw('CASE WHEN contract_expires_at IS NULL THEN 2 WHEN contract_expires_at < NOW() THEN 1 ELSE 0 END')
            ->orderBy('contract_expires_at')
            ->get();

        return view('livewire.admin.contratos', compact('institutions'));
    }
}
