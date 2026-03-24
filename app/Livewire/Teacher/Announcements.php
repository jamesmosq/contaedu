<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Announcement;
use App\Models\Central\Group;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.teacher')]
#[Title('Anuncios')]
class Announcements extends Component
{
    public ?int $selectedGroupId = null;

    // ── Formulario ────────────────────────────────────────────────────────────
    public bool $showForm = false;

    public ?int $editingId = null;

    public int $formGroupId = 0;

    public string $title = '';

    public string $body = '';

    public string $dueDate = '';

    public bool $formActive = true;

    public function selectGroup(int $id): void
    {
        $this->selectedGroupId = $id;
    }

    public function clearGroup(): void
    {
        $this->selectedGroupId = null;
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'title', 'body', 'dueDate']);
        $this->formGroupId = $this->selectedGroupId ?? 0;
        $this->formActive = true;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $ann = Announcement::where('teacher_id', auth()->id())->findOrFail($id);
        $this->editingId = $id;
        $this->formGroupId = $ann->group_id;
        $this->title = $ann->title;
        $this->body = $ann->body ?? '';
        $this->dueDate = $ann->due_date?->toDateString() ?? '';
        $this->formActive = $ann->active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'formGroupId' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:2000'],
            'dueDate' => ['nullable', 'date'],
        ], [
            'formGroupId.min' => 'Selecciona un grupo.',
        ]);

        // Validar que el grupo pertenece al docente
        $group = Group::where('teacher_id', auth()->id())
            ->findOrFail($this->formGroupId);

        $data = [
            'teacher_id' => auth()->id(),
            'group_id' => $group->id,
            'title' => $this->title,
            'body' => $this->body ?: null,
            'due_date' => $this->dueDate ?: null,
            'active' => $this->formActive,
        ];

        if ($this->editingId) {
            Announcement::where('teacher_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            Announcement::create($data);
        }

        $this->showForm = false;
        $this->reset(['editingId', 'title', 'body', 'dueDate']);
        $this->dispatch('notify', type: 'success', message: 'Aviso guardado.');
    }

    public function toggleActive(int $id): void
    {
        $ann = Announcement::where('teacher_id', auth()->id())->findOrFail($id);
        $ann->update(['active' => ! $ann->active]);
    }

    public function delete(int $id): void
    {
        Announcement::where('teacher_id', auth()->id())->findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Aviso eliminado.');
    }

    public function render(): mixed
    {
        $groups = Group::where('teacher_id', auth()->id())
            ->orderBy('name')
            ->get();

        $announcements = collect();
        $selectedGroup = null;

        if ($this->selectedGroupId) {
            $selectedGroup = $groups->firstWhere('id', $this->selectedGroupId);
            $announcements = Announcement::where('teacher_id', auth()->id())
                ->where('group_id', $this->selectedGroupId)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('livewire.teacher.announcements', compact('groups', 'announcements', 'selectedGroup'));
    }
}
