<?php

namespace App\Livewire\Coordinator;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.coordinator')]
#[Title('Actividad')]
class Actividad extends Component
{
    public int $selectedGroupId = 0;

    public string $filterStatus = '';

    public function render(): mixed
    {
        $institution = $this->institution();

        $groups = Group::where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();

        $query = Tenant::where('type', 'student')
            ->whereHas('group', fn ($q) => $q->where('institution_id', $institution->id))
            ->with('group')
            ->orderByDesc('last_activity_at');

        if ($this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        }

        $tenants = $query->get()->filter(function (Tenant $tenant) {
            if (! $this->filterStatus) {
                return true;
            }

            return $tenant->activityStatus()->value === $this->filterStatus;
        })->values();

        $stats = [
            'total' => $tenants->count(),
            'active' => $tenants->filter(fn ($t) => $t->activityStatus()->value === 'active')->count(),
            'inactive' => $tenants->filter(fn ($t) => $t->activityStatus()->value === 'inactive')->count(),
            'never' => $tenants->filter(fn ($t) => $t->activityStatus()->value === 'never_active')->count(),
        ];

        return view('livewire.coordinator.actividad', [
            'institution' => $institution,
            'groups' => $groups,
            'tenants' => $tenants,
            'stats' => $stats,
        ]);
    }

    private function institution(): Institution
    {
        $institution = auth()->user()->coordinatedInstitution;
        abort_if(! $institution, 403);

        return $institution;
    }
}
