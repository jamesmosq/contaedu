<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Group;
use App\Models\Central\TeacherTransfer;
use App\Models\Central\Tenant;
use App\Models\Tenant\BankAccount;
use App\Services\BankService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.teacher')]
#[Title('Enviar dinero')]
class EnviarDinero extends Component
{
    public string $mode = 'grupal';

    public int $selectedGroupId = 0;

    public string $searchCedula = '';

    public string $selectedTenantId = '';

    public string $amount = '';

    public string $description = 'Capital otorgado por docente';

    public bool $showPreview = false;

    public bool $showConfirm = false;

    /** @var array<int, array{id: string, name: string, company: string, hasAccount: bool}> */
    public array $previewList = [];

    public function updatedMode(): void
    {
        $this->resetPreview();
    }

    public function updatedSelectedGroupId(): void
    {
        $this->resetPreview();
    }

    private function resetPreview(): void
    {
        $this->showPreview = false;
        $this->showConfirm = false;
        $this->previewList = [];
        $this->selectedTenantId = '';
    }

    public function loadGroupPreview(): void
    {
        if (! $this->selectedGroupId) {
            $this->dispatch('notify', type: 'error', message: 'Selecciona un grupo.');

            return;
        }

        $group = Group::where('teacher_id', auth()->id())->find($this->selectedGroupId);

        if (! $group) {
            $this->dispatch('notify', type: 'error', message: 'Grupo no encontrado.');

            return;
        }

        $tenants = Tenant::where('group_id', $group->id)
            ->where('type', 'student')
            ->orderBy('student_name')
            ->get();

        if ($tenants->isEmpty()) {
            $this->dispatch('notify', type: 'warning', message: 'Este grupo no tiene estudiantes.');

            return;
        }

        $this->previewList = $tenants->map(function (Tenant $tenant) {
            $hasAccount = $tenant->run(fn () => BankAccount::where('es_principal', true)->exists());

            return [
                'id' => $tenant->id,
                'name' => $tenant->student_name,
                'company' => $tenant->company_name,
                'hasAccount' => $hasAccount,
            ];
        })->toArray();

        $this->showPreview = true;
    }

    public function searchIndividual(): void
    {
        $cedula = trim($this->searchCedula);

        if (empty($cedula)) {
            $this->dispatch('notify', type: 'error', message: 'Ingresa una cédula.');

            return;
        }

        $myGroupIds = Group::where('teacher_id', auth()->id())->pluck('id');

        $tenant = Tenant::where('id', $cedula)
            ->whereIn('group_id', $myGroupIds)
            ->where('type', 'student')
            ->first();

        if (! $tenant) {
            $this->dispatch('notify', type: 'error', message: 'Estudiante no encontrado en tus grupos.');
            $this->resetPreview();

            return;
        }

        $hasAccount = $tenant->run(fn () => BankAccount::where('es_principal', true)->exists());

        $this->selectedTenantId = $tenant->id;
        $this->previewList = [[
            'id' => $tenant->id,
            'name' => $tenant->student_name,
            'company' => $tenant->company_name,
            'hasAccount' => $hasAccount,
        ]];
        $this->showPreview = true;
    }

    public function openConfirm(): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:1000', 'max:9999999999'],
            'description' => ['required', 'string', 'max:200'],
        ], [
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'Ingresa un valor numérico válido.',
            'amount.min' => 'El monto mínimo es $1.000.',
            'description.required' => 'La descripción es obligatoria.',
        ]);

        $recipients = collect($this->previewList)->where('hasAccount', true)->count();

        if ($recipients === 0) {
            $this->dispatch('notify', type: 'error', message: 'Ningún estudiante tiene cuenta bancaria activa.');

            return;
        }

        $this->showConfirm = true;
    }

    public function enviar(): void
    {
        $amount = (float) $this->amount;
        $description = $this->description;
        $reached = 0;

        $tenantIds = collect($this->previewList)
            ->where('hasAccount', true)
            ->pluck('id');

        // Pre-cargar modelos Central antes de cambiar contexto de tenancy
        $tenants = Tenant::whereIn('id', $tenantIds)->get();

        foreach ($tenants as $tenant) {
            $success = $tenant->run(function () use ($amount, $description): bool {
                $cuenta = BankAccount::where('es_principal', true)->first();

                if (! $cuenta) {
                    return false;
                }

                $tx = BankService::registrarTransaccion($cuenta, 'consignacion', $amount, $description);
                BankService::generarAsientoBancario($tx, $cuenta);

                return true;
            });

            if ($success) {
                $reached++;
            }
        }

        $skipped = count($this->previewList) - $reached;

        $targetName = $this->mode === 'grupal'
            ? (Group::find($this->selectedGroupId)?->name ?? 'Grupo')
            : ($this->previewList[0]['name'] ?? 'Estudiante');

        TeacherTransfer::create([
            'teacher_id' => auth()->id(),
            'group_id' => $this->mode === 'grupal' ? ($this->selectedGroupId ?: null) : null,
            'tenant_id' => $this->mode === 'individual' ? ($this->selectedTenantId ?: null) : null,
            'target_name' => $targetName,
            'amount' => $amount,
            'description' => $description,
            'mode' => $this->mode,
            'students_reached' => $reached,
            'students_skipped' => $skipped,
        ]);

        $msg = $skipped > 0
            ? "{$reached} estudiante(s) recibieron el dinero. {$skipped} omitidos (sin cuenta bancaria)."
            : "{$reached} estudiante(s) recibieron el dinero correctamente.";

        $this->reset(['amount', 'showPreview', 'showConfirm', 'previewList', 'selectedGroupId', 'selectedTenantId', 'searchCedula']);
        $this->description = 'Capital otorgado por docente';

        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function render(): mixed
    {
        $groups = Group::where('teacher_id', auth()->id())
            ->orderBy('name')
            ->get();

        $history = TeacherTransfer::where('teacher_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('livewire.teacher.enviar-dinero', compact('groups', 'history'));
    }
}
