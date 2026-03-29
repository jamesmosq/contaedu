<?php

namespace App\Livewire\Tenant\Calendario;

use App\Models\Tenant\CompanyConfig;
use App\Services\CalendarioTributarioService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]
#[Title('Calendario tributario')]
class Index extends Component
{
    public int $year;

    public string $filtro = 'todos'; // todos | proximo | vencido

    public Collection $eventos;

    public int $diaLimite = 22;

    public string $regimen = 'simplificado';

    public function mount(CalendarioTributarioService $service): void
    {
        $this->year = now()->year;
        $this->eventos = collect();
        $this->cargar($service);
    }

    public function cargar(CalendarioTributarioService $service): void
    {
        $config = CompanyConfig::first();
        $nit = $config?->nit ?? '0';
        $this->regimen = $config?->regimen ?? 'simplificado';
        $this->diaLimite = $service->diaLimite($nit);
        $this->eventos = $service->generar($this->year, $nit, $this->regimen);
    }

    public function updatedYear(CalendarioTributarioService $service): void
    {
        $this->cargar($service);
    }

    public function eventosFiltrados(): Collection
    {
        if ($this->filtro === 'todos') {
            return $this->eventos;
        }

        return $this->eventos->filter(fn ($e) => $e['estado'] === $this->filtro)->values();
    }

    public function render(): mixed
    {
        return view('livewire.tenant.calendario.index', [
            'eventos' => $this->eventosFiltrados(),
            'proximos' => $this->eventos->where('estado', 'proximo')->count(),
            'vencidos' => $this->eventos->where('estado', 'vencido')->where('aplica', true)->count(),
        ])->title('Calendario Tributario');
    }
}
