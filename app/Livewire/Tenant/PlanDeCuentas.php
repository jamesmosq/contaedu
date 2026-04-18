<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Plan de cuentas')]
class PlanDeCuentas extends Component
{
    public bool $showForm = false;

    public string $search = '';

    // Form
    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'activo';

    public string $nature = 'debito';

    public ?int $parent_id = null;

    // Navegación guiada
    public ?string $selectedClase  = null;

    public ?string $selectedGrupo  = null;

    public ?string $selectedCuenta = null;

    public string $pasoActual = 'clase'; // 'clase'|'grupo'|'cuenta'|'subcuenta'

    public string $codigoSugerido = '';

    public function rules(): array
    {
        $uniqueCode = $this->editingId
            ? 'unique:accounts,code,' . $this->editingId
            : 'unique:accounts,code';

        return [
            'code'      => ['required', 'string', 'max:10', $uniqueCode],
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'in:activo,pasivo,patrimonio,ingreso,costo,gasto,orden'],
            'nature'    => ['required', 'in:debito,credito'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
        ];
    }

    public function seleccionarClase(string $codigo): void
    {
        $this->selectedClase  = $codigo;
        $this->selectedGrupo  = null;
        $this->selectedCuenta = null;
        $this->pasoActual     = 'grupo';
        $this->code           = '';
        $this->codigoSugerido = '';

        $clase = Account::where('code', $codigo)->first();
        if ($clase) {
            $this->type      = $clase->type;
            $this->nature    = $clase->nature;
            $this->parent_id = $clase->id;
        }
    }

    public function seleccionarGrupo(string $codigo): void
    {
        $this->selectedGrupo  = $codigo;
        $this->selectedCuenta = null;
        $this->pasoActual     = 'cuenta';

        $grupo = Account::where('code', $codigo)->first();
        if ($grupo) {
            $this->type      = $grupo->type;
            $this->nature    = $grupo->nature;
            $this->parent_id = $grupo->id;
        }
    }

    public function seleccionarCuenta(string $codigo): void
    {
        $this->selectedCuenta = $codigo;
        $this->pasoActual     = 'subcuenta';

        $cuenta = Account::where('code', $codigo)->first();
        if ($cuenta) {
            $this->type      = $cuenta->type;
            $this->nature    = $cuenta->nature;
            $this->parent_id = $cuenta->id;
        }

        $this->codigoSugerido = $this->sugerirCodigo($codigo);
        $this->code           = $this->codigoSugerido;
    }

    public function volverAPaso(string $paso): void
    {
        $this->pasoActual = $paso;
        if ($paso === 'clase') {
            $this->selectedClase  = null;
            $this->selectedGrupo  = null;
            $this->selectedCuenta = null;
        } elseif ($paso === 'grupo') {
            $this->selectedGrupo  = null;
            $this->selectedCuenta = null;
        } elseif ($paso === 'cuenta') {
            $this->selectedCuenta = null;
        }
        $this->code           = '';
        $this->codigoSugerido = '';
    }

    private function sugerirCodigo(string $codigoCuenta): string
    {
        $ultimo = Account::where('code', 'like', $codigoCuenta . '%')
            ->where('level', 4)
            ->orderByDesc('code')
            ->value('code');

        if (! $ultimo) {
            return $codigoCuenta . '05';
        }

        $sufijo      = (int) substr($ultimo, -2);
        $nuevoSufijo = str_pad($sufijo + 5, 2, '0', STR_PAD_LEFT);

        return $codigoCuenta . $nuevoSufijo;
    }

    public function openForm(?int $parentId = null): void
    {
        $this->reset(['editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
        $this->selectedClase  = null;
        $this->selectedGrupo  = null;
        $this->selectedCuenta = null;
        $this->pasoActual     = 'clase';
        $this->codigoSugerido = '';

        if ($parentId) {
            $parent          = Account::find($parentId);
            $this->type      = $parent->type;
            $this->nature    = $parent->nature;
            $this->parent_id = $parentId;

            $this->pasoActual = match ($parent->level) {
                1       => 'grupo',
                2       => 'cuenta',
                3       => 'subcuenta',
                default => 'clase',
            };

            if ($parent->level === 1) {
                $this->selectedClase = $parent->code;
            }
            if ($parent->level === 2) {
                $this->selectedClase = substr($parent->code, 0, 1);
                $this->selectedGrupo = $parent->code;
            }
            if ($parent->level === 3) {
                $this->selectedClase  = substr($parent->code, 0, 1);
                $this->selectedGrupo  = substr($parent->code, 0, 2);
                $this->selectedCuenta = $parent->code;
                $this->codigoSugerido = $this->sugerirCodigo($parent->code);
                $this->code           = $this->codigoSugerido;
            }
        }

        $this->showForm = true;
    }

    public function validationAttributes(): array
    {
        return [
            'code' => 'código de cuenta',
            'name' => 'nombre',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $level = $this->parent_id
            ? Account::find($this->parent_id)->level + 1
            : 1;

        Account::create([
            'code'      => $this->code,
            'name'      => $this->name,
            'type'      => $this->type,
            'nature'    => $this->nature,
            'parent_id' => $this->parent_id,
            'level'     => $level,
            'active'    => true,
        ]);

        $this->cancelForm();
        $this->dispatch('notify', type: 'success', message: 'Cuenta guardada correctamente.');
    }

    public function cancelForm(): void
    {
        $this->reset(['showForm', 'editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
        $this->selectedClase  = null;
        $this->selectedGrupo  = null;
        $this->selectedCuenta = null;
        $this->pasoActual     = 'clase';
        $this->codigoSugerido = '';
    }

    public function render(): mixed
    {
        $accounts = Account::query()
            ->when($this->search, fn ($q) => $q
                ->where('code', 'ilike', "%{$this->search}%")
                ->orWhere('name', 'ilike', "%{$this->search}%")
            )
            ->orderBy('code')
            ->get()
            ->groupBy('level');

        $parentAccounts = Account::where('level', '<', 5)->orderBy('code')->get();

        $clases  = Account::where('level', 1)->orderBy('code')->get();
        $grupos  = $this->selectedClase
            ? Account::where('level', 2)
                     ->where('code', 'like', $this->selectedClase . '%')
                     ->orderBy('code')->get()
            : collect();
        $cuentas = $this->selectedGrupo
            ? Account::where('level', 3)
                     ->where('code', 'like', $this->selectedGrupo . '%')
                     ->orderBy('code')->get()
            : collect();

        return view('livewire.tenant.cuentas.plan-de-cuentas',
            compact('accounts', 'parentAccounts', 'clases', 'grupos', 'cuentas'))
            ->title('Plan de Cuentas');
    }
}
