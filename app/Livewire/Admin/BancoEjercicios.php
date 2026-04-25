<?php

namespace App\Livewire\Admin;

use App\Imports\EjerciciosImport;
use App\Models\Central\Exercise;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.admin')]
#[Title('Banco de ejercicios')]
class BancoEjercicios extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $gTitle = '';

    public string $gInstructions = '';

    public string $gType = 'factura_venta';

    public string $gMontoMinimo = '';

    public string $gCuentaPuc = '';

    public int $gPuntos = 10;

    public $ejerciciosFile = null;

    public bool $fileReady = false;

    public ?array $importResult = null;

    public int $perPage = 25;

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedEjerciciosFile(): void
    {
        $this->fileReady = $this->ejerciciosFile !== null;
    }

    public function openForm(?int $id = null): void
    {
        $this->editingId = $id;

        if ($id) {
            $ex = Exercise::global()->findOrFail($id);
            $this->gTitle = $ex->title;
            $this->gInstructions = $ex->instructions ?? '';
            $this->gType = $ex->type;
            $this->gMontoMinimo = $ex->monto_minimo ? (string) $ex->monto_minimo : '';
            $this->gCuentaPuc = $ex->cuenta_puc_requerida ?? '';
            $this->gPuntos = $ex->puntos;
        } else {
            $this->reset(['gTitle', 'gInstructions', 'gMontoMinimo', 'gCuentaPuc']);
            $this->gType = 'factura_venta';
            $this->gPuntos = 10;
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'gTitle' => ['required', 'string', 'max:200'],
            'gType' => ['required', 'in:factura_venta,factura_compra,asiento_manual,registro_tercero,registro_producto,pago_proveedor'],
            'gMontoMinimo' => ['nullable', 'numeric', 'min:0'],
            'gPuntos' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $data = [
            'teacher_id' => auth()->id(),
            'title' => $this->gTitle,
            'instructions' => $this->gInstructions ?: null,
            'type' => $this->gType,
            'monto_minimo' => $this->gMontoMinimo !== '' ? (float) $this->gMontoMinimo : null,
            'cuenta_puc_requerida' => $this->gCuentaPuc ?: null,
            'puntos' => $this->gPuntos,
            'is_global' => true,
            'active' => true,
        ];

        if ($this->editingId) {
            Exercise::global()->findOrFail($this->editingId)->update($data);
            $msg = 'Ejercicio oficial actualizado.';
        } else {
            Exercise::create($data);
            $msg = 'Ejercicio oficial creado.';
        }

        $this->showForm = false;
        $this->editingId = null;
        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function delete(int $id): void
    {
        Exercise::global()->findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Ejercicio eliminado.');
    }

    public function importEjercicios(): void
    {
        $this->validate([
            'ejerciciosFile' => ['required', 'file', 'extensions:xlsx,xls', 'max:2048'],
        ], [
            'ejerciciosFile.required' => 'Selecciona un archivo Excel.',
            'ejerciciosFile.extensions' => 'El archivo debe ser .xlsx o .xls.',
            'ejerciciosFile.max' => 'El archivo no puede superar 2 MB.',
        ]);

        try {
            $import = new EjerciciosImport(teacherId: auth()->id(), isGlobal: true);
            Excel::import($import, $this->ejerciciosFile);

            $this->importResult = [
                'imported' => $import->imported,
                'errors' => $import->errors,
            ];
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al procesar el archivo: '.$e->getMessage());
        } finally {
            $this->ejerciciosFile = null;
            $this->fileReady = false;
        }
    }

    public function render(): mixed
    {
        $query = Exercise::global()
            ->withCount('assignments')
            ->orderByDesc('created_at');

        $total = $query->count();
        $exercises = $this->perPage > 0
            ? $query->paginate($this->perPage)
            : $query->get();

        return view('livewire.admin.banco-ejercicios', compact('exercises', 'total'));
    }
}
