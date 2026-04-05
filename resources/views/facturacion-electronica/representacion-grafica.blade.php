<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero_completo }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .page { max-width: 800px; margin: 20px auto; padding: 30px; }
        .header { border-bottom: 2px solid #1e40af; padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; }
        .empresa-name { font-size: 18px; font-weight: bold; color: #1e40af; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 16px; font-weight: bold; color: #1e40af; }
        .doc-title .numero { font-size: 20px; font-weight: bold; color: #1e293b; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-validada { background: #dcfce7; color: #166534; }
        .badge-borrador { background: #f1f5f9; color: #64748b; }
        .badge-rechazada { background: #fee2e2; color: #991b1b; }
        .badge-anulada { background: #f1f5f9; color: #374151; }
        .partes { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 16px 0; }
        .parte { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .parte h3 { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 6px; letter-spacing: 0.05em; }
        .parte .nombre { font-weight: bold; font-size: 13px; color: #1e293b; }
        .parte .detalle { color: #475569; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        thead th { background: #1e40af; color: white; padding: 8px 10px; text-align: left; font-size: 11px; }
        thead th.right { text-align: right; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        tbody td.right { text-align: right; }
        tfoot td { padding: 7px 10px; font-weight: bold; }
        tfoot tr:last-child td { font-size: 14px; color: #1e40af; border-top: 2px solid #1e40af; }
        .cufe-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 10px; margin-top: 16px; }
        .cufe-box .label { font-size: 10px; font-weight: bold; color: #0369a1; margin-bottom: 4px; }
        .cufe-box .valor { font-family: monospace; font-size: 10px; color: #1e293b; word-break: break-all; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        @page { margin: 18mm 20mm; }
        @media print { .no-print { display: none !important; } body { margin: 0; } .page { margin: 0; padding: 0; } }
    </style>
</head>
<body>
    <div class="page">

        <div class="no-print" style="text-align:right; margin-bottom: 12px;">
            <button onclick="window.print()" style="padding: 8px 20px; background: #1e40af; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
                Imprimir / Guardar PDF
            </button>
        </div>

        <div class="header">
            <div>
                <div class="empresa-name">{{ $factura->razon_social_emisor }}</div>
                <div style="color: #475569; margin-top: 4px;">NIT: {{ $factura->nit_emisor }}-{{ $factura->dv_emisor }}</div>
                <div style="margin-top: 6px;">
                    <span class="badge badge-{{ $factura->estado->value }}">{{ $factura->estado->label() }}</span>
                </div>
            </div>
            <div class="doc-title">
                <h1>FACTURA DE VENTA ELECTRÓNICA</h1>
                <div class="numero">{{ $factura->numero_completo }}</div>
                <div style="color: #475569; margin-top: 4px;">
                    Fecha: {{ $factura->fecha_emision->format('d/m/Y') }}<br>
                    Hora: {{ $factura->hora_emision->format('H:i:s') }}
                </div>
                <div style="margin-top: 4px; font-size: 11px; color: #64748b;">
                    Resolución: {{ $factura->resolucion->numero_resolucion }}
                </div>
            </div>
        </div>

        <div class="partes">
            <div class="parte">
                <h3>Emisor</h3>
                <div class="nombre">{{ $factura->razon_social_emisor }}</div>
                <div class="detalle">NIT: {{ $factura->nit_emisor }}-{{ $factura->dv_emisor }}</div>
                <div class="detalle">Ambiente: Pruebas (02)</div>
            </div>
            <div class="parte">
                <h3>Adquirente</h3>
                <div class="nombre">{{ $factura->nombre_adquirente }}</div>
                <div class="detalle">Doc. {{ $factura->tipo_doc_adquirente }}: {{ $factura->num_doc_adquirente }}</div>
                @if($factura->email_adquirente)
                    <div class="detalle">{{ $factura->email_adquirente }}</div>
                @endif
                @if($factura->direccion_adquirente)
                    <div class="detalle">{{ $factura->direccion_adquirente }}</div>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descripción</th>
                    <th class="right">Cantidad</th>
                    <th class="right">Precio unit.</th>
                    <th class="right">%IVA</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->detalles as $d)
                <tr>
                    <td>{{ $d->orden }}</td>
                    <td>{{ $d->descripcion }}</td>
                    <td class="right">{{ number_format((float)$d->cantidad, 2, ',', '.') }}</td>
                    <td class="right">${{ number_format((float)$d->precio_unitario, 0, ',', '.') }}</td>
                    <td class="right">{{ $d->porcentaje_iva }}%</td>
                    <td class="right">${{ number_format((float)$d->total_linea, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:normal; color:#475569;">Subtotal:</td>
                    <td style="text-align:right;">${{ number_format((float)$factura->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:normal; color:#475569;">IVA:</td>
                    <td style="text-align:right;">${{ number_format((float)$factura->valor_iva, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right;">TOTAL A PAGAR:</td>
                    <td style="text-align:right;">${{ number_format((float)$factura->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if($factura->cufe)
        <div class="cufe-box">
            <div class="label">CUFE — Código Único de Factura Electrónica (SHA-384)</div>
            <div class="valor">{{ $factura->cufe }}</div>
            @if($factura->qr_data)
            <div style="margin-top: 6px; font-size: 10px; color: #64748b;">Verificar en: {{ $factura->qr_data }}</div>
            @endif
        </div>
        @endif

        @if($factura->fecha_validacion_dian)
        <div style="margin-top: 10px; font-size: 11px; color: #64748b;">
            Validado por simulador DIAN: {{ $factura->fecha_validacion_dian->format('d/m/Y H:i:s') }}
            | Código respuesta: {{ $factura->codigo_respuesta_dian }}
        </div>
        @endif

        <div class="footer">
            Factura electrónica generada por ContaEdu — Sistema educativo de contabilidad | Ambiente de Pruebas<br>
            Este documento es para fines educativos. No tiene validez comercial ni legal.
        </div>

    </div>
</body>
</html>
