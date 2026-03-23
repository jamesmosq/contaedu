<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CompanyConfig;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPdfController extends Controller
{
    public function __invoke(Request $request, ReportService $service)
    {
        $report = $request->get('report', 'cartera');
        $dateFrom = $request->get('date_from', now()->startOfYear()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $accountId = $request->get('account_id');
        $config = CompanyConfig::first();

        $data = match ($report) {
            'diario' => $service->libroDiario($dateFrom, $dateTo),
            'mayor' => $accountId ? $service->libroMayor((int) $accountId, $dateFrom, $dateTo) : collect(),
            'comprobacion' => $service->balanceComprobacion($dateFrom, $dateTo),
            'resultados' => $service->estadoResultados($dateFrom, $dateTo),
            'balance' => $service->balanceGeneral($dateTo),
            'cartera' => $service->carteraPorCobrar(),
            'cxp' => $service->cuentasPorPagar(),
            'iva' => $service->libroIva($dateFrom, $dateTo),
            default => collect(),
        };

        $titles = [
            'diario' => 'Libro Diario',
            'mayor' => 'Libro Mayor',
            'comprobacion' => 'Balance de Comprobación',
            'resultados' => 'Estado de Resultados',
            'balance' => 'Balance General',
            'cartera' => 'Cartera por Cobrar',
            'cxp' => 'Cuentas por Pagar',
            'iva' => 'Libro Auxiliar de IVA',
        ];

        $pdf = Pdf::loadView('pdf.reporte', [
            'report' => $report,
            'data' => $data,
            'config' => $config,
            'title' => $titles[$report] ?? 'Reporte',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream(($titles[$report] ?? 'Reporte').'.pdf');
    }
}
