<?php

namespace App\Exports;

use App\Models\Central\StudentScore;
use App\Models\Central\Tenant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CalificacionesInstitutionExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly Collection $tenants,
        private readonly string $institutionName,
    ) {}

    public function title(): string
    {
        return 'Calificaciones';
    }

    public function headings(): array
    {
        return ['Cédula', 'Estudiante', 'Empresa', 'Grupo', 'Maestros', 'Facturación', 'Compras', 'Cierre', 'Promedio'];
    }

    public function collection(): Collection
    {
        $modules = ['maestros', 'facturacion', 'compras', 'cierre'];

        $scores = StudentScore::whereIn('tenant_id', $this->tenants->pluck('id'))
            ->current()
            ->get()
            ->groupBy('tenant_id')
            ->map(fn ($rows) => $rows->keyBy('module'));

        return $this->tenants->map(function (Tenant $tenant) use ($scores, $modules) {
            $tenantScores = $scores->get($tenant->id, collect());
            $values = [];

            foreach ($modules as $mod) {
                $values[] = $tenantScores->has($mod)
                    ? number_format((float) $tenantScores[$mod]->score, 1, '.', '')
                    : '—';
            }

            $filled = array_filter($values, fn ($v) => $v !== '—');
            $promedio = count($filled) > 0
                ? number_format(array_sum($filled) / count($filled), 1, '.', '')
                : '—';

            return [
                $tenant->id,
                $tenant->student_name,
                $tenant->company_name,
                $tenant->group?->name ?? '—',
                ...$values,
                $promedio,
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10472a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
