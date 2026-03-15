<?php

namespace App\Livewire\Teacher;

use App\Models\Central\StudentScore;
use App\Models\Central\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Rubrica extends Component
{
    public string $tenantId = '';

    // Scores por módulo: clave → score (string para input)
    public array $scores = [
        'maestros'     => '',
        'facturacion'  => '',
        'compras'      => '',
        'cierre'       => '',
    ];
    public array $notes = [
        'maestros'     => '',
        'facturacion'  => '',
        'compras'      => '',
        'cierre'       => '',
    ];

    public static array $modules = [
        'maestros'    => 'Maestros contables',
        'facturacion' => 'Facturación y cobro',
        'compras'     => 'Compras y pagos',
        'cierre'      => 'Cierre contable',
    ];

    public function mount(string $tenantId): void
    {
        $this->tenantId = $tenantId;

        // Cargar notas existentes
        $existing = StudentScore::where('tenant_id', $tenantId)->get()->keyBy('module');
        foreach (array_keys(self::$modules) as $mod) {
            if (isset($existing[$mod])) {
                $this->scores[$mod] = (string) $existing[$mod]->score;
                $this->notes[$mod]  = $existing[$mod]->notes ?? '';
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'scores.maestros'    => ['nullable', 'numeric', 'min:1', 'max:5'],
            'scores.facturacion' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'scores.compras'     => ['nullable', 'numeric', 'min:1', 'max:5'],
            'scores.cierre'      => ['nullable', 'numeric', 'min:1', 'max:5'],
        ], [
            'scores.*.numeric' => 'La nota debe ser un número.',
            'scores.*.min'     => 'La nota mínima es 1.0.',
            'scores.*.max'     => 'La nota máxima es 5.0.',
        ]);

        $gradedBy = auth()->id();

        foreach (array_keys(self::$modules) as $mod) {
            if ($this->scores[$mod] !== '') {
                StudentScore::updateOrCreate(
                    ['tenant_id' => $this->tenantId, 'module' => $mod],
                    [
                        'score'     => (float) $this->scores[$mod],
                        'notes'     => $this->notes[$mod] ?: null,
                        'graded_by' => $gradedBy,
                    ]
                );
            }
        }

        session()->flash('success', 'Notas guardadas correctamente.');
    }

    public function render(): mixed
    {
        $tenant = Tenant::findOrFail($this->tenantId);

        // Promedio ponderado (igual peso para todos los módulos con nota)
        $notasIngresadas = collect($this->scores)->filter(fn ($v) => $v !== '')->map(fn ($v) => (float) $v);
        $promedio        = $notasIngresadas->isNotEmpty() ? round($notasIngresadas->average(), 1) : null;

        return view('livewire.teacher.rubrica', [
            'tenant'   => $tenant,
            'modules'  => self::$modules,
            'promedio' => $promedio,
        ])->title('Rúbrica de calificación');
    }
}
