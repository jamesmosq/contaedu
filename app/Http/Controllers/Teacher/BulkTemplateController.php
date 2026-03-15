<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkTemplateController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_estudiantes.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel abra con tildes correctas
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['cedula', 'nombre_estudiante', 'nombre_empresa', 'nit_empresa', 'password']);

            // Filas de ejemplo
            fputcsv($handle, ['1023456001', 'Ana García', 'Comercial García SAS', '900100001-1', 'clave123']);
            fputcsv($handle, ['1023456002', 'Luis Pérez', 'Distribuciones Pérez', '900100002-2', 'clave456']);

            fclose($handle);
        }, 200, $headers);
    }
}
