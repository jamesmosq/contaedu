<?php

namespace App\Livewire\Teacher;

use App\Enums\TransferMode;
use App\Models\Central\Tenant;
use App\Services\TransferRequestService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class StudentSearch extends Component
{
    public string $cedula = '';

    public string $transferMode = '';

    public string $notes = '';

    public bool $showModal = false;

    public bool $showConfirm = false;

    /** Tenant encontrado tras la búsqueda. */
    public ?string $foundTenantId = null;

    public function search(): void
    {
        $this->validate(['cedula' => 'required|string|max:20']);

        $cedula = trim($this->cedula);

        // No puede buscar su propio grupo.
        $myGroupIds = auth()->user()->teacherGroups()->pluck('id');

        $tenant = Tenant::where('id', $cedula)
            ->where('type', 'student')
            ->first();

        if (! $tenant) {
            $this->addError('cedula', 'No existe ningún estudiante con esa cédula en la plataforma.');
            $this->foundTenantId = null;

            return;
        }

        if ($myGroupIds->contains($tenant->group_id)) {
            $this->addError('cedula', 'Ese estudiante ya pertenece a uno de tus grupos.');
            $this->foundTenantId = null;

            return;
        }

        $this->foundTenantId = $tenant->id;
        $this->showModal = true;
        $this->transferMode = TransferMode::Keep->value;
    }

    #[Computed]
    public function foundTenant(): ?Tenant
    {
        if (! $this->foundTenantId) {
            return null;
        }

        return Tenant::with('group.institution')->find($this->foundTenantId);
    }

    #[Computed]
    public function myGroups(): Collection
    {
        return auth()->user()
            ->teacherGroups()
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function transferModes(): array
    {
        return TransferMode::cases();
    }

    public string $targetGroupId = '';

    public function confirmAction(): void
    {
        $rules = [
            'targetGroupId' => 'required|integer|exists:groups,id',
            'transferMode' => 'required|in:keep,reset,fresh',
        ];

        if ($this->foundTenant?->isFree()) {
            $this->validate($rules);
            $this->showConfirm = true;
        } else {
            $this->validate(array_merge($rules, [
                'notes' => 'nullable|string|max:500',
            ]));
            $this->showConfirm = true;
        }
    }

    public function execute(): void
    {
        $tenant = Tenant::where('id', $this->foundTenantId)
            ->where('type', 'student')
            ->firstOrFail();

        $mode = TransferMode::from($this->transferMode);

        $service = app(TransferRequestService::class);

        try {
            if ($tenant->isFree()) {
                $service->claim(
                    claimer: auth()->user(),
                    tenantId: $tenant->id,
                    targetGroupId: (int) $this->targetGroupId,
                    mode: $mode,
                    notes: $this->notes ?: null,
                );

                session()->flash('success', "El estudiante {$tenant->student_name} fue incorporado exitosamente.");
            } else {
                $service->request(
                    requester: auth()->user(),
                    tenantId: $tenant->id,
                    targetGroupId: (int) $this->targetGroupId,
                    mode: $mode,
                    notes: $this->notes ?: null,
                );

                session()->flash('success', "Solicitud enviada. El superadministrador revisará la transferencia de {$tenant->student_name}.");
            }
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->reset(['cedula', 'foundTenantId', 'transferMode', 'notes', 'targetGroupId', 'showModal', 'showConfirm']);
        unset($this->foundTenant, $this->myGroups);
    }

    public function closeModal(): void
    {
        $this->reset(['foundTenantId', 'transferMode', 'notes', 'targetGroupId', 'showModal', 'showConfirm']);
        unset($this->foundTenant);
    }

    public function render(): View
    {
        return view('livewire.teacher.student-search')
            ->title('Buscar estudiante — Docente');
    }
}
