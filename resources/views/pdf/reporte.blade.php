<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 20mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }
        .page { padding: 28px 36px; }

        /* ── Cabecera ── */
        .header { border-bottom: 2px solid #10472a; padding-bottom: 14px; margin-bottom: 16px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; width: 55%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .empresa-name { font-size: 17px; font-weight: bold; color: #10472a; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: bold; color: #165e36; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px; }
        .period { font-size: 10px; color: #64748b; margin-top: 4px; }
        .period table { margin: 0 0 0 auto; border: 1px solid #d4f0e1; width: auto; }
        .period th { background: #edf8f2; padding: 3px 8px; text-align: left; font-size: 9px; color: #10472a; font-weight: 600; }
        .period td { padding: 3px 8px; font-size: 10px; color: #1e293b; font-weight: normal; border-bottom: none; }

        /* ── Tablas ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead th { background: #165e36; color: #ffffff; padding: 6px 9px; text-align: left; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        thead th.text-right { text-align: right; }
        tbody td { padding: 4px 9px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        tfoot td { padding: 5px 9px; font-weight: bold; background: #edf8f2; border-top: 2px solid #d4f0e1; font-size: 10px; }
        .text-right { text-align: right; }
        .mono { font-family: 'Courier New', monospace; }

        /* ── Sección título ── */
        .section-title { font-size: 10px; font-weight: bold; color: #10472a; padding: 5px 10px; background: #edf8f2; border-left: 3px solid #d4a017; margin-bottom: 8px; margin-top: 14px; }

        /* ── Asiento contable (libro diario) ── */
        .entry-header { background: #edf8f2; padding: 5px 9px; border-left: 3px solid #165e36; font-weight: bold; margin-bottom: 2px; font-size: 10px; color: #10472a; }

        /* ── Totales y resultados ── */
        .totals-row { font-weight: bold; background: #edf8f2; }
        .utilidad-box { border: 2px solid #165e36; background: #edf8f2; padding: 10px 12px; margin-top: 12px; text-align: right; font-size: 12px; color: #10472a; font-weight: bold; }
        .utilidad-box.perdida { border-color: #dc2626; background: #fef2f2; color: #991b1b; }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 8.5px; font-weight: bold; }
        .badge-auto { background: #edf8f2; color: #10472a; }

        /* ── Footer ── */
        .footer { margin-top: 20px; border-top: 1px solid #d4f0e1; padding-top: 8px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    {{-- Cabecera --}}
    <div class="header">
        <div class="header-left">
            <div class="empresa-name">{{ $config?->razon_social ?? config('app.name') }}</div>
            @if($config?->nit)
                <div class="subtitle">NIT: {{ $config->nit }}</div>
            @endif
            @if($config?->ciiu_code)
                <div class="subtitle">CIIU {{ $config->ciiu_code }} — {{ $config->ciiu_description }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="report-title">{{ $title }}</div>
            <div class="period">
                @if(!in_array($report, ['cartera','cxp','balance']))
                    <table>
                        <tr>
                            <th>Período</th>
                            <td>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Generado</th>
                            <td>{{ now()->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                @elseif($report === 'balance')
                    <table>
                        <tr>
                            <th>Al</th>
                            <td>{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Generado</th>
                            <td>{{ now()->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                @else
                    <table>
                        <tr>
                            <th>Generado</th>
                            <td>{{ now()->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Cartera / CxP ── --}}
    @if(in_array($report, ['cartera','cxp']))
        <table>
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>{{ $report === 'cartera' ? 'Cliente' : 'Proveedor' }}</th>
                    <th>Fecha</th>
                    <th>Vencimiento</th>
                    <th class="text-right">{{ $report === 'cartera' ? 'Total' : 'Saldo' }}</th>
                    <th class="text-right">Días vencida</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td class="mono">{{ $row['reference'] }}</td>
                        <td>{{ $report === 'cartera' ? $row['client'] : $row['supplier'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                        <td>{{ $row['due_date'] ? \Carbon\Carbon::parse($row['due_date'])->format('d/m/Y') : '—' }}</td>
                        <td class="text-right mono">$ {{ number_format($report==='cartera'?$row['total']:$row['balance'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ $row['vencida'] ? $row['dias_vencida'].' días' : 'Al día' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4"><strong>Total</strong></td>
                    <td class="text-right mono"><strong>$ {{ number_format($data->sum($report==='cartera'?'total':'balance'), 0, ',', '.') }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Libro Diario ── --}}
    @if($report === 'diario')
        @foreach($data as $entry)
            <div class="entry-header">
                {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }} &nbsp;|&nbsp;
                {{ $entry->reference }} &nbsp;|&nbsp;
                {{ $entry->description }}
                @if($entry->auto_generated) <span class="badge badge-auto">AUTO</span> @endif
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th>Descripción</th>
                        <th class="text-right">Débito</th>
                        <th class="text-right">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                        <tr>
                            <td class="mono">{{ $line->account->code }}</td>
                            <td>{{ $line->account->name }}</td>
                            <td>{{ $line->description }}</td>
                            <td class="text-right mono">{{ $line->debit > 0 ? '$ '.number_format($line->debit,0,',','.') : '' }}</td>
                            <td class="text-right mono">{{ $line->credit > 0 ? '$ '.number_format($line->credit,0,',','.') : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Totales:</strong></td>
                        <td class="text-right mono"><strong>$ {{ number_format($entry->lines->sum('debit'),0,',','.') }}</strong></td>
                        <td class="text-right mono"><strong>$ {{ number_format($entry->lines->sum('credit'),0,',','.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        @endforeach
    @endif

    {{-- ── Balance de Comprobación ── --}}
    @if($report === 'comprobacion')
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cuenta</th>
                    <th>Tipo</th>
                    <th class="text-right">Débitos</th>
                    <th class="text-right">Créditos</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td class="mono">{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ ucfirst($row['type']) }}</td>
                        <td class="text-right mono">$ {{ number_format($row['total_debit'],0,',','.') }}</td>
                        <td class="text-right mono">$ {{ number_format($row['total_credit'],0,',','.') }}</td>
                        <td class="text-right mono">$ {{ number_format($row['balance'],0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>TOTALES</strong></td>
                    <td class="text-right mono"><strong>$ {{ number_format($data->sum('total_debit'),0,',','.') }}</strong></td>
                    <td class="text-right mono"><strong>$ {{ number_format($data->sum('total_credit'),0,',','.') }}</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Estado de Resultados ── --}}
    @if($report === 'resultados')
        <div class="section-title">INGRESOS</div>
        <table>
            <tbody>
                @foreach($data['ingresos']['rows'] as $r)
                    <tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row"><td colspan="2"><strong>Total Ingresos</strong></td><td class="text-right mono"><strong>$ {{ number_format($data['ingresos']['total'],0,',','.') }}</strong></td></tr>
            </tfoot>
        </table>
        <div class="section-title">COSTOS DE VENTA</div>
        <table>
            <tbody>
                @foreach($data['costos']['rows'] as $r)
                    <tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row"><td colspan="2"><strong>Total Costos</strong></td><td class="text-right mono"><strong>$ {{ number_format($data['costos']['total'],0,',','.') }}</strong></td></tr>
            </tfoot>
        </table>
        <div class="section-title">GASTOS</div>
        <table>
            <tbody>
                @foreach($data['gastos']['rows'] as $r)
                    <tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row"><td colspan="2"><strong>Total Gastos</strong></td><td class="text-right mono"><strong>$ {{ number_format($data['gastos']['total'],0,',','.') }}</strong></td></tr>
            </tfoot>
        </table>
        <div class="utilidad-box {{ $data['utilidad'] < 0 ? 'perdida' : '' }}">
            {{ $data['utilidad'] >= 0 ? 'UTILIDAD DEL EJERCICIO' : 'PÉRDIDA DEL EJERCICIO' }}:
            $ {{ number_format(abs($data['utilidad']),0,',','.') }}
        </div>
    @endif

    {{-- ── Libro IVA ── --}}
    @if($report === 'iva')
        <table style="margin-bottom:14px; border:1px solid #d4f0e1;">
            <tbody>
                <tr>
                    <td style="padding:6px 10px; font-weight:bold; color:#165e36;">IVA generado (ventas)</td>
                    <td class="text-right mono" style="padding:6px 10px; color:#165e36; font-weight:bold;">$ {{ number_format($data['iva_ventas'], 0, ',', '.') }}</td>
                </tr>
                <tr style="background:#f8fafc;">
                    <td style="padding:6px 10px; color:#475569;">IVA descontable (compras)</td>
                    <td class="text-right mono" style="padding:6px 10px; color:#475569;">($ {{ number_format($data['iva_compras'], 0, ',', '.') }})</td>
                </tr>
                <tr>
                    <td style="padding:6px 10px; color:#b45309;">Reteiva practicada (2367)</td>
                    <td class="text-right mono" style="padding:6px 10px; color:#b45309;">($ {{ number_format($data['reteiva'], 0, ',', '.') }})</td>
                </tr>
                <tr style="background:#f8fafc;">
                    <td style="padding:6px 10px; color:#7c3aed;">Reteica practicada (2368)</td>
                    <td class="text-right mono" style="padding:6px 10px; color:#7c3aed;">($ {{ number_format($data['reteica'], 0, ',', '.') }})</td>
                </tr>
                @php $saldo = $data['saldo_dian']; @endphp
                <tr style="border-top:2px solid #10472a;">
                    <td style="padding:6px 10px; font-weight:bold; color:{{ $saldo > 0 ? '#dc2626' : '#165e36' }};">
                        Saldo {{ $saldo > 0 ? 'a pagar DIAN' : 'a favor' }}
                    </td>
                    <td class="text-right mono" style="padding:6px 10px; font-weight:bold; color:{{ $saldo > 0 ? '#dc2626' : '#165e36' }};">
                        $ {{ number_format(abs($saldo), 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="section-title">DETALLE DE MOVIMIENTOS</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th>Cuenta</th>
                    <th class="text-right">Débito</th>
                    <th class="text-right">Crédito</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['movimientos'] as $mov)
                    <tr>
                        <td class="mono">{{ \Carbon\Carbon::parse($mov['date'])->format('d/m/Y') }}</td>
                        <td class="mono">{{ $mov['reference'] }}</td>
                        <td>{{ $mov['description'] }}</td>
                        <td>{{ $mov['tipo'] }}</td>
                        <td class="mono">{{ $mov['cuenta'] }}</td>
                        <td class="text-right mono">{{ $mov['debito'] > 0 ? '$ '.number_format($mov['debito'],0,',','.') : '—' }}</td>
                        <td class="text-right mono">{{ $mov['credito'] > 0 ? '$ '.number_format($mov['credito'],0,',','.') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── Balance General ── --}}
    @if($report === 'balance')
        <div class="section-title">ACTIVOS</div>
        <table>
            <tbody>
                @foreach($data['activos']['rows'] as $r)
                    <tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row"><td colspan="2"><strong>Total Activos</strong></td><td class="text-right mono"><strong>$ {{ number_format($data['activos']['total'],0,',','.') }}</strong></td></tr>
            </tfoot>
        </table>
        <div class="section-title">PASIVOS</div>
        <table>
            <tbody>
                @foreach($data['pasivos']['rows'] as $r)
                    <tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row"><td colspan="2"><strong>Total Pasivos</strong></td><td class="text-right mono"><strong>$ {{ number_format($data['pasivos']['total'],0,',','.') }}</strong></td></tr>
            </tfoot>
        </table>
        <div class="section-title">PATRIMONIO</div>
        <table>
            <tbody>
                @foreach($data['patrimonio']['rows'] as $r)
                    <tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row"><td colspan="2"><strong>Total Patrimonio</strong></td><td class="text-right mono"><strong>$ {{ number_format($data['patrimonio']['total'],0,',','.') }}</strong></td></tr>
            </tfoot>
        </table>
    @endif

    {{-- ── Libro Mayor ── --}}
    @if($report === 'mayor')
        @if(isset($data['account']))
            <div class="entry-header">{{ $data['account']->code }} — {{ $data['account']->name }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Referencia</th>
                        <th>Descripción</th>
                        <th class="text-right">Débito</th>
                        <th class="text-right">Crédito</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['rows'] as $line)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($line['date'])->format('d/m/Y') }}</td>
                            <td class="mono">{{ $line['reference'] }}</td>
                            <td>{{ $line['description'] }}</td>
                            <td class="text-right mono">{{ $line['debit'] > 0 ? '$ '.number_format($line['debit'],0,',','.') : '' }}</td>
                            <td class="text-right mono">{{ $line['credit'] > 0 ? '$ '.number_format($line['credit'],0,',','.') : '' }}</td>
                            <td class="text-right mono">$ {{ number_format($line['balance'],0,',','.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    <div class="footer">
        Generado por ContaEdu — {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</body>
</html>
