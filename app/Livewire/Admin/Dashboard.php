<?php

namespace App\Livewire\Admin;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\TransferStudentService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    // ── Tab activa ──────────────────────────────────────────────────────────
    public string $tab = 'resumen';

    // ── Institución ─────────────────────────────────────────────────────────
    public bool $showInstForm = false;

    public ?int $instEditingId = null;

    public string $instName = '';

    public string $instNit = '';

    public string $instCity = '';

    // ── Transferencia de estudiante ─────────────────────────────────────────
    public bool $showTransferModal = false;

    public string $transferTenantId = '';

    public int $transferGroupId = 0;

    public string $transferMode = 'keep';

    // ── Coordinador ─────────────────────────────────────────────────────────
    public bool $showCoordinatorForm = false;

    public ?int $coordinatorEditingId = null;

    public string $coordinatorName = '';

    public string $coordinatorEmail = '';

    public string $coordinatorPassword = '';

    public int $coordinatorInstitutionId = 0;

    // ── Docente ─────────────────────────────────────────────────────────────
    public bool $showTeacherForm = false;

    public ?int $teacherEditingId = null;

    public string $teacherName = '';

    public string $teacherEmail = '';

    public string $teacherPassword = '';

    public int $teacherInstitution = 0;

    // ── Transferencia CRUD ──────────────────────────────────────────────────

    public function openTransfer(string $tenantId): void
    {
        $this->transferTenantId = $tenantId;
        $this->transferGroupId = 0;
        $this->transferMode = 'keep';
        $this->showTransferModal = true;
    }

    public function confirmTransfer(TransferStudentService $service): void
    {
        $this->validate([
            'transferGroupId' => ['required', 'integer', 'min:1', 'exists:groups,id'],
            'transferMode' => ['required', 'in:keep,reset,fresh'],
        ], [
            'transferGroupId.min' => 'Selecciona un grupo destino.',
            'transferGroupId.exists' => 'El grupo seleccionado no existe.',
        ]);

        $tenant = Tenant::findOrFail($this->transferTenantId);
        $newGroup = Group::findOrFail($this->transferGroupId);

        if ($tenant->group_id === $this->transferGroupId) {
            $this->addError('transferGroupId', 'El estudiante ya pertenece a ese grupo.');

            return;
        }

        $service->transfer($this->transferTenantId, $this->transferGroupId, $this->transferMode);

        session()->flash('success', "Estudiante transferido al grupo «{$newGroup->name}» correctamente.");
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    // ── Institución CRUD ────────────────────────────────────────────────────

    public function openCreateInst(): void
    {
        $this->reset(['instEditingId', 'instName', 'instNit', 'instCity']);
        $this->showInstForm = true;
    }

    public function openEditInst(int $id): void
    {
        $inst = Institution::findOrFail($id);
        $this->instEditingId = $id;
        $this->instName = $inst->name;
        $this->instNit = $inst->nit ?? '';
        $this->instCity = $inst->city ?? '';
        $this->showInstForm = true;
    }

    public function saveInst(): void
    {
        $this->validate([
            'instName' => ['required', 'string', 'max:150'],
            'instNit' => ['nullable', 'string', 'max:20'],
            'instCity' => ['nullable', 'string', 'max:100'],
        ]);

        $data = ['name' => $this->instName, 'nit' => $this->instNit, 'city' => $this->instCity, 'active' => true];

        if ($this->instEditingId) {
            Institution::findOrFail($this->instEditingId)->update($data);
        } else {
            Institution::create($data);
        }

        session()->flash('success', 'Institución guardada.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    public function deleteInst(int $id): void
    {
        Institution::findOrFail($id)->delete();
        session()->flash('success', 'Institución eliminada.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    // ── Coordinador CRUD ────────────────────────────────────────────────────

    public function openCreateCoordinator(): void
    {
        $this->reset(['coordinatorEditingId', 'coordinatorName', 'coordinatorEmail', 'coordinatorPassword', 'coordinatorInstitutionId']);
        $this->showCoordinatorForm = true;
    }

    public function openEditCoordinator(int $id): void
    {
        $coordinator = User::findOrFail($id);
        $this->coordinatorEditingId = $id;
        $this->coordinatorName = $coordinator->name;
        $this->coordinatorEmail = $coordinator->email;
        $this->coordinatorPassword = '';
        $this->coordinatorInstitutionId = $coordinator->coordinatedInstitution?->id ?? 0;
        $this->showCoordinatorForm = true;
    }

    public function saveCoordinator(): void
    {
        $rules = [
            'coordinatorName' => ['required', 'string', 'max:150'],
            'coordinatorEmail' => ['required', 'email', 'max:150', 'unique:users,email'.($this->coordinatorEditingId ? ",{$this->coordinatorEditingId}" : '')],
            'coordinatorInstitutionId' => ['required', 'integer', 'min:1', 'exists:institutions,id'],
        ];

        if (! $this->coordinatorEditingId) {
            $rules['coordinatorPassword'] = ['required', 'string', 'min:6'];
        } elseif ($this->coordinatorPassword) {
            $rules['coordinatorPassword'] = ['string', 'min:6'];
        }

        $this->validate($rules, [
            'coordinatorInstitutionId.min' => 'Selecciona una institución.',
            'coordinatorInstitutionId.exists' => 'La institución seleccionada no existe.',
        ]);

        if ($this->coordinatorEditingId) {
            $coordinator = User::findOrFail($this->coordinatorEditingId);
            $coordinator->update(array_filter([
                'name' => $this->coordinatorName,
                'email' => $this->coordinatorEmail,
                'password' => $this->coordinatorPassword ? Hash::make($this->coordinatorPassword) : null,
            ]));

            // Liberar institución anterior y asignar la nueva
            Institution::where('coordinator_id', $coordinator->id)->update(['coordinator_id' => null]);
            Institution::findOrFail($this->coordinatorInstitutionId)->update(['coordinator_id' => $coordinator->id]);
        } else {
            $coordinator = User::create([
                'name' => $this->coordinatorName,
                'email' => $this->coordinatorEmail,
                'password' => Hash::make($this->coordinatorPassword),
                'role' => 'coordinator',
            ]);

            Institution::findOrFail($this->coordinatorInstitutionId)->update(['coordinator_id' => $coordinator->id]);
        }

        session()->flash('success', 'Coordinador guardado.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    public function deleteCoordinator(int $id): void
    {
        $coordinator = User::where('id', $id)->where('role', 'coordinator')->firstOrFail();

        // Liberar la institución que tenía asignada
        Institution::where('coordinator_id', $id)->update(['coordinator_id' => null]);

        $coordinator->delete();
        session()->flash('success', 'Coordinador eliminado.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    // ── Docente CRUD ────────────────────────────────────────────────────────

    public function openCreateTeacher(): void
    {
        $this->reset(['teacherEditingId', 'teacherName', 'teacherEmail', 'teacherPassword', 'teacherInstitution']);
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
        $rules = [
            'teacherName' => ['required', 'string', 'max:150'],
            'teacherEmail' => ['required', 'email', 'max:150'],
        ];

        if (! $this->teacherEditingId) {
            $rules['teacherPassword'] = ['required', 'string', 'min:6'];
            $rules['teacherInstitution'] = ['required', 'integer', 'min:1'];
        } elseif ($this->teacherPassword) {
            $rules['teacherPassword'] = ['string', 'min:6'];
        }

        $this->validate($rules, [
            'teacherInstitution.min' => 'Selecciona una institución.',
        ]);

        if ($this->teacherEditingId) {
            $teacher = User::findOrFail($this->teacherEditingId);
            $teacher->update(array_filter([
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
                'institution_id' => $this->teacherInstitution,
                'teacher_id' => $teacher->id,
                'name' => 'Grupo '.date('Y'),
                'period' => date('Y').'-1',
                'active' => true,
            ]);
        }

        session()->flash('success', 'Docente guardado.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    public function deleteTeacher(int $id): void
    {
        User::where('id', $id)->where('role', 'teacher')->delete();
        session()->flash('success', 'Docente eliminado.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    public function render(): mixed
    {
        $institutions = Institution::with('coordinator')->withCount(['groups', 'groups as students_count' => fn ($q) => $q->join('tenants', 'tenants.group_id', '=', 'groups.id')])->orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->with('teacherGroups.institution')->withCount(['teacherGroups as students_count' => fn ($q) => $q->join('tenants', 'tenants.group_id', '=', 'groups.id')])->orderBy('name')->get();
        $coordinators = User::where('role', 'coordinator')->with('coordinatedInstitution')->orderBy('name')->get();
        $tenants = Tenant::with('group.institution')->orderByDesc('created_at')->get();
        $groups = Group::with(['institution', 'teacher'])->withCount('tenants')->orderBy('name')->get();

        $stats = [
            'instituciones' => $institutions->count(),
            'docentes' => $teachers->count(),
            'coordinadores' => $coordinators->count(),
            'estudiantes' => $tenants->count(),
            'grupos' => $groups->count(),
            'activos' => $tenants->where('active', true)->count(),
        ];

        $recentTenants = $tenants->take(8);

        return view('livewire.admin.dashboard', compact(
            'institutions', 'teachers', 'coordinators', 'tenants', 'groups', 'stats', 'recentTenants'
        ))->title('Superadmin — ContaEdu');
    }
}
