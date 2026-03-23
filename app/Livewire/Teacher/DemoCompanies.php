<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Tenant;
use App\Services\TenantProvisionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class DemoCompanies extends Component
{
    public const SECTORS = [
        'comercio'     => 'Comercio al por mayor y menor',
        'servicios'    => 'Servicios profesionales',
        'manufactura'  => 'Manufactura / Industria',
        'construccion' => 'Construcción',
        'agropecuario' => 'Agropecuario',
        'otro'         => 'Otro',
    ];

    // ── Formulario ─────────────────────────────────────────────────────────────
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
            'nitEmpresa'  => ['required', 'string', 'max:20'],
            'sector'      => ['required', 'string', 'in:'.implode(',', array_keys(self::SECTORS))],
        ]);

        $teacher = auth()->user();

        $service->provisionDemo([
            'teacher_id'   => $teacher->id,
            'teacher_name' => $teacher->name,
            'company_name' => $this->companyName,
            'nit_empresa'  => $this->nitEmpresa,
            'sector'       => $this->sector,
        ]);

        $this->showForm = false;
        $this->reset(['companyName', 'nitEmpresa', 'sector']);
        $this->dispatch('notify', type: 'success', message: 'Empresa de demostración creada.');
    }

    public function togglePublished(string $demoId): void
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $demo->update(['published' => ! $demo->published]);

        $status = $demo->published ? 'publicada para estudiantes' : 'ocultada a estudiantes';
        $this->dispatch('notify', type: 'success', message: "Empresa {$status}.");
    }

    public function delete(string $demoId): void
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $schemaName = $demo->tenancy_db_name;
        DB::statement("DROP SCHEMA IF EXISTS \"{$schemaName}\" CASCADE");
        Tenant::withoutEvents(fn () => $demo->delete());

        $this->dispatch('notify', type: 'success', message: 'Empresa eliminada.');
    }

    public function render(): mixed
    {
        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $demos = Tenant::on($centralConn)
            ->where('type', 'demo')
            ->where('teacher_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.teacher.demo-companies', [
            'demos'   => $demos,
            'sectors' => self::SECTORS,
        ])->title('Mis Empresas Demo');
    }
}
