<?php

namespace App\Livewire\Coordinator;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\TenantProvisionService;
use App\Services\TransferStudentService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.coordinator')]
#[Title('Panel Coordinador')]
class Dashboard extends Component
{
    use WithFileUploads;

    // ── Tab activa ──────────────────────────────────────────────────────────
    public string $tab = 'resumen';

    // ── Formulario docente ──────────────────────────────────────────────────
    public bool $showTeacherForm = false;

    public ?int $teacherEditingId = null;

    public string $teacherName = '';

    public string $teacherEmail = '';

    public string $teacherPassword = '';

    // ── Formulario grupo ────────────────────────────────────────────────────
    public bool $showGroupForm = false;

    public ?int $groupEditingId = null;

    public string $groupName = '';

    public string $groupPeriod = '';

    public int $groupTeacherId = 0;

    // ── Crear estudiante (individual + bulk) ────────────────────────────────
    public bool $showCreateForm = false;

    public string $createMode = 'single';

    public ?int $createForGroupId = null;

    public string $cedula = '';

    public string $studentName = '';

    public string $companyName = '';

    public string $nitEmpresa = '';

    public string $password = '';

    public $bulkFile = null;

    public array $bulkPreview = [];

    public array $bulkResults = [];

    public string $bulkError = '';

    public int $bulkPreviewPage = 1;

    public int $bulkPreviewPerPage = 10;

    public int $bulkResultsPage = 1;

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

    // ── Grupos CRUD ──────────────────────────────────────────────────────────

    public function openGroupForm(?int $id = null): void
    {
        $this->reset(['groupEditingId', 'groupName', 'groupPeriod', 'groupTeacherId']);

        if ($id) {
            $institution = $this->institution();
            $group = Group::where('id', $id)->where('institution_id', $institution->id)->firstOrFail();
            $this->groupEditingId = $id;
            $this->groupName = $group->name;
            $this->groupPeriod = $group->period;
            $this->groupTeacherId = $group->teacher_id ?? 0;
        } else {
            $this->groupPeriod = date('Y').'-'.ceil(now()->month / 6);
        }

        $this->showGroupForm = true;
    }

    public function saveGroup(): void
    {
        $institution = $this->institution();

        $this->validate([
            'groupName'      => ['required', 'string', 'max:100'],
            'groupPeriod'    => ['required', 'string', 'max:20'],
            'groupTeacherId' => ['required', 'integer', 'min:1', 'exists:users,id'],
        ], [
            'groupTeacherId.min' => 'Selecciona un docente.',
        ]);

        if ($this->groupEditingId) {
            Group::where('id', $this->groupEditingId)
                ->where('institution_id', $institution->id)
                ->update([
                    'name'       => $this->groupName,
                    'period'     => $this->groupPeriod,
                    'teacher_id' => $this->groupTeacherId,
                ]);
            $this->dispatch('notify', type: 'success', message: 'Grupo actualizado.');
        } else {
            Group::create([
                'institution_id' => $institution->id,
                'teacher_id'     => $this->groupTeacherId,
                'name'           => $this->groupName,
                'period'         => $this->groupPeriod,
                'active'         => true,
            ]);
            $this->dispatch('notify', type: 'success', message: 'Grupo creado.');
        }

        $this->showGroupForm = false;
        $this->reset(['groupEditingId', 'groupName', 'groupPeriod', 'groupTeacherId']);
    }

    public function deleteGroup(int $id): void
    {
        $institution = $this->institution();
        $group = Group::where('id', $id)->where('institution_id', $institution->id)->firstOrFail();

        if ($group->tenants()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'No se puede eliminar un grupo con estudiantes asignados.');
            return;
        }

        $group->delete();
        $this->dispatch('notify', type: 'success', message: 'Grupo eliminado.');
    }

    // ── Estudiantes: crear ────────────────────────────────────────────────────

    public function openCreate(string $mode = 'single', int $groupId = 0): void
    {
        $this->reset([
            'cedula', 'studentName', 'companyName', 'nitEmpresa', 'password',
            'bulkFile', 'bulkPreview', 'bulkResults', 'bulkError', 'bulkPreviewPage', 'bulkResultsPage',
        ]);
        $this->createMode = $mode;
        $this->createForGroupId = $groupId ?: null;
        $this->showCreateForm = true;
    }

    public function switchMode(string $mode): void
    {
        $this->createMode = $mode;
        $this->reset(['bulkFile', 'bulkPreview', 'bulkResults', 'bulkError', 'bulkPreviewPage']);
    }

    public function createCompany(TenantProvisionService $service): void
    {
        $institution  = $this->institution();
        $multipleGroups = Group::where('institution_id', $institution->id)->count() > 1;

        $rules = [
            'cedula'      => ['required', 'string', 'max:20', 'unique:tenants,id'],
            'studentName' => ['required', 'string', 'max:120'],
            'companyName' => ['required', 'string', 'max:120'],
            'nitEmpresa'  => ['required', 'string', 'max:20'],
            'password'    => ['required', 'string', 'min:6'],
        ];

        if ($multipleGroups) {
            $rules['createForGroupId'] = ['required', 'integer', 'min:1', 'exists:groups,id'];
        }

        $this->validate($rules, [
            'cedula.unique'            => 'Ya existe un estudiante con esa cédula.',
            'createForGroupId.min'     => 'Selecciona un grupo.',
            'createForGroupId.required'=> 'Selecciona un grupo.',
        ]);

        $group = $this->resolveGroup();

        $service->provision([
            'cedula'       => $this->cedula,
            'student_name' => $this->studentName,
            'company_name' => $this->companyName,
            'nit_empresa'  => $this->nitEmpresa,
            'group_id'     => $group->id,
            'password'     => $this->password,
        ]);

        $companyName = $this->companyName;
        $this->showCreateForm = false;
        $this->reset(['cedula', 'studentName', 'companyName', 'nitEmpresa', 'password', 'createForGroupId']);
        $this->dispatch('notify', type: 'success', message: "Empresa \"{$companyName}\" creada exitosamente.");
    }

    public function processBulkFile(): void
    {
        $this->bulkError = '';
        $this->bulkPreview = [];
        $this->bulkResults = [];

        $this->validate([
            'bulkFile' => ['required', 'file', 'mimes:csv,txt', 'max:512'],
        ], [
            'bulkFile.required' => 'Selecciona un archivo CSV.',
            'bulkFile.mimes'   => 'El archivo debe ser CSV (.csv).',
            'bulkFile.max'     => 'El archivo no puede superar 512 KB.',
        ]);

        $path   = $this->bulkFile->getRealPath();
        $handle = fopen($path, 'r');
        $preview = [];
        $row = 0;

        $firstLine = fgets($handle);
        rewind($handle);
        $separator = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        while (($cols = fgetcsv($handle, 1000, $separator)) !== false) {
            $row++;
            if ($row === 1) { continue; }
            if ($row === 2 && isset($cols[0])) { $cols[0] = ltrim($cols[0], "\xEF\xBB\xBF"); }
            if (empty(array_filter($cols))) { continue; }

            if (count($cols) < 5) {
                $this->bulkError = "Fila {$row}: el archivo no tiene las 5 columnas requeridas.";
                fclose($handle);
                return;
            }

            [$cedula, $nombre, $empresa, $nit, $pass] = array_map('trim', $cols);

            $errors = [];
            if (empty($cedula))              { $errors[] = 'cédula requerida'; }
            if (empty($nombre))              { $errors[] = 'nombre requerido'; }
            if (empty($empresa))             { $errors[] = 'empresa requerida'; }
            if (empty($nit))                 { $errors[] = 'NIT requerido'; }
            if (empty($pass) || strlen($pass) < 6) { $errors[] = 'contraseña mín. 6 caracteres'; }

            $preview[] = [
                'cedula'       => $cedula,
                'student_name' => $nombre,
                'company_name' => $empresa,
                'nit_empresa'  => $nit,
                'password'     => $pass,
                'error'        => implode(', ', $errors),
            ];
        }

        fclose($handle);

        if (empty($preview)) {
            $this->bulkError = 'El archivo no contiene filas de datos.';
            return;
        }

        $this->bulkPreview = $preview;
        $this->bulkPreviewPage = 1;
    }

    public function confirmBulkCreate(TenantProvisionService $service): void
    {
        if (empty($this->bulkPreview)) { return; }

        $institution = $this->institution();
        $multipleGroups = Group::where('institution_id', $institution->id)->count() > 1;

        if ($multipleGroups && ! $this->createForGroupId) {
            $this->addError('createForGroupId', 'Selecciona un grupo antes de importar.');
            return;
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $group   = $this->resolveGroup();
        $results = [];

        foreach ($this->bulkPreview as $row) {
            if (! empty($row['error'])) { continue; }

            if (Tenant::where('id', $row['cedula'])->exists()) {
                $results[] = array_merge($row, ['status' => 'skipped', 'message' => 'Ya existe, omitido.']);
                continue;
            }

            try {
                if (tenancy()->tenant !== null) { tenancy()->end(); }

                $service->provision([
                    'cedula'       => $row['cedula'],
                    'student_name' => $row['student_name'],
                    'company_name' => $row['company_name'],
                    'nit_empresa'  => $row['nit_empresa'],
                    'group_id'     => $group->id,
                    'password'     => $row['password'],
                ]);

                $results[] = array_merge($row, ['status' => 'ok', 'message' => '']);
            } catch (\Exception $e) {
                $results[] = array_merge($row, ['status' => 'error', 'message' => $e->getMessage()]);
            } finally {
                if (tenancy()->tenant !== null) { tenancy()->end(); }
            }
        }

        $this->bulkPreview  = [];
        $this->bulkResults  = $results;
        $this->bulkFile     = null;
        $this->bulkResultsPage = 1;

        $created = count(array_filter($results, fn ($r) => $r['status'] === 'ok'));
        $skipped = count(array_filter($results, fn ($r) => $r['status'] === 'skipped'));
        $failed  = count(array_filter($results, fn ($r) => $r['status'] === 'error'));

        $parts = [];
        if ($created > 0) { $parts[] = "{$created} creada(s)"; }
        if ($skipped > 0) { $parts[] = "{$skipped} ya existían"; }
        if ($failed  > 0) { $parts[] = "{$failed} con error"; }

        $type    = $failed > 0 ? 'warning' : ($created > 0 ? 'success' : 'info');
        $message = implode(', ', $parts).'.';
        $this->dispatch('notify', type: $type, message: $message);
    }

    public function bulkPreviewNextPage(): void
    {
        $total = (int) ceil(count($this->bulkPreview) / $this->bulkPreviewPerPage);
        if ($this->bulkPreviewPage < $total) { $this->bulkPreviewPage++; }
    }

    public function bulkPreviewPrevPage(): void
    {
        if ($this->bulkPreviewPage > 1) { $this->bulkPreviewPage--; }
    }

    public function bulkResultsNextPage(): void
    {
        $total = (int) ceil(count($this->bulkResults) / $this->bulkPreviewPerPage);
        if ($this->bulkResultsPage < $total) { $this->bulkResultsPage++; }
    }

    public function bulkResultsPrevPage(): void
    {
        if ($this->bulkResultsPage > 1) { $this->bulkResultsPage--; }
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

    private function resolveGroup(): Group
    {
        $institution = $this->institution();

        if ($this->createForGroupId) {
            return Group::where('id', $this->createForGroupId)
                ->where('institution_id', $institution->id)
                ->firstOrFail();
        }

        $group = Group::where('institution_id', $institution->id)->first();
        abort_if(! $group, 403, 'No hay grupos en esta institución.');

        return $group;
    }
}
