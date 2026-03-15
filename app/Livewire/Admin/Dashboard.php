<?php

namespace App\Livewire\Admin;

use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    // ── Tab activa ──────────────────────────────────────────────────────────
    public string $tab = 'instituciones';

    // ── Institución ─────────────────────────────────────────────────────────
    public bool   $showInstForm  = false;
    public ?int   $instEditingId = null;
    public string $instName      = '';
    public string $instNit       = '';
    public string $instCity      = '';

    // ── Docente ─────────────────────────────────────────────────────────────
    public bool   $showTeacherForm  = false;
    public ?int   $teacherEditingId = null;
    public string $teacherName      = '';
    public string $teacherEmail     = '';
    public string $teacherPassword  = '';
    public int    $teacherInstitution = 0; // para crear grupo automático

    // ── Institución CRUD ────────────────────────────────────────────────────

    public function openCreateInst(): void
    {
        $this->reset(['instEditingId', 'instName', 'instNit', 'instCity']);
        $this->showInstForm = true;
    }

    public function openEditInst(int $id): void
    {
        $inst = Institution::findOrFail($id);
        $this->instEditingId = $id;
        $this->instName      = $inst->name;
        $this->instNit       = $inst->nit ?? '';
        $this->instCity      = $inst->city ?? '';
        $this->showInstForm  = true;
    }

    public function saveInst(): void
    {
        $this->validate([
            'instName' => ['required', 'string', 'max:150'],
            'instNit'  => ['nullable', 'string', 'max:20'],
            'instCity' => ['nullable', 'string', 'max:100'],
        ]);

        $data = ['name' => $this->instName, 'nit' => $this->instNit, 'city' => $this->instCity, 'active' => true];

        if ($this->instEditingId) {
            Institution::findOrFail($this->instEditingId)->update($data);
        } else {
            Institution::create($data);
        }

        $this->showInstForm = false;
        $this->reset(['instEditingId', 'instName', 'instNit', 'instCity']);
        session()->flash('success', 'Institución guardada.');
    }

    public function deleteInst(int $id): void
    {
        Institution::findOrFail($id)->delete();
    }

    // ── Docente CRUD ────────────────────────────────────────────────────────

    public function openCreateTeacher(): void
    {
        $this->reset(['teacherEditingId', 'teacherName', 'teacherEmail', 'teacherPassword', 'teacherInstitution']);
        $this->showTeacherForm = true;
    }

    public function openEditTeacher(int $id): void
    {
        $teacher = User::findOrFail($id);
        $this->teacherEditingId = $id;
        $this->teacherName      = $teacher->name;
        $this->teacherEmail     = $teacher->email;
        $this->teacherPassword  = '';
        $this->showTeacherForm  = true;
    }

    public function saveTeacher(): void
    {
        $rules = [
            'teacherName'  => ['required', 'string', 'max:150'],
            'teacherEmail' => ['required', 'email', 'max:150'],
        ];

        if (! $this->teacherEditingId) {
            $rules['teacherPassword']    = ['required', 'string', 'min:6'];
            $rules['teacherInstitution'] = ['required', 'integer', 'min:1'];
        } elseif ($this->teacherPassword) {
            $rules['teacherPassword'] = ['string', 'min:6'];
        }

        $this->validate($rules, [
            'teacherInstitution.min' => 'Selecciona una institución.',
        ]);

        if ($this->teacherEditingId) {
            $teacher = User::findOrFail($this->teacherEditingId);
            $teacher->update(array_filter([
                'name'     => $this->teacherName,
                'email'    => $this->teacherEmail,
                'password' => $this->teacherPassword ? Hash::make($this->teacherPassword) : null,
            ]));
        } else {
            $teacher = User::create([
                'name'     => $this->teacherName,
                'email'    => $this->teacherEmail,
                'password' => Hash::make($this->teacherPassword),
                'role'     => 'teacher',
            ]);

            // Crear grupo por defecto para este docente
            Group::create([
                'institution_id' => $this->teacherInstitution,
                'teacher_id'     => $teacher->id,
                'name'           => 'Grupo ' . date('Y'),
                'period'         => date('Y') . '-1',
                'active'         => true,
            ]);
        }

        $this->showTeacherForm = false;
        $this->reset(['teacherEditingId', 'teacherName', 'teacherEmail', 'teacherPassword', 'teacherInstitution']);
        session()->flash('success', 'Docente guardado.');
    }

    public function deleteTeacher(int $id): void
    {
        User::where('id', $id)->where('role', 'teacher')->delete();
    }

    public function render(): mixed
    {
        $institutions = Institution::withCount('groups')->orderBy('name')->get();
        $teachers     = User::where('role', 'teacher')->with('teacherGroups.institution')->orderBy('name')->get();

        return view('livewire.admin.dashboard', compact('institutions', 'teachers'))
            ->title('Superadmin');
    }
}
