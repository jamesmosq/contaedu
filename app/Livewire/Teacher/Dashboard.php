<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Tenant;
use App\Models\Tenant\Invoice;
use App\Services\TenantProvisionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    // Formulario crear empresa
    public bool   $showCreateForm = false;
    public string $cedula         = '';
    public string $studentName    = '';
    public string $companyName    = '';
    public string $nitEmpresa     = '';
    public string $password       = '';

    public function openCreate(): void
    {
        $this->reset(['cedula', 'studentName', 'companyName', 'nitEmpresa', 'password']);
        $this->showCreateForm = true;
    }

    public function createCompany(TenantProvisionService $service): void
    {
        $this->validate([
            'cedula'      => ['required', 'string', 'max:20', 'unique:tenants,id'],
            'studentName' => ['required', 'string', 'max:120'],
            'companyName' => ['required', 'string', 'max:120'],
            'nitEmpresa'  => ['required', 'string', 'max:20'],
            'password'    => ['required', 'string', 'min:6'],
        ], [
            'cedula.unique' => 'Ya existe un estudiante con esa cédula.',
        ]);

        $teacher = auth()->user();
        $group   = $teacher->teacherGroups()->first();

        abort_if(! $group, 403, 'No tienes un grupo asignado.');

        $service->provision([
            'cedula'       => $this->cedula,
            'student_name' => $this->studentName,
            'company_name' => $this->companyName,
            'nit_empresa'  => $this->nitEmpresa,
            'group_id'     => $group->id,
            'password'     => $this->password,
        ]);

        $this->showCreateForm = false;
        $this->reset(['cedula', 'studentName', 'companyName', 'nitEmpresa', 'password']);
        session()->flash('success', "Empresa \"{$this->companyName}\" creada exitosamente.");
    }

    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups  = $teacher->teacherGroups()->with(['institution', 'tenants'])->get();

        $students = [];
        foreach ($groups as $group) {
            foreach ($group->tenants as $tenant) {
                $metrics = $tenant->run(function () {
                    return [
                        'invoices_count' => Invoice::where('type', 'venta')->where('status', 'emitida')->count(),
                        'invoices_total' => (float) Invoice::where('type', 'venta')->where('status', 'emitida')->sum('total'),
                        'last_invoice'   => Invoice::where('type', 'venta')->max('updated_at'),
                    ];
                });

                $students[] = [
                    'tenant'  => $tenant,
                    'group'   => $group,
                    'metrics' => $metrics,
                ];
            }
        }

        return view('livewire.teacher.dashboard', compact('groups', 'students'))
            ->title('Panel Docente');
    }
}
