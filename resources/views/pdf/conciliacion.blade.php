<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #334155; }
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 14px; }
        .company { font-size: 13px; font-weight: bold; color: #1e3a8a; }
        .subtitle { font-size: 10px; color: #64748b; }
        .report-title { font-size: 12px; font-weight: bold; color: #1e40af; margin-top: 4px; }
        .period { font-size: 8.5px; color: #94a3b8; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f1f5f9; color: #475569; font-size: 8px; text-transform: uppercase; padding: 4px 7px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 3.5px 7px; border-bottom: 1px solid #f1f5f9; }
        tfoot td { background: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1; }
        .text-right { text-align: right; }
        .mono { font-family: 'Courier New', monospace; }
        .section-title { font-size: 10px; font-weight: bold; color: #1e3a8a; padding: 6px 0 4px; border-bottom: 1px solid #e2e8f0; margin-bottom: 6px; margin-top: 12px; }
        .summary-table { width: 100%; margin-bottom: 14px; }
        .summary-table td { padding: 5px 10px; border: 1px solid #e2e8f0; }
        .summary-label { color: #64748b; }
        .summary-value { text-align: right; font-weight: bold; font-family: 'Courier New', monospace; color: #1e3a8a; }
        .row-cleared { background: #f0fdf4; }
        .row-transit { background: #eff6ff; }
        .row-outstanding { background: #fff7ed; }
        .row-bank { background: #faf5ff; }
        .formula-box { border: 1px solid #fde68a; background: #fffbeb; padding: 8px 10px; margin-bottom: 12px; font-size: 8.5px; color: #92400e; }
        .balanced-box { border: 2px solid #16a34a; background: #f0fdf4; padding: 8px 10px; text-align: right; }
        .unbalanced-box { border: 2px solid #dc2626; background: #fef2f2; padding: 8px 10px; text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 7.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; text-align: center; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7.5px; font-weight: bold; }
        .badge-cruzado { background: #dcfce7; color: #15803d; }
        .badge-transito { background: #dbeafe; color: #1d4ed8; }
        .badge-circulacion { background: #ffedd5; color: #c2410c; }
    </style>
</head>
<body>

    @php
        $rec        = $reconciliation;
        $bookItems  = $rec->items->where('source', 'libro')->sortBy('date');
        $bankItems  = $rec->items->where('source', 'banco')->sortBy('date');
    @endphp

    <div class="header">
        <div class="company">{{ $config?->razon_social ?? config('app.name') }}</div>
        @if($config?->nit) <div class="subtitle">NIT: {{ $config->nit }}</div> @endif
        @if($config?->ciiu_code) <div class="subtitle">CIIU {{ $config->ciiu_code }} — {{ $config->ciiu_description }}</div> @endif
        <div class="report-title">Conciliación Bancaria — {{ $rec->account->code }} {{ $rec->account->name }}</div>
        <div class="period">
            Período: {{ $rec->period_start->format('d/m/Y') }} al {{ $rec->period_end->format('d/m/Y') }}
            &nbsp;|&nbsp; Estado: {{ $rec->isFinalizada() ? 'Finalizada' : 'Borrador' }}
            &nbsp;|&nbsp; Generado el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    {{-- Fórmula de conciliación --}}
    <div class="formula-box">
        <strong>Fórmula de conciliación bancaria:</strong><br>
        Saldo extracto bancario + Depósitos en tránsito − Cheques en circulación +/− Ajustes banco = Saldo ajustado extracto<br>
        El saldo ajustado del extracto debe ser igual al saldo según libros contables. Diferencia = 0 indica conciliación correcta.
    </div>

    {{-- Resumen de cuadre --}}
    <div class="section-title">Resumen del cuadre</div>
    <table class="summary-table">
        <tr>
            <td class="summary-label">Saldo según extracto bancario</td>
            <td class="summary-value">${{ number_format($rec->statement_balance, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">+ Depósitos en tránsito (en libros, no en banco)</td>
            <td class="summary-value">+ ${{ number_format($rec->depositsInTransit(), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">− Cheques en circulación (en libros, no cobrados)</td>
            <td class="summary-value">− ${{ number_format($rec->outstandingChecks(), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">+/− Ajustes banco (cargos, intereses, notas)</td>
            <td class="summary-value">{{ $rec->bankAdjustments() >= 0 ? '+' : '−' }} ${{ number_format(abs($rec->bankAdjustments()), 2, ',', '.') }}</td>
        </tr>
        <tr style="background:#f1f5f9;">
            <td><strong>= Saldo ajustado del extracto</strong></td>
            <td class="summary-value">${{ number_format($rec->adjustedStatementBalance(), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Saldo según libros contables</td>
            <td class="summary-value">${{ number_format($rec->bookBalance(), 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="{{ $rec->isBalanced() ? 'balanced-box' : 'unbalanced-box' }}">
        <strong>Diferencia: ${{ number_format(abs($rec->difference()), 2, ',', '.') }}</strong>
        {{ $rec->isBalanced() ? '✓ Conciliación cuadrada correctamente' : '⚠ Existe diferencia — revisar partidas' }}
    </div>

    {{-- Movimientos del libro --}}
    <div class="section-title">Movimientos en libros contables — {{ $bookItems->count() }} registros</div>
    @if($bookItems->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th class="text-right">Débito</th>
                    <th class="text-right">Crédito</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookItems as $item)
                    <tr class="{{ $item->reconciled ? 'row-cleared' : ($item->debit > 0 ? 'row-transit' : 'row-outstanding') }}">
                        <td>{{ $item->date->format('d/m/Y') }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right mono">{{ $item->debit > 0 ? '$'.number_format($item->debit, 0, ',', '.') : '—' }}</td>
                        <td class="text-right mono">{{ $item->credit > 0 ? '$'.number_format($item->credit, 0, ',', '.') : '—' }}</td>
                        <td>
                            @if($item->reconciled)
                                <span class="badge badge-cruzado">Cruzado</span>
                            @elseif($item->debit > 0)
                                <span class="badge badge-transito">En tránsito</span>
                            @else
                                <span class="badge badge-circulacion">En circulación</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Subtotales</strong></td>
                    <td class="text-right mono">${{ number_format($bookItems->sum('debit'), 0, ',', '.') }}</td>
                    <td class="text-right mono">${{ number_format($bookItems->sum('credit'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- Partidas bancarias --}}
    @if($bankItems->isNotEmpty())
        <div class="section-title">Partidas del extracto no en libros — {{ $bankItems->count() }} registros</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th class="text-right">Débito</th>
                    <th class="text-right">Crédito</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bankItems as $item)
                    <tr class="row-bank">
                        <td>{{ $item->date->format('d/m/Y') }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right mono">{{ $item->debit > 0 ? '$'.number_format($item->debit, 0, ',', '.') : '—' }}</td>
                        <td class="text-right mono">{{ $item->credit > 0 ? '$'.number_format($item->credit, 0, ',', '.') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Subtotales</strong></td>
                    <td class="text-right mono">${{ number_format($bankItems->sum('debit'), 0, ',', '.') }}</td>
                    <td class="text-right mono">${{ number_format($bankItems->sum('credit'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    @if($rec->notes)
        <div style="font-size:8.5px;color:#64748b;margin-top:8px;"><strong>Notas:</strong> {{ $rec->notes }}</div>
    @endif

    <div class="footer">
        {{ $config?->razon_social ?? config('app.name') }} — Conciliación Bancaria {{ $rec->period_start->format('m/Y') }} — ContaEdu (plataforma educativa)
    </div>

</body>
</html>
