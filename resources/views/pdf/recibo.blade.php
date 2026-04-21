<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .page { max-width: 800px; margin: 20px auto; padding: 30px; }

        .header { border-bottom: 2px solid #10472a; padding-bottom: 14px; margin-bottom: 20px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; width: 60%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .empresa-name { font-size: 17px; font-weight: bold; color: #10472a; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        .doc-title { font-size: 14px; font-weight: bold; color: #10472a; text-transform: uppercase; letter-spacing: 1px; }
        .doc-ref { font-size: 20px; font-weight: bold; color: #1e293b; margin: 4px 0; }
        .meta-table { margin: 6px 0 0 auto; border-collapse: collapse; border: 1px solid #d4f0e1; }
        .meta-table th { background: #edf8f2; padding: 3px 10px; font-size: 9px; color: #10472a; font-weight: 600; text-align: left; }
        .meta-table td { padding: 3px 10px; font-size: 10px; }

        .section-title { font-size: 9px; font-weight: bold; color: #10472a; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
        .info-box { border: 1px solid #d4f0e1; background: #f8fffe; border-radius: 4px; padding: 12px 14px; margin-bottom: 16px; }
        .info-row { display: table; width: 100%; margin-bottom: 4px; }
        .info-label { display: table-cell; font-size: 9.5px; color: #64748b; width: 30%; }
        .info-value { display: table-cell; font-size: 10px; font-weight: 600; color: #1e293b; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .items-table thead tr { background: #10472a; }
        .items-table thead th { padding: 6px 10px; font-size: 9px; color: #d4f0e1; font-weight: 600; text-align: left; text-transform: uppercase; }
        .items-table thead th.right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #e8f5ee; }
        .items-table tbody tr:last-child { border-bottom: none; }
        .items-table tbody td { padding: 7px 10px; font-size: 10px; color: #334155; }
        .items-table tbody td.right { text-align: right; font-weight: 600; }
        .items-table tfoot tr { background: #edf8f2; border-top: 2px solid #10472a; }
        .items-table tfoot td { padding: 7px 10px; font-size: 11px; font-weight: bold; color: #10472a; }
        .items-table tfoot td.right { text-align: right; }

        .notes-box { border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 12px; margin-bottom: 20px; font-size: 10px; color: #475569; }

        .firma-section { margin-top: 40px; display: table; width: 100%; }
        .firma-col { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
        .firma-line { border-top: 1px solid #334155; width: 180px; margin: 0 auto 6px; }
        .firma-label { font-size: 9px; color: #64748b; }

        .footer { margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 8px; color: #94a3b8; text-align: center; }
        .badge-aplicado { display: inline-block; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
<div class="page">

    {{-- ENCABEZADO --}}
    <div class="header">
        <div class="header-left">
            <div class="empresa-name">{{ $config?->company_name ?? 'Mi Empresa' }}</div>
            @if($config?->nit)
                <div class="subtitle">NIT {{ $config->nit }}{{ $config->dv ? '-'.$config->dv : '' }}</div>
            @endif
            @if($config?->address)
                <div class="subtitle">{{ $config->address }}{{ $config->city ? ', '.$config->city : '' }}</div>
            @endif
            <div class="subtitle" style="margin-top:8px; font-size:9px; color:#94a3b8;">ContaEdu — Sistema de Formación Contable</div>
        </div>
        <div class="header-right">
            <div class="doc-title">Recibo de Caja</div>
            <div class="doc-ref">RC-{{ str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div style="margin-top:4px;">
                <span class="badge-aplicado">{{ $receipt->status->label() }}</span>
            </div>
            <table class="meta-table" style="margin-top:8px;">
                <tr><th>Fecha</th><td>{{ $receipt->date->format('d/m/Y') }}</td></tr>
            </table>
        </div>
    </div>

    {{-- DATOS DEL CLIENTE --}}
    <div class="section-title">Recibido de</div>
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nombre / Razón social</span>
            <span class="info-value">{{ $receipt->third?->name ?? '—' }}</span>
        </div>
        @if($receipt->third?->document_number)
        <div class="info-row">
            <span class="info-label">Documento</span>
            <span class="info-value">{{ $receipt->third->document_type ?? '' }} {{ $receipt->third->document_number }}</span>
        </div>
        @endif
    </div>

    {{-- DETALLE DE FACTURAS CANCELADAS --}}
    <div class="section-title">Aplicado a</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Documento</th>
                <th>Descripción</th>
                <th class="right">Monto aplicado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $item)
                <tr>
                    <td style="font-family:monospace; font-weight:bold;">
                        @if($item->invoice)
                            {{ $item->invoice->fullReference() }}
                        @elseif($item->feFactura)
                            {{ $item->feFactura->numero_completo ?? 'FE-'.$item->feFactura->id }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($item->invoice)
                            Factura de venta
                        @elseif($item->feFactura)
                            Factura electrónica DIAN
                        @else
                            —
                        @endif
                    </td>
                    <td class="right">$ {{ number_format($item->amount_applied, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total recibido</td>
                <td class="right">$ {{ number_format($receipt->total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- NOTAS --}}
    @if($receipt->notes)
        <div class="section-title">Notas</div>
        <div class="notes-box">{{ $receipt->notes }}</div>
    @endif

    {{-- FIRMAS --}}
    <div class="firma-section">
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Firma quien recibe</div>
            <div class="firma-label" style="margin-top:2px;">{{ $config?->company_name ?? '' }}</div>
        </div>
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Firma quien paga</div>
            <div class="firma-label" style="margin-top:2px;">{{ $receipt->third?->name ?? '' }}</div>
        </div>
    </div>

    <div class="footer">
        RC-{{ str_pad($receipt->id, 5, '0', STR_PAD_LEFT) }} — Generado el {{ now()->format('d/m/Y H:i') }} — ContaEdu Sistema de Formación Contable
    </div>

</div>
</body>
</html>
