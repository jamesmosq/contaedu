<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Group;
use App\Models\Central\ReferenceAccessLog;
use App\Models\Central\Tenant;
use App\Services\TenantProvisionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.teacher')]
#[Title('Mis demos')]
class DemoCompanies extends Component
{
    public const SECTORS = [
        'comercio' => 'Comercio al por mayor y menor',
        'servicios' => 'Servicios profesionales',
        'manufactura' => 'Manufactura / Industria',
        'construccion' => 'Construcción',
        'agropecuario' => 'Agropecuario',
        'otro' => 'Otro',
    ];

    // ── Formulario: nueva empresa ───────────────────────────────────────────────
    public bool $showForm = false;

    public string $companyName = '';

    public string $nitEmpresa = '';

    public string $sector = 'comercio';

    public function openCreate(): void
    {
        $this->reset(['companyName', 'nitEmpresa']);
        $this->sector = 'comercio';
        $this->showForm = true;
    }

    public function create(TenantProvisionService $service): void
    {
        $this->validate([
            'companyName' => ['required', 'string', 'max:120'],
            'nitEmpresa' => ['required', 'string', 'max:20'],
            'sector' => ['required', 'string', 'in:'.implode(',', array_keys(self::SECTORS))],
        ]);

        $teacher = auth()->user();

        $service->provisionDemo([
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'company_name' => $this->companyName,
            'nit_empresa' => $this->nitEmpresa,
            'sector' => $this->sector,
        ]);

        $this->showForm = false;
        $this->reset(['companyName', 'nitEmpresa', 'sector']);
        $this->dispatch('notify', type: 'success', message: 'Empresa de demostración creada.');
    }

    // ── Modal: asignar grupos ───────────────────────────────────────────────────
    public bool $showGroupsModal = false;

    public string $editingDemoId = '';

    /** @var array<int> IDs de grupos seleccionados en el modal */
    public array $selectedGroupIds = [];

    public function openGroupsModal(string $demoId): void
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $this->editingDemoId = $demoId;
        $this->selectedGroupIds = $demo->assignedGroups()->pluck('groups.id')->map(fn ($id) => (int) $id)->toArray();
        $this->showGroupsModal = true;
    }

    public function saveGroups(): void
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demo = Tenant::on($centralConn)
            ->where('id', $this->editingDemoId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        // Validar que los grupos pertenecen al docente
        $validIds = Group::where('teacher_id', auth()->id())
            ->whereIn('id', $this->selectedGroupIds)
            ->pluck('id')
            ->toArray();

        $demo->assignedGroups()->sync($validIds);

        $this->showGroupsModal = false;
        $this->reset(['editingDemoId', 'selectedGroupIds']);

        $count = count($validIds);
        $msg = $count === 0
            ? 'Empresa retirada de todos los grupos.'
            : "Empresa asignada a {$count} ".($count === 1 ? 'grupo' : 'grupos').'.';

        $this->dispatch('notify', type: 'success', message: $msg);
    }

    // ── Modal: accesos de estudiantes ──────────────────────────────────────────
    public bool $showAccessModal = false;

    public string $accessDemoId = '';

    /** @var array<array{tenant: Tenant, accessed_at: string|null}> */
    public array $accessData = [];

    public function openAccessModal(string $demoId): void
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('teacher_id', auth()->id())
            ->with('assignedGroups')
            ->firstOrFail();

        // Todos los estudiantes de los grupos asignados
        $groupIds = $demo->assignedGroups->pluck('id');

        // Mapa groupId → groupName para mostrar en el modal
        $groupNames = $demo->assignedGroups->pluck('name', 'id');

        $students = Tenant::on($centralConn)
            ->where('type', 'student')
            ->whereIn('group_id', $groupIds)
            ->orderBy('group_id')
            ->orderBy('student_name')
            ->get(['id', 'student_name', 'company_name', 'group_id']);

        // Logs de acceso para esta demo
        $logs = ReferenceAccessLog::on($centralConn)
            ->where('demo_tenant_id', $demoId)
            ->pluck('accessed_at', 'student_tenant_id');

        $this->accessData = $students->map(fn ($s) => [
            'id' => $s->id,
            'student_name' => $s->student_name,
            'company_name' => $s->company_name,
            'group_name' => $groupNames[$s->group_id] ?? '—',
            'accessed_at' => $logs->has($s->id) ? $logs[$s->id]->format('d/m/Y H:i') : null,
        ])->toArray();

        $this->accessDemoId = $demoId;
        $this->showAccessModal = true;
    }

    // ── Eliminar ───────────────────────────────────────────────────────────────
    public function delete(string $demoId): void
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $schemaName = $demo->tenancy_db_name;
        $demo->assignedGroups()->detach();
        DB::statement("DROP SCHEMA IF EXISTS \"{$schemaName}\" CASCADE");
        Tenant::withoutEvents(fn () => $demo->delete());

        $this->dispatch('notify', type: 'success', message: 'Empresa eliminada.');
    }

    // ── Render ─────────────────────────────────────────────────────────────────
    public function render(): mixed
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demos = Tenant::on($centralConn)
            ->where('type', 'demo')
            ->where('teacher_id', auth()->id())
            ->with('assignedGroups')
            ->orderByDesc('created_at')
            ->get();

        // Conteo de accesos únicos por demo
        $demoIds = $demos->pluck('id');
        $accessCounts = ReferenceAccessLog::on($centralConn)
            ->whereIn('demo_tenant_id', $demoIds)
            ->selectRaw('demo_tenant_id, COUNT(*) as total')
            ->groupBy('demo_tenant_id')
            ->pluck('total', 'demo_tenant_id');

        // Total de estudiantes asignados por demo (via grupos)
        $studentCounts = collect();
        foreach ($demos as $demo) {
            $groupIds = $demo->assignedGroups->pluck('id');
            $total = $groupIds->isNotEmpty()
                ? Tenant::on($centralConn)->where('type', 'student')->whereIn('group_id', $groupIds)->count()
                : 0;
            $studentCounts[$demo->id] = $total;
        }

        $groups = Group::where('teacher_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.demo-companies', [
            'demos' => $demos,
            'sectors' => self::SECTORS,
            'groups' => $groups,
            'accessCounts' => $accessCounts,
            'studentCounts' => $studentCounts,
        ]);
    }
}
