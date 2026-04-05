<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CompanyConfig;
use App\Models\Tenant\FixedAsset;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ActivosFijosPdfController extends Controller
{
    public function __invoke(): Response
    {
        $assets = FixedAsset::orderBy('acquisition_date')->get();
        $config = CompanyConfig::first();

        $pdf = Pdf::loadView('pdf.activos-fijos', compact('assets', 'config'))
            ->setPaper('letter', 'landscape')
            ->setOption(['margin_top' => 15, 'margin_right' => 18, 'margin_bottom' => 15, 'margin_left' => 18]);

        return $pdf->stream('Activos-Fijos.pdf');
    }
}
