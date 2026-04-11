<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\Account;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('PUC Interactivo')]
class PucInteractivo extends Component
{
    public string $search = '';
    public ?int $selectedId = null;
    public ?Account $selectedAccount = null;
    public string $claseActiva = '';

    public function seleccionar(int $id): void
    {
        $account = Account::find($id);

        if ($account && ! $account->tieneContenidoAcademico()) {
            $conDinamica = $account->cuentaConDinamica();
            $account->descripcion    = $conDinamica->descripcion;
            $account->dinamica_debe  = $conDinamica->dinamica_debe;
            $account->dinamica_haber = $conDinamica->dinamica_haber;
            $account->ejemplo        = $conDinamica->ejemplo;
        }

        $this->selectedId      = $id;
        $this->selectedAccount = $account;
    }

    public function filtrarClase(string $clase): void
    {
        $this->claseActiva     = ($clase === $this->claseActiva) ? '' : $clase;
        $this->selectedAccount = null;
        $this->selectedId      = null;
    }

    public function render(): mixed
    {
        $query = Account::query()->orderBy('code');

        if ($this->search) {
            $query->where(fn ($q) =>
                $q->where('code', 'ilike', "%{$this->search}%")
                  ->orWhere('name', 'ilike', "%{$this->search}%")
            );
        } elseif ($this->claseActiva) {
            $query->where('code', 'like', $this->claseActiva . '%');
        }

        $accounts = $query->get();
        $clases   = Account::where('level', 1)->orderBy('code')->get();

        return view('livewire.tenant.puc.puc-interactivo', compact('accounts', 'clases'));
    }
}
