<?php

namespace App\Imports;

use App\Models\Central\Exercise;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EjerciciosImport implements ToCollection, WithHeadingRow
{
    private const TIPOS_VALIDOS = [
        'factura_venta',
        'factura_compra',
        'asiento_manual',
        'registro_tercero',
        'registro_producto',
        'pago_proveedor',
    ];

    private const TIPO_ALIASES = [
        'asiento_contable' => 'asiento_manual',
    ];

    public int $imported = 0;

    /** @var array<int, array{fila: int, error: string}> */
    public array $errors = [];

    public function __construct(
        private readonly ?int $teacherId,
        private readonly bool $isGlobal,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $fila = $index + 2;

            $titulo = trim((string) ($row['titulo'] ?? ''));
            $tipo = trim((string) ($row['tipo'] ?? ''));
            $tipo = self::TIPO_ALIASES[$tipo] ?? $tipo;
            $puntos = (int) ($row['puntos'] ?? 10);

            if ($titulo === '') {
                $this->errors[] = ['fila' => $fila, 'error' => 'El título es obligatorio.'];

                continue;
            }

            if (! in_array($tipo, self::TIPOS_VALIDOS, true)) {
                $this->errors[] = ['fila' => $fila, 'error' => "Tipo «{$tipo}» no válido."];

                continue;
            }

            if ($puntos < 1 || $puntos > 100) {
                $this->errors[] = ['fila' => $fila, 'error' => "Puntos debe estar entre 1 y 100 (valor: {$puntos})."];

                continue;
            }

            $montoRaw = $row['monto_minimo'] ?? null;
            $monto = $montoRaw !== null && $montoRaw !== '' ? (float) $montoRaw : null;

            if ($monto !== null && $monto < 0) {
                $this->errors[] = ['fila' => $fila, 'error' => 'El monto mínimo no puede ser negativo.'];

                continue;
            }

            Exercise::create([
                'teacher_id' => $this->teacherId,
                'title' => $titulo,
                'instructions' => trim((string) ($row['instrucciones'] ?? '')) ?: null,
                'type' => $tipo,
                'monto_minimo' => $monto,
                'cuenta_puc_requerida' => trim((string) ($row['cuenta_puc_requerida'] ?? '')) ?: null,
                'puntos' => $puntos,
                'active' => true,
                'is_global' => $this->isGlobal,
            ]);

            $this->imported++;
        }
    }
}
