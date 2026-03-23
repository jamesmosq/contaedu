<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\StudentScore;
use App\Models\Central\Tenant;
use App\Models\Tenant\Invoice;
use App\Services\TenantProvisionService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.teacher')]
class Dashboard extends Component
{
    use WithFileUploads;

    // ─── Formulario: crear empresa ────────────────────────────────────────────

    public bool $showCreateForm = false;

    public string $createMode = 'single'; // 'single' | 'bulk'

    public ?int $createForGroupId = null;

    public string $cedula = '';

    public string $studentName = '';

    public string $companyName = '';

    public string $nitEmpresa = '';

    public string $password = '';

    // ─── Carga masiva ─────────────────────────────────────────────────────────

    /** @var mixed Livewire TemporaryUploadedFile */
    public $bulkFile = null;

    /** @var array<int,array<string,string>> Filas parseadas del CSV para vista previa */
    public array $bulkPreview = [];

    /** @var array<int,array<string,string|bool>> Resultados luego de crear */
    public array $bulkResults = [];

    public string $bulkError = '';

    public int $bulkPreviewPage = 1;

    public int $bulkPreviewPerPage = 10;

    public int $bulkResultsPage = 1;

    // ─── Navegación: grupo seleccionado ──────────────────────────────────────

    public ?int $selectedGroupId = null;

    public function selectGroup(int $id): void
    {
        $this->selectedGroupId = $id;
        $this->showCreateForm = false;
    }

    public function backToGroups(): void
    {
        $this->selectedGroupId = null;
        $this->showCreateForm = false;
    }

    // ─── Formulario: crear/editar grupo ──────────────────────────────────────

    public bool $showGroupForm = false;

    public ?int $editingGroupId = null;

    public string $groupName = '';

    public string $groupPeriod = '';

    // ─── Crear empresa ────────────────────────────────────────────────────────

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

    public function bulkPreviewNextPage(): void
    {
        $totalPages = (int) ceil(count($this->bulkPreview) / $this->bulkPreviewPerPage);
        if ($this->bulkPreviewPage < $totalPages) {
            $this->bulkPreviewPage++;
        }
    }

    public function bulkPreviewPrevPage(): void
    {
        if ($this->bulkPreviewPage > 1) {
            $this->bulkPreviewPage--;
        }
    }

    public function bulkResultsNextPage(): void
    {
        $totalPages = (int) ceil(count($this->bulkResults) / $this->bulkPreviewPerPage);
        if ($this->bulkResultsPage < $totalPages) {
            $this->bulkResultsPage++;
        }
    }

    public function bulkResultsPrevPage(): void
    {
        if ($this->bulkResultsPage > 1) {
            $this->bulkResultsPage--;
        }
    }

    public function createCompany(TenantProvisionService $service): void
    {
        $this->validate([
            'cedula' => ['required', 'string', 'max:20', 'unique:tenants,id'],
            'studentName' => ['required', 'string', 'max:120'],
            'companyName' => ['required', 'string', 'max:120'],
            'nitEmpresa' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'cedula.unique' => 'Ya existe un estudiante con esa cédula.',
        ]);

        $group = $this->resolveGroup();

        $service->provision([
            'cedula' => $this->cedula,
            'student_name' => $this->studentName,
            'company_name' => $this->companyName,
            'nit_empresa' => $this->nitEmpresa,
            'group_id' => $group->id,
            'password' => $this->password,
        ]);

        $companyName = $this->companyName;

        $this->showCreateForm = false;
        $this->reset(['cedula', 'studentName', 'companyName', 'nitEmpresa', 'password', 'createForGroupId']);
        $this->dispatch('notify', type: 'success', message: "Empresa \"{$companyName}\" creada exitosamente.");
    }

    // ─── Carga masiva: parsear CSV ────────────────────────────────────────────

    public function processBulkFile(): void
    {
        $this->bulkError = '';
        $this->bulkPreview = [];
        $this->bulkResults = [];

        $this->validate([
            'bulkFile' => ['required', 'file', 'mimes:csv,txt', 'max:512'],
        ], [
            'bulkFile.required' => 'Selecciona un archivo CSV.',
            'bulkFile.mimes' => 'El archivo debe ser CSV (.csv).',
            'bulkFile.max' => 'El archivo no puede superar 512 KB.',
        ]);

        $path = $this->bulkFile->getRealPath();
        $handle = fopen($path, 'r');
        $preview = [];
        $row = 0;

        $firstLine = fgets($handle);
        rewind($handle);
        $separator = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        while (($cols = fgetcsv($handle, 1000, $separator)) !== false) {
            $row++;

            if ($row === 1) {
                continue;
            }

            if ($row === 2 && isset($cols[0])) {
                $cols[0] = ltrim($cols[0], "\xEF\xBB\xBF");
            }

            if (empty(array_filter($cols))) {
                continue;
            }

            if (count($cols) < 5) {
                $this->bulkError = "Fila {$row}: el archivo no tiene las 5 columnas requeridas (cedula, nombre_estudiante, nombre_empresa, nit_empresa, password).";
                fclose($handle);

                return;
            }

            [$cedula, $nombre, $empresa, $nit, $pass] = array_map('trim', $cols);

            $errors = [];
            if (empty($cedula)) {
                $errors[] = 'cédula requerida';
            }
            if (empty($nombre)) {
                $errors[] = 'nombre requerido';
            }
            if (empty($empresa)) {
                $errors[] = 'empresa requerida';
            }
            if (empty($nit)) {
                $errors[] = 'NIT requerido';
            }
            if (empty($pass) || strlen($pass) < 6) {
                $errors[] = 'contraseña mín. 6 caracteres';
            }

            $preview[] = [
                'cedula' => $cedula,
                'student_name' => $nombre,
                'company_name' => $empresa,
                'nit_empresa' => $nit,
                'password' => $pass,
                'error' => implode(', ', $errors),
            ];
        }

        fclose($handle);

        if (empty($preview)) {
            $this->bulkError = 'El archivo no contiene filas de datos (solo cabecera o está vacío).';

            return;
        }

        $this->bulkPreview = $preview;
        $this->bulkPreviewPage = 1;
    }

    // ─── Carga masiva: confirmar creación ─────────────────────────────────────

    public function confirmBulkCreate(TenantProvisionService $service): void
    {
        if (empty($this->bulkPreview)) {
            return;
        }

        // Cada tenant requiere crear schema + migrar + sembrar PUC; eliminamos límites.
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $group = $this->resolveGroup();
        $results = [];

        foreach ($this->bulkPreview as $row) {
            if (! empty($row['error'])) {
                continue;
            }

            // Si la cédula ya existe, omitir sin error.
            if (Tenant::where('id', $row['cedula'])->exists()) {
                $results[] = array_merge($row, ['status' => 'skipped', 'message' => 'Ya existe, omitido.']);

                continue;
            }

            try {
                // Garantizar contexto central antes de cada provisión.
                // El pipeline (CreateDatabase → MigrateDatabase → SeedDatabase) inicializa
                // el contexto de tenancy y no lo revierte; hay que hacerlo manualmente.
                if (tenancy()->tenant !== null) {
                    tenancy()->end();
                }

                $service->provision([
                    'cedula' => $row['cedula'],
                    'student_name' => $row['student_name'],
                    'company_name' => $row['company_name'],
                    'nit_empresa' => $row['nit_empresa'],
                    'group_id' => $group->id,
                    'password' => $row['password'],
                ]);

                $results[] = array_merge($row, ['status' => 'ok', 'message' => '']);
            } catch (\Exception $e) {
                $results[] = array_merge($row, ['status' => 'error', 'message' => $e->getMessage()]);
            } finally {
                // Siempre revertir al contexto central, aunque haya excepción.
                if (tenancy()->tenant !== null) {
                    tenancy()->end();
                }
            }
        }

        $this->bulkPreview = [];
        $this->bulkResults = $results;
        $this->bulkFile = null;
        $this->bulkResultsPage = 1;

        $created = count(array_filter($results, fn ($r) => $r['status'] === 'ok'));
        $skipped = count(array_filter($results, fn ($r) => $r['status'] === 'skipped'));
        $failed = count(array_filter($results, fn ($r) => $r['status'] === 'error'));

        $parts = [];
        if ($created > 0) {
            $parts[] = "{$created} creada(s)";
        }
        if ($skipped > 0) {
            $parts[] = "{$skipped} ya existían";
        }
        if ($failed > 0) {
            $parts[] = "{$failed} con error";
        }

        $type = $failed > 0 ? 'warning' : ($created > 0 ? 'success' : 'info');
        $message = implode(', ', $parts).'.';

        $this->dispatch('notify', type: $type, message: $message);
    }

    // ─── CRUD de grupos ───────────────────────────────────────────────────────

    public function openGroupForm(?int $id = null): void
    {
        $this->editingGroupId = $id;

        if ($id) {
            $group = Group::where('id', $id)->where('teacher_id', auth()->id())->firstOrFail();
            $this->groupName = $group->name;
            $this->groupPeriod = $group->period;
        } else {
            $this->groupName = '';
            $this->groupPeriod = date('Y').'-'.ceil(now()->month / 6);
        }

        $this->showGroupForm = true;
    }

    public function saveGroup(): void
    {
        $this->validate([
            'groupName' => ['required', 'string', 'max:100'],
            'groupPeriod' => ['required', 'string', 'max:20'],
        ]);

        $teacher = auth()->user();

        if ($this->editingGroupId) {
            Group::where('id', $this->editingGroupId)
                ->where('teacher_id', $teacher->id)
                ->update(['name' => $this->groupName, 'period' => $this->groupPeriod]);

            $this->dispatch('notify', type: 'success', message: 'Grupo actualizado.');
        } else {
            $institutionId = $teacher->teacherGroups()->value('institution_id')
                ?? Institution::first()?->id;

            abort_if(! $institutionId, 422, 'No hay institución disponible.');

            Group::create([
                'institution_id' => $institutionId,
                'teacher_id' => $teacher->id,
                'name' => $this->groupName,
                'period' => $this->groupPeriod,
                'active' => true,
            ]);

            $this->dispatch('notify', type: 'success', message: 'Grupo creado correctamente.');
        }

        $this->showGroupForm = false;
        $this->reset(['editingGroupId', 'groupName', 'groupPeriod']);
    }

    public function deleteGroup(int $id): void
    {
        $group = Group::where('id', $id)->where('teacher_id', auth()->id())->firstOrFail();

        if ($group->tenants()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'No se puede eliminar un grupo con empresas asignadas.');

            return;
        }

        $group->delete();
        $this->dispatch('notify', type: 'success', message: 'Grupo eliminado.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolveGroup(): Group
    {
        if ($this->createForGroupId) {
            return Group::where('id', $this->createForGroupId)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();
        }

        $group = auth()->user()->teacherGroups()->first();
        abort_if(! $group, 403, 'No tienes un grupo asignado.');

        return $group;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups = $teacher->teacherGroups()->with(['institution', 'tenants'])->orderBy('created_at')->get();

        $allTenants = $groups->flatMap(fn ($g) => $g->tenants);
        $totalStudents = $allTenants->count();
        $activeStudents = $allTenants->where('active', true)->count();
        $institution = $groups->first()?->institution;

        $stats = [
            'grupos' => $groups->count(),
            'estudiantes' => $totalStudents,
            'activos' => $activeStudents,
            'institucion' => $institution?->name ?? '—',
        ];

        $selectedGroup = null;
        $students = [];

        if ($this->selectedGroupId) {
            $selectedGroup = $groups->firstWhere('id', $this->selectedGroupId);

            if ($selectedGroup) {
                $tenantIds = $selectedGroup->tenants->pluck('id');
                $scoresByTenant = $tenantIds->isNotEmpty()
                    ? StudentScore::whereIn('tenant_id', $tenantIds)->get()->groupBy('tenant_id')
                    : collect();

                foreach ($selectedGroup->tenants as $tenant) {
                    $metrics = $tenant->run(fn () => [
                        'invoices_count' => Invoice::where('type', 'venta')->where('status', 'emitida')->count(),
                        'invoices_total' => (float) Invoice::where('type', 'venta')->where('status', 'emitida')->sum('total'),
                    ]);

                    $tenantScores = $scoresByTenant->get($tenant->id, collect());

                    $students[] = [
                        'tenant' => $tenant,
                        'metrics' => $metrics,
                        'promedio' => $tenantScores->isNotEmpty() ? round($tenantScores->avg('score'), 1) : null,
                        'graded' => $tenantScores->count(),
                    ];
                }
            }
        }

        return view('livewire.teacher.dashboard', compact('groups', 'selectedGroup', 'students', 'stats'))
            ->title('Panel Docente');
    }
}
