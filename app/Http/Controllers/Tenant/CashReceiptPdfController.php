<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\CompanyConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CashReceiptPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $receipt = CashReceipt::with(['third', 'items.invoice', 'items.feFactura'])
            ->findOrFail($request->get('id'));

        $config = CompanyConfig::first();

        $pdf = Pdf::loadView('pdf.recibo', compact('receipt', 'config'))
            ->setPaper('letter', 'portrait')
            ->setOption(['margin_top' => 18, 'margin_right' => 20, 'margin_bottom' => 18, 'margin_left' => 20, 'isHtml5ParserEnabled' => true]);

        $nombre = 'RC-'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT).'-'.$receipt->date->format('Y-m-d').'.pdf';

        return $pdf->stream($nombre);
    }
}
