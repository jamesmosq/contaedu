<?php

namespace App\Livewire\Student;

use App\Models\Central\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Empresas de referencia')]
class Referencias extends Component
{
    public function render(): mixed
    {
        $student = auth('student')->user();
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $demos = collect();

        if ($student->group_id) {
            // Solo muestra demos asignadas específicamente al grupo del estudiante.
            // Un JOIN sobre demo_group (tabla central) — sin cambios de schema.
            $demos = Tenant::on($centralConn)
                ->where('type', 'demo')
                ->whereHas('assignedGroups', fn ($q) => $q->where('groups.id', $student->group_id))
                ->orderBy('sector')
                ->orderBy('company_name')
                ->get();
        }

        return view('livewire.student.referencias', compact('demos'));
    }
}
