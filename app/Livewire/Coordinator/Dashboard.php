<?php

namespace App\Livewire\Coordinator;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\TransferStudentService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.coordinator')]
#[Title('Panel Coordinador')]
class Dashboard extends Component
{
    // ── Tab activa ──────────────────────────────────────────────────────────
    public string $tab = 'resumen';

    // ── Formulario docente ──────────────────────────────────────────────────
    public bool $showTeacherForm = false;

    public ?int $teacherEditingId = null;

    public string $teacherName = '';

    public string $teacherEmail = '';

    public string $teacherPassword = '';

    // ── Transferencia ───────────────────────────────────────────────────────
    public bool $showTransferModal = false;

    public string $transferTenantId = '';

    public int $transferGroupId = 0;

    public string $transferMode = 'keep';

    // ── Docentes CRUD ────────────────────────────────────────────────────────

    public function openCreateTeacher(): void
    {
        $this->reset(['teacherEditingId', 'teacherName', 'teacherEmail', 'teacherPassword']);
        $this->showTeacherForm = true;
    }

    public function openEditTeacher(int $id): void
    {
        $teacher = User::findOrFail($id);
        $this->teacherEditingId = $id;
        $this->teacherName = $teacher->name;
        $this->teacherEmail = $teacher->email;
        $this->teacherPassword = '';
        $this->showTeacherForm = true;
    }

    public function saveTeacher(): void
    {
        $institution = $this->institution();

        $rules = [
            'teacherName' => ['required', 'string', 'max:150'],
            'teacherEmail' => ['required', 'email', 'max:150', 'unique:users,email'.($this->teacherEditingId ? ",{$this->teacherEditingId}" : '')],
        ];

        if (! $this->teacherEditingId) {
            $rules['teacherPassword'] = ['required', 'string', 'min:6'];
        } elseif ($this->teacherPassword) {
            $rules['teacherPassword'] = ['string', 'min:6'];
        }

        $this->validate($rules);

        if ($this->teacherEditingId) {
            User::findOrFail($this->teacherEditingId)->update(array_filter([
                'name' => $this->teacherName,
                'email' => $this->teacherEmail,
                'password' => $this->teacherPassword ? Hash::make($this->teacherPassword) : null,
            ]));
        } else {
            $teacher = User::create([
                'name' => $this->teacherName,
                'email' => $this->teacherEmail,
                'password' => Hash::make($this->teacherPassword),
                'role' => 'teacher',
            ]);

            Group::create([
                'institution_id' => $institution->id,
                'teacher_id' => $teacher->id,
                'name' => 'Grupo '.date('Y'),
                'period' => date('Y').'-'.ceil(now()->month / 6),
                'active' => true,
            ]);
        }

        $this->showTeacherForm = false;
        $this->reset(['teacherEditingId', 'teacherName', 'teacherEmail', 'teacherPassword']);
        $this->dispatch('notify', type: 'success', message: 'Docente guardado.');
    }

    public function deleteTeacher(int $id): void
    {
        $institution = $this->institution();

        // Verificar que el docente pertenece a esta institución
        $belongs = User::where('id', $id)
            ->where('role', 'teacher')
            ->whereHas('teacherGroups', fn ($q) => $q->where('institution_id', $institution->id))
            ->exists();

        abort_if(! $belongs, 403);

        User::where('id', $id)->where('role', 'teacher')->delete();
        $this->dispatch('notify', type: 'success', message: 'Docente eliminado.');
    }

    // ── Transferencia ────────────────────────────────────────────────────────

    public function openTransfer(string $tenantId): void
    {
        $this->transferTenantId = $tenantId;
        $this->transferGroupId = 0;
        $this->transferMode = 'keep';
        $this->showTransferModal = true;
    }

    public function confirmTransfer(TransferStudentService $service): void
    {
        $institution = $this->institution();

        $this->validate([
            'transferGroupId' => ['required', 'integer', 'min:1', 'exists:groups,id'],
            'transferMode' => ['required', 'in:keep,reset,fresh'],
        ], [
            'transferGroupId.min' => 'Selecciona un grupo destino.',
        ]);

        // Solo grupos de esta institución
        $groupBelongs = Group::where('id', $this->transferGroupId)
            ->where('institution_id', $institution->id)
            ->exists();

        abort_if(! $groupBelongs, 403, 'El grupo no pertenece a tu institución.');

        $tenant = Tenant::findOrFail($this->transferTenantId);
        $newGroup = Group::findOrFail($this->transferGroupId);

        if ($tenant->group_id === $this->transferGroupId) {
            $this->addError('transferGroupId', 'El estudiante ya pertenece a ese grupo.');

            return;
        }

        $service->transfer($this->transferTenantId, $this->transferGroupId, $this->transferMode);

        $this->showTransferModal = false;
        $this->reset(['transferTenantId', 'transferGroupId', 'transferMode']);
        $this->dispatch('notify', type: 'success', message: "Estudiante transferido al grupo «{$newGroup->name}» correctamente.");
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render(): mixed
    {
        $institution = $this->institution();

        $teachers = User::where('role', 'teacher')
            ->whereHas('teacherGroups', fn ($q) => $q->where('institution_id', $institution->id))
            ->withCount(['teacherGroups as groups_count' => fn ($q) => $q->where('institution_id', $institution->id)])
            ->orderBy('name')
            ->get();

        $groups = Group::where('institution_id', $institution->id)
            ->with('teacher')
            ->withCount('tenants')
            ->orderBy('name')
            ->get();

        $tenants = Tenant::whereHas('group', fn ($q) => $q->where('institution_id', $institution->id))
            ->where('type', 'student')
            ->with('group')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'docentes' => $teachers->count(),
            'grupos' => $groups->count(),
            'estudiantes' => $tenants->count(),
            'activos' => $tenants->where('active', true)->count(),
        ];

        return view('livewire.coordinator.dashboard', compact(
            'institution', 'teachers', 'groups', 'tenants', 'stats'
        ));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function institution(): Institution
    {
        $institution = auth()->user()->coordinatedInstitution;
        abort_if(! $institution, 403, 'No tienes una institución asignada. Contacta al superadmin.');

        return $institution;
    }
}
