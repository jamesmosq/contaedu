<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BankDocument;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\CompanyConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BancoPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $id       = $request->get('id');
        $document = BankDocument::with('bankAccount')->findOrFail($id);
        $cuenta   = $document->bankAccount;
        $config   = CompanyConfig::first();

        // Cargar movimientos del período actual para el extracto
        $movimientos = collect();
        if ($document->tipo === 'extracto') {
            $movimientos = BankTransaction::where('bank_account_id', $cuenta->id)
                ->whereYear('fecha_transaccion', $document->generado_at->year)
                ->whereMonth('fecha_transaccion', $document->generado_at->month)
                ->orderBy('fecha_transaccion')
                ->orderBy('id')
                ->get();
        }

        $view = 'pdf.banco.' . $document->tipo;

        $pdf = Pdf::loadView($view, compact('document', 'cuenta', 'config', 'movimientos'))
            ->setPaper('letter', 'portrait')
            ->setOption(['margin_top' => 18, 'margin_right' => 20, 'margin_bottom' => 18, 'margin_left' => 20]);

        $nombreArchivo = match ($document->tipo) {
            'extracto'    => 'Extracto-' . $cuenta->account_number . '-' . $document->generado_at->format('Y-m') . '.pdf',
            'certificado' => 'Certificado-Bancario-' . $document->generado_at->format('Y-m-d') . '.pdf',
            'referencia'  => 'Referencia-Bancaria-' . $document->generado_at->format('Y-m-d') . '.pdf',
            'paz_y_salvo' => 'Paz-y-Salvo-' . $document->generado_at->format('Y-m-d') . '.pdf',
            default       => 'Documento-Bancario.pdf',
        };

        return $pdf->stream($nombreArchivo);
    }
}
