<?php

namespace App\Livewire\Teacher;

use App\Models\Central\Group;
use App\Models\Central\Tenant;
use App\Models\Tenant\Invoice;
use App\Services\TenantProvisionService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithFileUploads;

    // ─── Formulario individual ────────────────────────────────────────────────

    public bool $showCreateForm = false;

    public string $createMode = 'single'; // 'single' | 'bulk'

    public string $cedula = '';

    public string $studentName = '';

    public string $companyName = '';

    public string $nitEmpresa = '';

    public string $password = '';

    // ─── Carga masiva ─────────────────────────────────────────────────────────

    /** @var mixed Livewire TemporaryUploadedFile */
    public $bulkFile = null;

    /** @var array<int,array<string,string>> Filas parseadas del CSV para vista previa */
    public array $bulkPreview = [];

    /** @var array<int,array<string,string|bool>> Resultados luego de crear */
    public array $bulkResults = [];

    public string $bulkError = '';

    // ─── Abrir modal ──────────────────────────────────────────────────────────

    public function openCreate(string $mode = 'single'): void
    {
        $this->reset([
            'cedula', 'studentName', 'companyName', 'nitEmpresa', 'password',
            'bulkFile', 'bulkPreview', 'bulkResults', 'bulkError',
        ]);
        $this->createMode = $mode;
        $this->showCreateForm = true;
    }

    public function switchMode(string $mode): void
    {
        $this->createMode = $mode;
        $this->reset(['bulkFile', 'bulkPreview', 'bulkResults', 'bulkError']);
    }

    // ─── Crear empresa individual ─────────────────────────────────────────────

    public function createCompany(TenantProvisionService $service): void
    {
        $this->validate([
            'cedula' => ['required', 'string', 'max:20', 'unique:tenants,id'],
            'studentName' => ['required', 'string', 'max:120'],
            'companyName' => ['required', 'string', 'max:120'],
            'nitEmpresa' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'cedula.unique' => 'Ya existe un estudiante con esa cédula.',
        ]);

        $group = $this->getGroup();

        $service->provision([
            'cedula' => $this->cedula,
            'student_name' => $this->studentName,
            'company_name' => $this->companyName,
            'nit_empresa' => $this->nitEmpresa,
            'group_id' => $group->id,
            'password' => $this->password,
        ]);

        $companyName = $this->companyName;

        $this->showCreateForm = false;
        $this->reset(['cedula', 'studentName', 'companyName', 'nitEmpresa', 'password']);
        session()->flash('success', "Empresa \"{$companyName}\" creada exitosamente.");
    }

    // ─── Carga masiva: parsear CSV ────────────────────────────────────────────

    public function processBulkFile(): void
    {
        $this->bulkError = '';
        $this->bulkPreview = [];
        $this->bulkResults = [];

        $this->validate([
            'bulkFile' => ['required', 'file', 'mimes:csv,txt', 'max:512'],
        ], [
            'bulkFile.required' => 'Selecciona un archivo CSV.',
            'bulkFile.mimes' => 'El archivo debe ser CSV (.csv).',
            'bulkFile.max' => 'El archivo no puede superar 512 KB.',
        ]);

        $path = $this->bulkFile->getRealPath();
        $handle = fopen($path, 'r');
        $preview = [];
        $row = 0;

        // Auto-detectar separador: Excel en español usa ";" en lugar de ","
        $firstLine = fgets($handle);
        rewind($handle);
        $separator = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        while (($cols = fgetcsv($handle, 1000, $separator)) !== false) {
            $row++;

            // Saltar cabecera
            if ($row === 1) {
                continue;
            }

            // Limpiar BOM si existe en la primera columna
            if ($row === 2 && isset($cols[0])) {
                $cols[0] = ltrim($cols[0], "\xEF\xBB\xBF");
            }

            // Ignorar filas vacías
            if (empty(array_filter($cols))) {
                continue;
            }

            if (count($cols) < 5) {
                $this->bulkError = "Fila {$row}: el archivo no tiene las 5 columnas requeridas (cedula, nombre_estudiante, nombre_empresa, nit_empresa, password).";
                fclose($handle);

                return;
            }

            [$cedula, $nombre, $empresa, $nit, $pass] = array_map('trim', $cols);

            $errors = [];
            if (empty($cedula)) {
                $errors[] = 'cédula requerida';
            }
            if (empty($nombre)) {
                $errors[] = 'nombre requerido';
            }
            if (empty($empresa)) {
                $errors[] = 'empresa requerida';
            }
            if (empty($nit)) {
                $errors[] = 'NIT requerido';
            }
            if (empty($pass) || strlen($pass) < 6) {
                $errors[] = 'contraseña mín. 6 caracteres';
            }

            $preview[] = [
                'cedula' => $cedula,
                'student_name' => $nombre,
                'company_name' => $empresa,
                'nit_empresa' => $nit,
                'password' => $pass,
                'error' => implode(', ', $errors),
            ];
        }

        fclose($handle);

        if (empty($preview)) {
            $this->bulkError = 'El archivo no contiene filas de datos (solo cabecera o está vacío).';

            return;
        }

        $this->bulkPreview = $preview;
    }

    // ─── Carga masiva: confirmar creación ─────────────────────────────────────

    public function confirmBulkCreate(TenantProvisionService $service): void
    {
        if (empty($this->bulkPreview)) {
            return;
        }

        $group = $this->getGroup();
        $results = [];

        foreach ($this->bulkPreview as $row) {
            if (! empty($row['error'])) {
                $results[] = array_merge($row, ['status' => 'error', 'message' => $row['error']]);

                continue;
            }

            if (Tenant::find($row['cedula'])) {
                $results[] = array_merge($row, ['status' => 'error', 'message' => 'La cédula ya existe.']);

                continue;
            }

            try {
                $service->provision([
                    'cedula' => $row['cedula'],
                    'student_name' => $row['student_name'],
                    'company_name' => $row['company_name'],
                    'nit_empresa' => $row['nit_empresa'],
                    'group_id' => $group->id,
                    'password' => $row['password'],
                ]);

                $results[] = array_merge($row, ['status' => 'ok', 'message' => 'Creado exitosamente.']);
            } catch (\Throwable $e) {
                $results[] = array_merge($row, ['status' => 'error', 'message' => $e->getMessage()]);
            }
        }

        $this->bulkPreview = [];
        $this->bulkResults = $results;
        $this->bulkFile = null;

        $created = count(array_filter($results, fn ($r) => $r['status'] === 'ok'));
        session()->flash('success', "{$created} empresa(s) creada(s) exitosamente.");
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getGroup(): Group
    {
        $group = auth()->user()->teacherGroups()->first();
        abort_if(! $group, 403, 'No tienes un grupo asignado.');

        return $group;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render(): mixed
    {
        $teacher = auth()->user();
        $groups = $teacher->teacherGroups()->with(['institution', 'tenants'])->get();

        $students = [];
        foreach ($groups as $group) {
            foreach ($group->tenants as $tenant) {
                $metrics = $tenant->run(function () {
                    return [
                        'invoices_count' => Invoice::where('type', 'venta')->where('status', 'emitida')->count(),
                        'invoices_total' => (float) Invoice::where('type', 'venta')->where('status', 'emitida')->sum('total'),
                        'last_invoice' => Invoice::where('type', 'venta')->max('updated_at'),
                    ];
                });

                $students[] = [
                    'tenant' => $tenant,
                    'group' => $group,
                    'metrics' => $metrics,
                ];
            }
        }

        return view('livewire.teacher.dashboard', compact('groups', 'students'))
            ->title('Panel Docente');
    }
}
