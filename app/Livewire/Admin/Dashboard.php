<?php

namespace App\Livewire\Admin;

use App\Models\Central\Exercise;
use App\Models\Central\ExerciseCompletion;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\TransferStudentService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    public string $instContractExpiresAt = '';

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

    // ── Filtro métricas ─────────────────────────────────────────────────────
    public int $metricsInstitutionId = 0;

    // ── Banco global de ejercicios ──────────────────────────────────────────
    public bool $showGlobalExerciseForm = false;

    public ?int $globalExerciseEditingId = null;

    public string $gTitle = '';

    public string $gInstructions = '';

    public string $gType = 'factura_venta';

    public string $gMontoMinimo = '';

    public string $gCuentaPuc = '';

    public int $gPuntos = 10;

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
        $this->reset(['instEditingId', 'instName', 'instNit', 'instCity', 'instContractExpiresAt']);
        $this->showInstForm = true;
    }

    public function openEditInst(int $id): void
    {
        $inst = Institution::findOrFail($id);
        $this->instEditingId = $id;
        $this->instName = $inst->name;
        $this->instNit = $inst->nit ?? '';
        $this->instCity = $inst->city ?? '';
        $this->instContractExpiresAt = $inst->contract_expires_at?->format('Y-m-d') ?? '';
        $this->showInstForm = true;
    }

    public function saveInst(): void
    {
        $this->validate([
            'instName' => ['required', 'string', 'max:150'],
            'instNit' => ['nullable', 'string', 'max:20'],
            'instCity' => ['nullable', 'string', 'max:100'],
            'instContractExpiresAt' => ['nullable', 'date'],
        ]);

        $data = [
            'name' => $this->instName,
            'nit' => $this->instNit,
            'city' => $this->instCity,
            'active' => true,
            'contract_expires_at' => $this->instContractExpiresAt ?: null,
        ];

        if ($this->instEditingId) {
            Institution::findOrFail($this->instEditingId)->update($data);
        } else {
            Institution::create($data);
        }

        session()->flash('success', 'Institución guardada.');
        $this->redirect(route('admin.dashboard'), navigate: false);
    }

    public function toggleInstitution(int $id): void
    {
        $inst = Institution::findOrFail($id);
        $inst->update(['active' => ! $inst->active]);
        $msg = $inst->active ? "Institución «{$inst->name}» habilitada." : "Institución «{$inst->name}» deshabilitada. Los estudiantes no podrán iniciar sesión.";
        $this->dispatch('notify', type: $inst->active ? 'success' : 'warning', message: $msg);
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

    // ── Banco global CRUD ───────────────────────────────────────────────────

    public function openGlobalExerciseForm(?int $id = null): void
    {
        $this->globalExerciseEditingId = $id;

        if ($id) {
            $ex = Exercise::global()->findOrFail($id);
            $this->gTitle = $ex->title;
            $this->gInstructions = $ex->instructions ?? '';
            $this->gType = $ex->type;
            $this->gMontoMinimo = $ex->monto_minimo ? (string) $ex->monto_minimo : '';
            $this->gCuentaPuc = $ex->cuenta_puc_requerida ?? '';
            $this->gPuntos = $ex->puntos;
        } else {
            $this->reset(['gTitle', 'gInstructions', 'gMontoMinimo', 'gCuentaPuc']);
            $this->gType = 'factura_venta';
            $this->gPuntos = 10;
        }

        $this->showGlobalExerciseForm = true;
    }

    public function saveGlobalExercise(): void
    {
        $this->validate([
            'gTitle' => ['required', 'string', 'max:200'],
            'gType' => ['required', 'in:factura_venta,factura_compra,asiento_manual,registro_tercero,registro_producto,pago_proveedor'],
            'gMontoMinimo' => ['nullable', 'numeric', 'min:0'],
            'gPuntos' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $data = [
            'teacher_id' => auth()->id(),
            'title' => $this->gTitle,
            'instructions' => $this->gInstructions ?: null,
            'type' => $this->gType,
            'monto_minimo' => $this->gMontoMinimo !== '' ? (float) $this->gMontoMinimo : null,
            'cuenta_puc_requerida' => $this->gCuentaPuc ?: null,
            'puntos' => $this->gPuntos,
            'is_global' => true,
            'active' => true,
        ];

        if ($this->globalExerciseEditingId) {
            Exercise::global()->findOrFail($this->globalExerciseEditingId)->update($data);
            $msg = 'Ejercicio global actualizado.';
        } else {
            Exercise::create($data);
            $msg = 'Ejercicio global creado.';
        }

        $this->showGlobalExerciseForm = false;
        $this->globalExerciseEditingId = null;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function deleteGlobalExercise(int $id): void
    {
        Exercise::global()->findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Ejercicio global eliminado.');
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

        $metrics = $this->tab === 'metricas' ? $this->buildMetrics($tenants) : null;
        $saludResumen = $this->tab === 'resumen' ? $this->calcularSaludPorInstitucion($tenants) : collect();
        $globalExercises = $this->tab === 'banco' ? Exercise::global()->withCount('assignments')->orderByDesc('created_at')->get() : collect();

        return view('livewire.admin.dashboard', compact(
            'institutions', 'teachers', 'coordinators', 'tenants', 'groups', 'stats', 'recentTenants', 'metrics', 'saludResumen', 'globalExercises'
        ))->title('Superadmin — ContaEdu');
    }

    private function buildMetrics(Collection $tenants): array
    {
        $instId = $this->metricsInstitutionId ?: null;

        $studentIds = Tenant::where('type', 'student')
            ->when($instId, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('institution_id', $instId)))
            ->pluck('id');

        $cacheKey = 'admin_metrics_detail_'.($instId ?? 'all');
        $operacional = Cache::remember($cacheKey, 300, function () use ($studentIds) {
            if ($studentIds->isEmpty()) {
                return ['facturas_30d' => 0, 'compras_30d' => 0, 'asientos_30d' => 0,
                    'facturas_7d' => 0, 'compras_7d' => 0, 'asientos_7d' => 0];
            }

            $makeUnion = fn (string $table, string $interval) => $studentIds
                ->map(fn ($id) => "SELECT COUNT(*) as n FROM \"tenant_{$id}\".{$table} WHERE created_at >= NOW() - INTERVAL '{$interval}'")
                ->join(' UNION ALL ');

            return [
                'facturas_30d' => (int) DB::selectOne("SELECT SUM(n) as t FROM ({$makeUnion('invoices', '30 days')}) x")?->t,
                'compras_30d' => (int) DB::selectOne("SELECT SUM(n) as t FROM ({$makeUnion('purchase_invoices', '30 days')}) x")?->t,
                'asientos_30d' => (int) DB::selectOne("SELECT SUM(n) as t FROM ({$makeUnion('journal_entries', '30 days')}) x")?->t,
                'facturas_7d' => (int) DB::selectOne("SELECT SUM(n) as t FROM ({$makeUnion('invoices', '7 days')}) x")?->t,
                'compras_7d' => (int) DB::selectOne("SELECT SUM(n) as t FROM ({$makeUnion('purchase_invoices', '7 days')}) x")?->t,
                'asientos_7d' => (int) DB::selectOne("SELECT SUM(n) as t FROM ({$makeUnion('journal_entries', '7 days')}) x")?->t,
            ];
        });

        // Registros de estudiantes por semana (últimas 8 semanas)
        $registrosSemana = Tenant::where('type', 'student')
            ->where('created_at', '>=', now()->subWeeks(8))
            ->when($instId, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('institution_id', $instId)))
            ->selectRaw("DATE_TRUNC('week', created_at) as semana, COUNT(*) as total")
            ->groupBy('semana')
            ->orderBy('semana')
            ->get()
            ->map(fn ($r) => ['semana' => Carbon::parse($r->semana)->format('d/m'), 'total' => (int) $r->total]);

        // Completaciones de ejercicios
        $completaciones = ExerciseCompletion::when(! $studentIds->isEmpty(), fn ($q) => $q->whereIn('tenant_id', $studentIds))
            ->selectRaw('result, COUNT(*) as total')
            ->groupBy('result')
            ->pluck('total', 'result');

        // Top grupos por tasa de completación
        $topGrupos = Group::with(['institution', 'teacher', 'tenants'])
            ->withCount('tenants')
            ->whereHas('tenants')
            ->when($instId, fn ($q) => $q->where('institution_id', $instId))
            ->get()
            ->map(function ($g) {
                $tenantIds = $g->tenants->pluck('id');
                $completadas = ExerciseCompletion::whereIn('tenant_id', $tenantIds)
                    ->where('result', 'aprobado')
                    ->count();

                return [
                    'nombre' => $g->name,
                    'institucion' => $g->institution?->name ?? '—',
                    'docente' => $g->teacher?->name ?? '—',
                    'estudiantes' => $g->tenants_count,
                    'aprobados' => $completadas,
                ];
            })
            ->sortByDesc('aprobados')
            ->take(8)
            ->values();

        // Tipos de ejercicio más usados (filtrados por docentes de la institución)
        $tiposEjercicio = Exercise::when($instId, function ($q) use ($instId) {
            $q->whereHas('teacher.teacherGroups', fn ($g) => $g->where('institution_id', $instId));
        })
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        // Nuevos estudiantes este mes vs mes anterior
        $baseQuery = fn () => Tenant::where('type', 'student')
            ->when($instId, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('institution_id', $instId)));

        $estudiantesEsteMes = $baseQuery()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $estudiantesMesAnterior = $baseQuery()->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();

        $institucionNombre = $instId ? Institution::find($instId)?->name : null;

        $saludUso = $this->calcularSaludUso($operacional, $completaciones);

        // Salud por institución (para la tabla resumen — solo cuando no hay filtro)
        $saludPorInstitucion = ! $instId ? $this->calcularSaludPorInstitucion($tenants) : collect();

        return compact(
            'operacional',
            'registrosSemana',
            'completaciones',
            'topGrupos',
            'tiposEjercicio',
            'estudiantesEsteMes',
            'estudiantesMesAnterior',
            'institucionNombre',
            'saludUso',
            'saludPorInstitucion',
        );
    }

    private function calcularSaludUso(array $operacional, Collection $completaciones): array
    {
        $ops30d = $operacional['facturas_30d'] + $operacional['compras_30d'] + $operacional['asientos_30d'];
        $total = $completaciones->sum();
        $aprobados = (int) ($completaciones['aprobado'] ?? 0);
        $tasaComp = $total > 0 ? ($aprobados / $total) * 100 : 0;

        if ($ops30d >= 50 && $tasaComp >= 50) {
            return ['nivel' => 'alta',  'label' => 'Uso alto',   'color' => 'green',  'ops' => $ops30d, 'tasa' => round($tasaComp)];
        }

        if ($ops30d >= 10 || $tasaComp >= 20) {
            return ['nivel' => 'media', 'label' => 'Uso medio',  'color' => 'amber',  'ops' => $ops30d, 'tasa' => round($tasaComp)];
        }

        return ['nivel' => 'baja',  'label' => 'Uso bajo',   'color' => 'slate',  'ops' => $ops30d, 'tasa' => round($tasaComp)];
    }

    private function calcularSaludPorInstitucion(Collection $tenants): Collection
    {
        $institutions = Institution::all()->keyBy('id');

        return $institutions->map(function (Institution $inst) use ($tenants) {
            $ids = $tenants->where('type', 'student')
                ->filter(fn ($t) => $t->group?->institution_id === $inst->id)
                ->pluck('id');

            if ($ids->isEmpty()) {
                return ['nivel' => 'baja', 'color' => 'slate', 'label' => 'Sin estudiantes'];
            }

            $cacheKey = 'admin_metrics_ops_'.$inst->id;
            $ops = Cache::remember($cacheKey, 300, function () use ($ids) {
                $makeUnion = fn (string $table) => $ids
                    ->map(fn ($id) => "SELECT COUNT(*) as n FROM \"tenant_{$id}\".{$table} WHERE created_at >= NOW() - INTERVAL '30 days'")
                    ->join(' UNION ALL ');

                return (int) DB::selectOne('SELECT SUM(n) as t FROM ('.$makeUnion('invoices').
                    ' UNION ALL '.$makeUnion('purchase_invoices').
                    ' UNION ALL '.$makeUnion('journal_entries').') x')?->t;
            });

            $total = ExerciseCompletion::whereIn('tenant_id', $ids)->count();
            $aprobados = ExerciseCompletion::whereIn('tenant_id', $ids)->where('result', 'aprobado')->count();
            $tasa = $total > 0 ? ($aprobados / $total) * 100 : 0;

            if ($ops >= 50 && $tasa >= 50) {
                return ['nivel' => 'alta',  'color' => 'green', 'label' => 'Uso alto'];
            }

            if ($ops >= 10 || $tasa >= 20) {
                return ['nivel' => 'media', 'color' => 'amber', 'label' => 'Uso medio'];
            }

            return ['nivel' => 'baja', 'color' => 'slate', 'label' => 'Uso bajo'];
        });
    }
}
