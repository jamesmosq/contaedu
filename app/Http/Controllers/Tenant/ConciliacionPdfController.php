<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BankReconciliation;
use App\Models\Tenant\CompanyConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConciliacionPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $id = $request->get('id');
        $reconciliation = BankReconciliation::with([
            'account',
            'items' => fn ($q) => $q->orderBy('date'),
        ])->findOrFail($id);

        $config = CompanyConfig::first();

        $pdf = Pdf::loadView('pdf.conciliacion', compact('reconciliation', 'config'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('Conciliacion-Bancaria-'.$reconciliation->period_start->format('Y-m').'.pdf');
    }
}
