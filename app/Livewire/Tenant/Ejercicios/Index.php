<?php

namespace App\Livewire\Tenant\Ejercicios;

use App\Models\Central\ExerciseAssignment;
use App\Models\Central\ExerciseCompletion;
use App\Models\Central\Tenant as CentralTenant;
use App\Services\ExerciseVerificationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Ejercicios')]
class Index extends Component
{
    public ?int $submittingId = null;

    public function submit(int $assignmentId, ExerciseVerificationService $service): void
    {
        $student = auth('student')->user();

        // Pre-cargar assignment y exercise antes de entrar al contexto del tenant
        $assignment = ExerciseAssignment::with('exercise')->find($assignmentId);

        if (! $assignment) {
            $this->dispatch('notify', type: 'error', message: 'Asignación no encontrada.');

            return;
        }

        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $tenant = CentralTenant::on($centralConn)->find($student->id);

        if (! $tenant) {
            $this->dispatch('notify', type: 'error', message: 'Error al identificar la empresa.');

            return;
        }

        $this->submittingId = $assignmentId;

        try {
            $result = $service->submit($assignment, $tenant);

            $label = match ($result['result']) {
                'aprobado' => '¡Ejercicio aprobado!',
                'parcial' => 'Parcialmente cumplido.',
                default => 'No cumple los requisitos.',
            };

            $type = match ($result['result']) {
                'aprobado' => 'success',
                'parcial' => 'warning',
                default => 'error',
            };

            $this->dispatch('notify', type: $type, message: $label.' '.$result['detail']['mensaje']);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al verificar: '.$e->getMessage());
        } finally {
            $this->submittingId = null;
        }
    }

    public function render(): mixed
    {
        $student = auth('student')->user();

        // Obtener el group_id del tenant desde Central (sin cambio de contexto)
        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $tenant = CentralTenant::on($centralConn)->find($student->id);

        if (! $tenant || ! $tenant->group_id) {
            return view('livewire.tenant.ejercicios.index', ['items' => collect()]);
        }

        $assignments = ExerciseAssignment::with('exercise')
            ->where('group_id', $tenant->group_id)
            ->whereHas('exercise', fn ($q) => $q->where('active', true))
            ->orderByDesc('assigned_at')
            ->get();

        $completions = ExerciseCompletion::where('tenant_id', $student->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $items = $assignments->map(fn ($a) => [
            'assignment' => $a,
            'completion' => $completions->get($a->id),
        ]);

        return view('livewire.tenant.ejercicios.index', compact('items'));
    }
}
