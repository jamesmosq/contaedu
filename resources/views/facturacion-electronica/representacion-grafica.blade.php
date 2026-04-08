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

        /* Cabecera */
        .header { border-bottom: 2px solid #10472a; padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; }
        .empresa-name { font-size: 18px; font-weight: bold; color: #10472a; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 14px; font-weight: bold; color: #165e36; }
        .doc-title .numero { font-size: 20px; font-weight: bold; color: #1e293b; margin: 4px 0; }
        .doc-title .fechas { font-size: 11px; color: #475569; }
        .doc-title .fechas table { margin: 0; border: 1px solid #d4f0e1; border-radius: 4px; width: auto; }
        .doc-title .fechas th { background: #edf8f2; padding: 3px 8px; text-align: left; font-size: 10px; color: #10472a; font-weight: 600; }
        .doc-title .fechas td { padding: 3px 8px; font-size: 11px; color: #1e293b; font-weight: normal; border-bottom: none; }

        /* Badge estado */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .badge-validada  { background: #dcfce7; color: #166534; }
        .badge-borrador  { background: #f1f5f9; color: #64748b; }
        .badge-rechazada { background: #fee2e2; color: #991b1b; }
        .badge-anulada   { background: #f1f5f9; color: #374151; }

        /* Partes */
        .partes { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 14px 0; }
        .parte { background: #edf8f2; border: 1px solid #d4f0e1; border-radius: 6px; padding: 10px 12px; }
        .parte h3 { font-size: 10px; text-transform: uppercase; color: #10472a; font-weight: 600; margin-bottom: 5px; letter-spacing: 0.05em; }
        .parte .nombre { font-weight: bold; font-size: 13px; color: #1e293b; }
        .parte .detalle { color: #475569; margin-top: 2px; font-size: 11px; }

        /* Tabla ítems */
        table { width: 100%; border-collapse: collapse; margin: 14px 0; }
        thead th { background: #165e36; color: white; padding: 7px 10px; text-align: left; font-size: 11px; font-weight: 600; }
        thead th.right { text-align: right; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #edf8f2; font-size: 11px; }
        tbody td.right { text-align: right; }
        tfoot td { padding: 6px 10px; font-weight: bold; font-size: 12px; }
        tfoot tr.total-row td { font-size: 14px; color: #10472a; border-top: 2px solid #10472a; }

        /* Valor en letras */
        .letras-box { background: #edf8f2; border: 1px solid #d4f0e1; border-radius: 6px; padding: 8px 12px; margin-bottom: 10px; }
        .letras-box .label { font-size: 10px; font-weight: bold; color: #10472a; margin-bottom: 2px; text-transform: uppercase; }
        .letras-box .valor { font-size: 12px; color: #1e293b; font-style: italic; }

        /* Condiciones de pago */
        .condiciones { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .condicion-bloque { background: #edf8f2; border: 1px solid #d4f0e1; border-radius: 6px; padding: 8px 12px; }
        .condicion-bloque .label { font-size: 10px; font-weight: bold; color: #10472a; margin-bottom: 2px; text-transform: uppercase; }
        .condicion-bloque .valor { font-size: 11px; color: #1e293b; }

        /* Notas */
        .notas-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; }
        .notas-box .label { font-size: 10px; font-weight: bold; color: #92400e; margin-bottom: 2px; text-transform: uppercase; }
        .notas-box .valor { font-size: 11px; color: #78350f; }

        /* Footer */
        .footer { margin-top: 16px; border-top: 1px solid #d4f0e1; padding-top: 10px; font-size: 10px; color: #475569; line-height: 1.6; }
        .footer .resolucion-text { margin-bottom: 6px; color: #1e293b; }
        .footer .dian-text { margin-bottom: 6px; color: #475569; }
        .footer .cufe-label { font-weight: bold; color: #10472a; }
        .footer .cufe-valor { font-family: monospace; font-size: 9px; word-break: break-all; color: #475569; }
        .footer .qr-url { font-size: 9px; color: #165e36; margin-top: 4px; word-break: break-all; }
        .footer .disclaimer { text-align: center; color: #94a3b8; font-size: 9px; margin-top: 8px; border-top: 1px solid #d4f0e1; padding-top: 6px; }

        @page { margin: 18mm 20mm; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .page { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="page">

        {{-- Botón imprimir (solo pantalla) --}}
        <div class="no-print" style="text-align:right; margin-bottom: 12px;">
            <button onclick="window.print()" style="padding: 8px 20px; background: #10472a; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
                Imprimir / Guardar PDF
            </button>
        </div>

        {{-- Cabecera --}}
        <div class="header">
            <div>
                <div class="empresa-name">{{ $factura->razon_social_emisor }}</div>
                <div style="color:#475569; margin-top:4px; font-size:11px;">NIT: {{ $factura->nit_emisor }}-{{ $factura->dv_emisor }}</div>
                <div style="margin-top:6px;">
                    <span class="badge badge-{{ $factura->estado->value }}">{{ $factura->estado->label() }}</span>
                </div>
            </div>
            <div class="doc-title">
                <h1>FACTURA DE VENTA ELECTRÓNICA</h1>
                <div class="numero">{{ $factura->numero_completo }}</div>
                <div class="fechas">
                    <table>
                        <tr>
                            <th>Generación</th>
                            <td>{{ $factura->fecha_emision->format('d/m/Y') }}, {{ $factura->hora_emision->format('H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Expedición</th>
                            <td>{{ $factura->fecha_emision->format('d/m/Y') }}, {{ $factura->hora_emision->format('H:i') }}</td>
                        </tr>
                        @if($factura->fecha_vencimiento_pago)
                        <tr>
                            <th>Vencimiento</th>
                            <td>{{ $factura->fecha_vencimiento_pago->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Emisor / Adquirente --}}
        <div class="partes">
            <div class="parte">
                <h3>Emisor</h3>
                <div class="nombre">{{ $factura->razon_social_emisor }}</div>
                <div class="detalle">NIT: {{ $factura->nit_emisor }}-{{ $factura->dv_emisor }}</div>
                <div class="detalle">Responsable de IVA</div>
                <div class="detalle" style="color:#94a3b8; font-size:10px;">Ambiente: Pruebas (02)</div>
            </div>
            <div class="parte">
                <h3>Adquirente</h3>
                <div class="nombre">{{ $factura->nombre_adquirente }}</div>
                <div class="detalle">{{ $factura->tipo_doc_adquirente }}: {{ $factura->num_doc_adquirente }}</div>
                @if($factura->telefono_adquirente)
                    <div class="detalle">Tel: {{ $factura->telefono_adquirente }}</div>
                @endif
                @if($factura->direccion_adquirente)
                    <div class="detalle">Dir: {{ $factura->direccion_adquirente }}{{ $factura->municipio_adquirente ? ' — ' . $factura->municipio_adquirente : '' }}</div>
                @endif
                @if($factura->email_adquirente)
                    <div class="detalle" style="font-size:10px;">{{ $factura->email_adquirente }}</div>
                @endif
            </div>
        </div>

        {{-- Tabla de ítems --}}
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
                    <td class="right">{{ number_format((float) $d->cantidad, 2, ',', '.') }}</td>
                    <td class="right">${{ number_format((float) $d->precio_unitario, 0, ',', '.') }}</td>
                    <td class="right">{{ $d->porcentaje_iva }}%</td>
                    <td class="right">${{ number_format((float) $d->total_linea, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:normal; color:#475569;">Subtotal:</td>
                    <td style="text-align:right;">${{ number_format((float) $factura->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if((float) $factura->total_descuentos > 0)
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:normal; color:#dc2626;">Descuentos:</td>
                    <td style="text-align:right; color:#dc2626;">-${{ number_format((float) $factura->total_descuentos, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="5" style="text-align:right; font-weight:normal; color:#475569;">IVA:</td>
                    <td style="text-align:right;">${{ number_format((float) $factura->valor_iva, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">TOTAL A PAGAR:</td>
                    <td style="text-align:right;">${{ number_format((float) $factura->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Valor en letras --}}
        <div class="letras-box">
            <div class="label">Valor en letras:</div>
            <div class="valor">{{ numero_a_letras((float) $factura->total) }}</div>
        </div>

        {{-- Condiciones de pago --}}
        @if($factura->medio_pago || $factura->forma_pago)
        <div class="condiciones">
            @if($factura->medio_pago)
            <div class="condicion-bloque">
                <div class="label">Medio de pago</div>
                <div class="valor">{{ $factura->medioPagoLabel() }}</div>
            </div>
            @endif
            @if($factura->forma_pago)
            <div class="condicion-bloque">
                <div class="label">Forma de pago</div>
                <div class="valor">
                    {{ $factura->formaPagoLabel() }}
                    @if($factura->fecha_vencimiento_pago)
                        — Vence: {{ $factura->fecha_vencimiento_pago->format('d/m/Y') }}
                    @endif
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Notas / Observaciones --}}
        @if($factura->notas)
        <div class="notas-box">
            <div class="label">Observaciones:</div>
            <div class="valor">{{ $factura->notas }}</div>
        </div>
        @endif

        {{-- Footer: resolución + validación DIAN + CUFE + disclaimer --}}
        <div class="footer">
            <div class="resolucion-text">
                <strong>Número Autorización {{ $factura->resolucion->numero_resolucion }}</strong>
                aprobado el {{ $factura->resolucion->fecha_desde->format('d/m/Y') }},
                prefijo <strong>{{ $factura->resolucion->prefijo ?: 'Sin prefijo' }}</strong>,
                desde el número <strong>{{ number_format($factura->resolucion->numero_desde, 0, ',', '.') }}</strong>
                al <strong>{{ number_format($factura->resolucion->numero_hasta, 0, ',', '.') }}</strong>.
                Vigencia: {{ $factura->resolucion->fecha_desde->format('d/m/Y') }} al {{ $factura->resolucion->fecha_hasta->format('d/m/Y') }}.
            </div>

            @if($factura->fecha_validacion_dian)
            <div class="dian-text">
                Validado por simulador DIAN: {{ $factura->fecha_validacion_dian->format('d/m/Y H:i:s') }}
                | Código: {{ $factura->codigo_respuesta_dian }}
            </div>
            @endif

            @if($factura->cufe)
            <div>
                <span class="cufe-label">CUFE: </span>
                <span class="cufe-valor">{{ $factura->cufe }}</span>
            </div>
            @endif

            @if($factura->qr_data)
            <div class="qr-url">Verificar en DIAN: {{ $factura->qr_data }}</div>
            @endif

            <div class="disclaimer">
                Factura electrónica generada por ContaEdu — Sistema educativo de contabilidad | Ambiente de Pruebas<br>
                Este documento es para fines educativos. No tiene validez comercial ni legal.
            </div>
        </div>

    </div>
</body>
</html>
