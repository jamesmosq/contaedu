<?php

namespace App\Livewire\Coordinator;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\StudentScore;
use App\Models\Central\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.coordinator')]
#[Title('Calificaciones')]
class Calificaciones extends Component
{
    public int $selectedGroupId = 0;

    public static array $modules = [
        'maestros' => 'Maestros',
        'facturacion' => 'Facturación',
        'compras' => 'Compras',
        'cierre' => 'Cierre',
    ];

    public function render(): mixed
    {
        $institution = $this->institution();

        $groups = Group::where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();

        $query = Tenant::where('type', 'student')
            ->whereHas('group', fn ($q) => $q->where('institution_id', $institution->id))
            ->with('group');

        if ($this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        }

        $tenants = $query->orderBy('student_name')->get();

        $scores = StudentScore::whereIn('tenant_id', $tenants->pluck('id'))
            ->current()
            ->get()
            ->groupBy('tenant_id')
            ->map(fn ($rows) => $rows->keyBy('module'));

        $rows = $tenants->map(function (Tenant $tenant) use ($scores) {
            $tenantScores = $scores->get($tenant->id, collect());
            $values = [];

            foreach (array_keys(self::$modules) as $mod) {
                $values[$mod] = $tenantScores->has($mod)
                    ? (float) $tenantScores[$mod]->score
                    : null;
            }

            $filled = array_filter($values, fn ($v) => $v !== null);
            $promedio = count($filled) > 0
                ? round(array_sum($filled) / count($filled), 1)
                : null;

            return [
                'tenant' => $tenant,
                'scores' => $values,
                'promedio' => $promedio,
            ];
        });

        return view('livewire.coordinator.calificaciones', [
            'institution' => $institution,
            'groups' => $groups,
            'rows' => $rows,
        ]);
    }

    private function institution(): Institution
    {
        $institution = auth()->user()->coordinatedInstitution;
        abort_if(! $institution, 403);

        return $institution;
    }
}
