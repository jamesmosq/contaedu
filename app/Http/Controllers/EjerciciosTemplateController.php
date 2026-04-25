<?php

namespace App\Http\Controllers;

use App\Exports\EjerciciosPlantillaExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EjerciciosTemplateController extends Controller
{
    public function __invoke(): BinaryFileResponse
    {
        return Excel::download(new EjerciciosPlantillaExport, 'plantilla-ejercicios.xlsx');
    }
}
