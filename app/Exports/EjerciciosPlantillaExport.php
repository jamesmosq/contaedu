<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EjerciciosPlantillaExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'titulo',
            'instrucciones',
            'tipo',
            'monto_minimo',
            'cuenta_puc_requerida',
            'puntos',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Registrar venta de mercancía',
                'El estudiante debe registrar una factura de venta por $500.000 a un cliente de contado.',
                'factura_venta',
                '500000',
                '4135',
                '10',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10472a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0fdf4']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Dropdown de validación para columna C (tipo) — filas 2 a 200
                $tipos = implode(',', [
                    'factura_venta',
                    'factura_compra',
                    'asiento_manual',
                    'registro_tercero',
                    'registro_producto',
                    'pago_proveedor',
                ]);

                foreach (range(2, 200) as $row) {
                    $validation = $sheet->getCell("C{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1("\"{$tipos}\"");
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Tipo inválido');
                    $validation->setError('Usa uno de los valores del desplegable.');
                }

                // Nota en fila 1 sobre tipos válidos
                $sheet->getComment('C1')->getText()->createTextRun(
                    "Tipos válidos:\n- factura_venta\n- factura_compra\n- asiento_manual\n- registro_tercero\n- registro_producto\n- pago_proveedor"
                );

                // Ancho personalizado para instrucciones
                $sheet->getColumnDimension('B')->setWidth(50);
                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(22);

                // Fila de encabezado más alta
                $sheet->getRowDimension(1)->setRowHeight(20);
            },
        ];
    }
}
