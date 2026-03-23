<?php

namespace App\Livewire\Student;

use App\Models\Central\Group;
use App\Models\Central\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class Referencias extends Component
{
    public function render(): mixed
    {
        $student = auth('student')->user();
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        $group = Group::on($centralConn)->find($student->group_id);

        $demos = collect();
        if ($group) {
            $demos = Tenant::on($centralConn)
                ->where('type', 'demo')
                ->where('teacher_id', $group->teacher_id)
                ->where('published', true)
                ->orderBy('sector')
                ->orderBy('company_name')
                ->get();
        }

        return view('livewire.student.referencias', compact('demos'))
            ->title('Empresas de Referencia');
    }
}
