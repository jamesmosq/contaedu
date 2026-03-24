<?php

namespace App\Livewire\Teacher;

use App\Models\Tenant\CompanySummary;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.teacher')]
#[Title('Panel comparativo')]
class Comparativo extends Component
{
    public ?int $selectedGroupId = null;

    public function selectGroup(int $id): void
    {
        $this->selectedGroupId = $id;
    }

    public function clearGroup(): void
    {
        $this->selectedGroupId = null;
    }

    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups = $teacher->teacherGroups()->with('institution')->orderBy('created_at')->get();

        $selectedGroup = null;
        $rows = [];

        if ($this->selectedGroupId) {
            $selectedGroup = $groups->firstWhere('id', $this->selectedGroupId);

            if ($selectedGroup) {
                $tenants = $selectedGroup->load('tenants')->tenants;

                foreach ($tenants as $tenant) {
                    // Leer el resumen materializado: 1 sola query, sin cambio de schema
                    $summary = $tenant->run(fn () => CompanySummary::first());

                    $rows[] = [
                        'tenant' => $tenant,
                        'group' => $selectedGroup,
                        'summary' => $summary,
                    ];
                }
            }
        }

        return view('livewire.teacher.comparativo', compact('groups', 'selectedGroup', 'rows'));
    }
}
