<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #334155; }
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 16px; }
        .company { font-size: 14px; font-weight: bold; color: #1e3a8a; }
        .subtitle { font-size: 11px; color: #64748b; }
        .report-title { font-size: 13px; font-weight: bold; color: #1e40af; margin-top: 4px; }
        .period { font-size: 9px; color: #94a3b8; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f1f5f9; color: #475569; font-size: 8px; text-transform: uppercase; padding: 5px 8px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; }
        tfoot td { background: #f8fafc; font-weight: bold; border-top: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .mono { font-family: 'Courier New', monospace; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-auto { background: #dbeafe; color: #1d4ed8; }
        .entry-header { background: #f8fafc; padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-weight: bold; }
        .section-title { font-size: 11px; font-weight: bold; padding: 8px 0 4px; color: #1e3a8a; border-bottom: 1px solid #e2e8f0; margin-bottom: 6px; }
        .totals-row { font-weight: bold; background: #f1f5f9; }
        .utilidad-box { border: 2px solid #16a34a; padding: 10px; margin-top: 12px; text-align: right; }
        .utilidad-box.perdida { border-color: #dc2626; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">{{ $config?->razon_social ?? config('app.name') }}</div>
        @if($config?->nit) <div class="subtitle">NIT: {{ $config->nit }}</div> @endif
        <div class="report-title">{{ $title }}</div>
        @if(!in_array($report, ['cartera','cxp','balance']))
            <div class="period">Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
        @elseif($report === 'balance')
            <div class="period">Al: {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
        @endif
    </div>

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
                        <td class="mono">{{ $report === 'cartera' ? $row['reference'] : $row['reference'] }}</td>
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
                    <td colspan="4">Total</td>
                    <td class="text-right mono">$ {{ number_format($data->sum($report==='cartera'?'total':'balance'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    @if($report === 'diario')
        @foreach($data as $entry)
            <div class="entry-header">
                {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }} &nbsp;|&nbsp; {{ $entry->reference }} &nbsp;|&nbsp; {{ $entry->description }}
                @if($entry->auto_generated) <span class="badge badge-auto">AUTO</span> @endif
            </div>
            <table>
                <thead><tr><th>Código</th><th>Cuenta</th><th>Descripción</th><th class="text-right">Débito</th><th class="text-right">Crédito</th></tr></thead>
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
                <tfoot><tr><td colspan="3" class="text-right">Totales:</td><td class="text-right mono">$ {{ number_format($entry->lines->sum('debit'),0,',','.') }}</td><td class="text-right mono">$ {{ number_format($entry->lines->sum('credit'),0,',','.') }}</td></tr></tfoot>
            </table>
        @endforeach
    @endif

    @if($report === 'comprobacion')
        <table>
            <thead><tr><th>Código</th><th>Cuenta</th><th>Tipo</th><th class="text-right">Débitos</th><th class="text-right">Créditos</th><th class="text-right">Saldo</th></tr></thead>
            <tbody>
                @foreach($data as $row)
                    <tr><td class="mono">{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td>{{ ucfirst($row['type']) }}</td><td class="text-right mono">$ {{ number_format($row['total_debit'],0,',','.') }}</td><td class="text-right mono">$ {{ number_format($row['total_credit'],0,',','.') }}</td><td class="text-right mono">$ {{ number_format($row['balance'],0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr><td colspan="3" class="text-right">TOTALES</td><td class="text-right mono">$ {{ number_format($data->sum('total_debit'),0,',','.') }}</td><td class="text-right mono">$ {{ number_format($data->sum('total_credit'),0,',','.') }}</td><td></td></tr></tfoot>
        </table>
    @endif

    @if($report === 'resultados')
        <div class="section-title">INGRESOS</div>
        <table><tbody>@foreach($data['ingresos']['rows'] as $r)<tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>@endforeach</tbody><tfoot><tr class="totals-row"><td colspan="2">Total Ingresos</td><td class="text-right mono">$ {{ number_format($data['ingresos']['total'],0,',','.') }}</td></tr></tfoot></table>
        <div class="section-title">COSTOS DE VENTA</div>
        <table><tbody>@foreach($data['costos']['rows'] as $r)<tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>@endforeach</tbody><tfoot><tr class="totals-row"><td colspan="2">Total Costos</td><td class="text-right mono">$ {{ number_format($data['costos']['total'],0,',','.') }}</td></tr></tfoot></table>
        <div class="section-title">GASTOS</div>
        <table><tbody>@foreach($data['gastos']['rows'] as $r)<tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>@endforeach</tbody><tfoot><tr class="totals-row"><td colspan="2">Total Gastos</td><td class="text-right mono">$ {{ number_format($data['gastos']['total'],0,',','.') }}</td></tr></tfoot></table>
        <div class="utilidad-box {{ $data['utilidad'] < 0 ? 'perdida' : '' }}">
            <strong>{{ $data['utilidad'] >= 0 ? 'UTILIDAD DEL EJERCICIO' : 'PÉRDIDA DEL EJERCICIO' }}: $ {{ number_format(abs($data['utilidad']),0,',','.') }}</strong>
        </div>
    @endif

    @if($report === 'balance')
        <div class="section-title">ACTIVOS</div>
        <table><tbody>@foreach($data['activos']['rows'] as $r)<tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>@endforeach</tbody><tfoot><tr class="totals-row"><td colspan="2">Total Activos</td><td class="text-right mono">$ {{ number_format($data['activos']['total'],0,',','.') }}</td></tr></tfoot></table>
        <div class="section-title">PASIVOS</div>
        <table><tbody>@foreach($data['pasivos']['rows'] as $r)<tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>@endforeach</tbody><tfoot><tr class="totals-row"><td colspan="2">Total Pasivos</td><td class="text-right mono">$ {{ number_format($data['pasivos']['total'],0,',','.') }}</td></tr></tfoot></table>
        <div class="section-title">PATRIMONIO</div>
        <table><tbody>@foreach($data['patrimonio']['rows'] as $r)<tr><td class="mono">{{ $r['code'] }}</td><td>{{ $r['name'] }}</td><td class="text-right mono">$ {{ number_format($r['balance'],0,',','.') }}</td></tr>@endforeach</tbody><tfoot><tr class="totals-row"><td colspan="2">Total Patrimonio</td><td class="text-right mono">$ {{ number_format($data['patrimonio']['total'],0,',','.') }}</td></tr></tfoot></table>
    @endif

    <div class="footer">Generado por ContaEdu — {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
