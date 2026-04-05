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
        .doc-title h1 { font-size: 13px; font-weight: bold; color: #165e36; text-transform: uppercase; letter-spacing: 0.03em; }
        .doc-title .cuenta { font-size: 18px; font-weight: bold; color: #1e293b; margin: 3px 0; }
        .doc-title .meta { font-size: 10px; color: #64748b; margin-top: 4px; }
        .doc-title .meta table { margin: 0 0 0 auto; border: 1px solid #d4f0e1; width: auto; }
        .doc-title .meta th { background: #edf8f2; padding: 3px 8px; text-align: left; font-size: 9px; color: #10472a; font-weight: 600; }
        .doc-title .meta td { padding: 3px 8px; font-size: 10px; color: #1e293b; font-weight: normal; border-bottom: none; }

        /* ── Tablas ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead th { background: #165e36; color: #ffffff; padding: 6px 9px; text-align: left; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        thead th.text-right { text-align: right; }
        tbody td { padding: 4px 9px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        tfoot td { padding: 5px 9px; font-weight: bold; background: #edf8f2; border-top: 2px solid #d4f0e1; font-size: 10px; }
        .text-right { text-align: right; }
        .mono { font-family: 'Courier New', monospace; }

        /* ── Resumen de cuadre ── */
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary-table td { padding: 5px 10px; border-bottom: 1px solid #edf8f2; font-size: 10.5px; }
        .summary-table tr:last-child td { border-bottom: none; }
        .summary-label { color: #475569; }
        .summary-value { text-align: right; font-weight: bold; font-family: 'Courier New', monospace; color: #10472a; width: 160px; }
        .summary-highlight { background: #edf8f2; }
        .summary-highlight td { font-weight: bold; }

        /* ── Sección título ── */
        .section-title { font-size: 10px; font-weight: bold; color: #10472a; padding: 5px 10px; background: #edf8f2; border-left: 3px solid #d4a017; margin-bottom: 8px; margin-top: 14px; }

        /* ── Cajas informativas ── */
        .formula-box { border: 1px solid #fde68a; background: #fffbeb; padding: 8px 12px; margin-bottom: 14px; font-size: 9px; color: #78350f; line-height: 1.5; }
        .balanced-box   { border: 2px solid #165e36; background: #edf8f2; padding: 8px 12px; text-align: right; margin-bottom: 8px; font-size: 10.5px; color: #10472a; font-weight: bold; }
        .unbalanced-box { border: 2px solid #dc2626; background: #fef2f2; padding: 8px 12px; text-align: right; margin-bottom: 8px; font-size: 10.5px; color: #991b1b; font-weight: bold; }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 1px 7px; border-radius: 4px; font-size: 8.5px; font-weight: bold; }
        .badge-cruzado     { background: #dcfce7; color: #166534; }
        .badge-transito    { background: #edf8f2; color: #165e36; }
        .badge-circulacion { background: #ffedd5; color: #c2410c; }

        /* ── Filas coloreadas ── */
        .row-cleared     { background: #f0fdf4; }
        .row-transit     { background: #edf8f2; }
        .row-outstanding { background: #fff7ed; }
        .row-bank        { background: #f8fafc; }

        /* ── Footer ── */
        .footer { margin-top: 20px; border-top: 1px solid #d4f0e1; padding-top: 8px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    @php
        $rec       = $reconciliation;
        $bookItems = $rec->items->where('source', 'libro')->sortBy('date');
        $bankItems = $rec->items->where('source', 'banco')->sortBy('date');
    @endphp

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
        <div class="header-right doc-title">
            <h1>Conciliación Bancaria</h1>
            <div class="cuenta">{{ $rec->account->code }} {{ $rec->account->name }}</div>
            <div class="meta">
                <table>
                    <tr>
                        <th>Período</th>
                        <td>{{ $rec->period_start->format('d/m/Y') }} al {{ $rec->period_end->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>{{ $rec->isFinalizada() ? 'Finalizada' : 'Borrador' }}</td>
                    </tr>
                    <tr>
                        <th>Generado</th>
                        <td>{{ now()->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Fórmula educativa --}}
    <div class="formula-box">
        <strong>Fórmula de conciliación bancaria:</strong><br>
        Saldo extracto bancario + Depósitos en tránsito − Cheques en circulación +/− Ajustes banco = Saldo ajustado extracto.<br>
        El saldo ajustado del extracto debe ser igual al saldo según libros contables. <strong>Diferencia = $0</strong> indica conciliación correcta.
    </div>

    {{-- Resumen de cuadre --}}
    <div class="section-title">Resumen del cuadre</div>
    <table class="summary-table" style="border: 1px solid #d4f0e1;">
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
        <tr class="summary-highlight">
            <td><strong>= Saldo ajustado del extracto</strong></td>
            <td class="summary-value">${{ number_format($rec->adjustedStatementBalance(), 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Saldo según libros contables</td>
            <td class="summary-value">${{ number_format($rec->bookBalance(), 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="{{ $rec->isBalanced() ? 'balanced-box' : 'unbalanced-box' }}">
        Diferencia: ${{ number_format(abs($rec->difference()), 2, ',', '.') }}
        &nbsp;&nbsp;{{ $rec->isBalanced() ? '✓ Conciliación cuadrada correctamente' : '⚠ Existe diferencia — revisar partidas' }}
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
    @else
        <p style="font-size:10px; color:#94a3b8; padding: 8px 0;">Sin movimientos en libros para este período.</p>
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
        <div style="font-size:9.5px; color:#475569; margin-top:8px; padding: 6px 10px; background:#f8fafc; border-left: 3px solid #d4a017;">
            <strong>Notas:</strong> {{ $rec->notes }}
        </div>
    @endif

    <div class="footer">
        {{ $config?->razon_social ?? config('app.name') }} — Conciliación Bancaria {{ $rec->period_start->format('m/Y') }} — ContaEdu (plataforma educativa)
    </div>

</div>
</body>
</html>
