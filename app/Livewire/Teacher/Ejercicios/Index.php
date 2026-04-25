<?php

namespace App\Livewire\Teacher\Ejercicios;

use App\Models\Central\Exercise;
use App\Models\Central\ExerciseAssignment;
use App\Models\Central\ExerciseCompletion;
use App\Models\Central\Group;
use App\Models\Central\Tenant as CentralTenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.teacher')]
#[Title('Ejercicios')]
class Index extends Component
{
    public string $tab = 'ejercicios'; // 'ejercicios' | 'asignar' | 'resultados'

    // ── Crear ejercicio ────────────────────────────────────────────────────────
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $instructions = '';

    public string $type = 'factura_venta';

    public string $monto_minimo = '';

    public string $cuenta_puc_requerida = '';

    public int $puntos = 10;

    // ── Asignar ejercicio a grupo ──────────────────────────────────────────────
    public bool $showAssignModal = false;

    public ?int $assigningExerciseId = null;

    public int $assignGroupId = 0;

    public string $assignDueDate = '';

    // ── Ver resultados ─────────────────────────────────────────────────────────
    public ?int $viewingAssignmentId = null;

    public function openForm(?int $id = null): void
    {
        $this->editingId = $id;

        if ($id) {
            $ex = Exercise::where('id', $id)->where('teacher_id', auth()->id())->firstOrFail();
            $this->title = $ex->title;
            $this->instructions = $ex->instructions ?? '';
            $this->type = $ex->type;
            $this->monto_minimo = $ex->monto_minimo ? (string) $ex->monto_minimo : '';
            $this->cuenta_puc_requerida = $ex->cuenta_puc_requerida ?? '';
            $this->puntos = $ex->puntos;
        } else {
            $this->reset(['title', 'instructions', 'monto_minimo', 'cuenta_puc_requerida']);
            $this->type = 'factura_venta';
            $this->puntos = 10;
        }

        $this->showForm = true;
    }

    public function saveExercise(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:factura_venta,factura_compra,asiento_manual,registro_tercero,registro_producto,pago_proveedor'],
            'monto_minimo' => ['nullable', 'numeric', 'min:0'],
            'puntos' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $data = [
            'teacher_id' => auth()->id(),
            'title' => $this->title,
            'instructions' => $this->instructions ?: null,
            'type' => $this->type,
            'monto_minimo' => $this->monto_minimo !== '' ? (float) $this->monto_minimo : null,
            'cuenta_puc_requerida' => $this->cuenta_puc_requerida ?: null,
            'puntos' => $this->puntos,
        ];

        if ($this->editingId) {
            Exercise::where('id', $this->editingId)->where('teacher_id', auth()->id())->update($data);
            $msg = 'Ejercicio actualizado.';
        } else {
            Exercise::create($data);
            $msg = 'Ejercicio creado.';
        }

        $this->showForm = false;
        $this->editingId = null;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function toggleActive(int $id): void
    {
        $ex = Exercise::where('id', $id)->where('teacher_id', auth()->id())->firstOrFail();
        $ex->update(['active' => ! $ex->active]);
        $this->dispatch('notify', type: 'success', message: $ex->active ? 'Ejercicio activado.' : 'Ejercicio desactivado.');
    }

    public function openAssign(int $exerciseId): void
    {
        $this->assigningExerciseId = $exerciseId;
        $this->assignGroupId = 0;
        $this->assignDueDate = '';
        $this->showAssignModal = true;
    }

    public function confirmAssign(): void
    {
        $this->validate([
            'assignGroupId' => ['required', 'integer', 'min:1'],
        ], ['assignGroupId.min' => 'Selecciona un grupo.']);

        $group = Group::where('id', $this->assignGroupId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        ExerciseAssignment::create([
            'exercise_id' => $this->assigningExerciseId,
            'group_id' => $group->id,
            'assigned_at' => now(),
            'due_date' => $this->assignDueDate ?: null,
        ]);

        $this->showAssignModal = false;
        $this->dispatch('notify', type: 'success', message: 'Ejercicio asignado al grupo '.$group->name.'.');
    }

    public function deleteAssignment(int $id): void
    {
        $assignment = ExerciseAssignment::whereHas('exercise', fn ($q) => $q->where('teacher_id', auth()->id()))
            ->findOrFail($id);
        $assignment->delete();
        $this->dispatch('notify', type: 'success', message: 'Asignación eliminada.');
    }

    public function viewResults(int $assignmentId): void
    {
        $this->viewingAssignmentId = $assignmentId;
        $this->tab = 'resultados';
    }

    public function cloneGlobal(int $exerciseId): void
    {
        $source = Exercise::global()->findOrFail($exerciseId);

        Exercise::create([
            'teacher_id' => auth()->id(),
            'title' => $source->title,
            'instructions' => $source->instructions,
            'type' => $source->type,
            'monto_minimo' => $source->monto_minimo,
            'cuenta_puc_requerida' => $source->cuenta_puc_requerida,
            'puntos' => $source->puntos,
            'active' => true,
            'is_global' => false,
            'cloned_from_id' => $source->id,
        ]);

        $this->dispatch('notify', type: 'success', message: '«'.$source->title.'» copiado a tus ejercicios.');
    }

    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups = Group::where('teacher_id', $teacher->id)->orderBy('name')->get();

        $exercises = Exercise::where('teacher_id', $teacher->id)
            ->withCount('assignments')
            ->orderByDesc('created_at')
            ->get();

        $assignments = ExerciseAssignment::whereHas('exercise', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->with(['exercise', 'group'])
            ->withCount('completions')
            ->orderByDesc('assigned_at')
            ->get();

        $results = null;
        $viewingAssignment = null;

        if ($this->viewingAssignmentId) {
            $viewingAssignment = ExerciseAssignment::with(['exercise', 'group'])->find($this->viewingAssignmentId);

            if ($viewingAssignment) {
                $tenants = CentralTenant::where('group_id', $viewingAssignment->group_id)
                    ->where('type', 'student')
                    ->where('active', true)
                    ->orderBy('student_name')
                    ->get();

                $completions = ExerciseCompletion::where('assignment_id', $this->viewingAssignmentId)
                    ->get()
                    ->keyBy('tenant_id');

                $results = $tenants->map(fn ($t) => [
                    'tenant' => $t,
                    'completion' => $completions->get($t->id),
                ]);
            }
        }

        $globalExercises = Exercise::global()->orderByDesc('created_at')->get();

        return view('livewire.teacher.ejercicios.index', compact(
            'exercises', 'assignments', 'groups', 'results', 'viewingAssignment', 'globalExercises'
        ));
    }
}
