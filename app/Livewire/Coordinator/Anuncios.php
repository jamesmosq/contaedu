<?php

namespace App\Livewire\Coordinator;

use App\Models\Central\Announcement;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.coordinator')]
#[Title('Anuncios')]
class Anuncios extends Component
{
    public int $selectedGroupId = 0;

    public function render(): mixed
    {
        $institution = $this->institution();

        $groups = Group::where('institution_id', $institution->id)
            ->orderBy('name')
            ->get();

        $query = Announcement::whereHas('group', fn ($q) => $q->where('institution_id', $institution->id))
            ->with(['group', 'teacher'])
            ->orderByDesc('created_at');

        if ($this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        }

        $announcements = $query->get();

        return view('livewire.coordinator.anuncios', [
            'institution' => $institution,
            'groups' => $groups,
            'announcements' => $announcements,
        ]);
    }

    private function institution(): Institution
    {
        $institution = auth()->user()->coordinatedInstitution;
        abort_if(! $institution, 403);

        return $institution;
    }
}
