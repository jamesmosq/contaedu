<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 20mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        /* ── Header ── */
        .header { border-bottom: 2px solid #10472a; padding-bottom: 14px; margin-bottom: 16px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; width: 55%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .empresa-name { font-size: 17px; font-weight: bold; color: #10472a; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        .doc-title { font-size: 13px; font-weight: bold; color: #165e36; text-transform: uppercase; letter-spacing: 0.03em; }
        .banco-name { font-size: 18px; font-weight: bold; color: #1e293b; margin: 3px 0; }
        .meta-table { margin: 0 0 0 auto; border: 1px solid #d4f0e1; }
        .meta-table th { background: #edf8f2; padding: 3px 8px; text-align: left; font-size: 9px; color: #10472a; font-weight: 600; }
        .meta-table td { padding: 3px 8px; font-size: 10px; color: #1e293b; }

        /* ── Section title ── */
        .section-title { font-size: 10px; font-weight: bold; color: #10472a; padding: 5px 10px; background: #edf8f2; border-left: 3px solid #d4a017; margin-bottom: 8px; margin-top: 14px; }

        /* ── Resumen ── */
        .resumen-box { border: 1px solid #d4f0e1; background: #f8fffe; padding: 10px 14px; margin-bottom: 14px; display: table; width: 100%; }
        .resumen-col { display: table-cell; width: 50%; vertical-align: top; }
        .resumen-item { margin-bottom: 5px; font-size: 10px; }
        .resumen-label { color: #475569; }
        .resumen-value { font-weight: bold; font-family: 'Courier New', monospace; color: #1e293b; }
        .resumen-saldo { font-size: 15px; font-weight: bold; color: #10472a; }

        /* ── Tabla de movimientos ── */
        table.movs { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.movs thead th { background: #165e36; color: #fff; padding: 5px 8px; text-align: left; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        table.movs thead th.r { text-align: right; }
        table.movs tbody td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; font-size: 9.5px; }
        table.movs tbody tr.cargo  td { background: #fff5f5; }
        table.movs tbody tr.abono  td { background: #f0fdf4; }
        table.movs tfoot td { padding: 5px 8px; font-weight: bold; background: #edf8f2; border-top: 2px solid #d4f0e1; font-size: 10px; }
        .r { text-align: right; }
        .mono { font-family: 'Courier New', monospace; }
        .text-red  { color: #dc2626; }
        .text-grn  { color: #16a34a; }

        /* ── Page wrapper ── */
        .page { padding: 28px 36px; }

        /* ── Footer ── */
        .footer { margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 8.5px; color: #94a3b8; text-align: center; }
        .watermark { color: #10472a; font-size: 8px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="page">
    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <div class="empresa-name">{{ $config?->company_name ?? 'Empresa' }}</div>
            <div class="subtitle">NIT: {{ $config?->nit ?? '—' }} &nbsp;|&nbsp; {{ $config?->city ?? '' }}</div>
            <div class="subtitle" style="margin-top:6px; font-size:9px; color:#94a3b8;">ContaEdu — Simulador Educativo</div>
        </div>
        <div class="header-right">
            <div class="doc-title">Extracto Bancario</div>
            <div class="banco-name">{{ $cuenta->nombreBanco() }}</div>
            <table class="meta-table">
                <tr><th>N° Cuenta</th><td class="mono">{{ $cuenta->account_number }}</td></tr>
                <tr><th>Tipo</th><td>{{ ucfirst($cuenta->account_type) }}</td></tr>
                <tr><th>Período</th><td>{{ $document->generado_at->translatedFormat('F Y') }}</td></tr>
                <tr><th>Emitido</th><td>{{ $document->generado_at->format('d/m/Y H:i') }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Resumen de cuenta --}}
    <div class="section-title">Resumen de cuenta</div>
    @php
        $totalDebitos  = $movimientos->filter(fn($m) => $m->esCargo())->sum(fn($m) => $m->valor + $m->gmf + $m->comision);
        $totalCreditos = $movimientos->filter(fn($m) => !$m->esCargo())->sum('valor');
        $saldoInicial  = $movimientos->isNotEmpty() ? ($movimientos->first()->saldo_despues - ($movimientos->first()->esCargo() ? -($movimientos->first()->valor + $movimientos->first()->gmf + $movimientos->first()->comision) : $movimientos->first()->valor)) : $cuenta->saldo;
    @endphp
    <div class="resumen-box">
        <div class="resumen-col">
            <div class="resumen-item"><span class="resumen-label">Banco: </span><span class="resumen-value">{{ $cuenta->nombreBanco() }}</span></div>
            <div class="resumen-item"><span class="resumen-label">Total débitos: </span><span class="resumen-value text-red">${{ number_format($totalDebitos, 0, ',', '.') }}</span></div>
            <div class="resumen-item"><span class="resumen-label">Total créditos: </span><span class="resumen-value text-grn">${{ number_format($totalCreditos, 0, ',', '.') }}</span></div>
        </div>
        <div class="resumen-col" style="text-align:right">
            <div class="resumen-item"><span class="resumen-label">Saldo actual: </span></div>
            <div class="resumen-saldo">${{ number_format($cuenta->saldo, 0, ',', '.') }}</div>
            @if($cuenta->sobregiro_usado > 0)
                <div class="resumen-item" style="color:#dc2626; margin-top:4px;">Sobregiro usado: ${{ number_format($cuenta->sobregiro_usado, 0, ',', '.') }}</div>
            @endif
            <div class="resumen-item" style="margin-top:4px;"><span class="resumen-label">Movimientos: </span><span class="resumen-value">{{ $movimientos->count() }}</span></div>
        </div>
    </div>

    {{-- Tabla de movimientos --}}
    <div class="section-title">Movimientos del período</div>
    @if($movimientos->isEmpty())
        <p style="color:#94a3b8; font-size:10px; padding:8px 0;">No hay movimientos registrados en este período.</p>
    @else
        <table class="movs">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th class="r">Débito</th>
                    <th class="r">Crédito</th>
                    <th class="r">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $mov)
                    @php $esCargo = $mov->esCargo(); @endphp
                    <tr class="{{ $esCargo ? 'cargo' : 'abono' }}">
                        <td class="mono">{{ $mov->fecha_transaccion->format('d/m/Y') }}</td>
                        <td>
                            {{ $mov->descripcion }}
                            @if($mov->gmf > 0)
                                <span style="color:#ea580c; font-size:8px;"> +GMF ${{ number_format($mov->gmf, 0, ',', '.') }}</span>
                            @endif
                            @if($mov->comision > 0)
                                <span style="color:#ea580c; font-size:8px;"> +ACH ${{ number_format($mov->comision, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td style="font-size:8.5px;">{{ str_replace('_', ' ', $mov->tipo) }}</td>
                        <td class="r mono text-red">
                            @if($esCargo)${{ number_format($mov->valor + $mov->gmf + $mov->comision, 0, ',', '.') }}@endif
                        </td>
                        <td class="r mono text-grn">
                            @if(!$esCargo)${{ number_format($mov->valor, 0, ',', '.') }}@endif
                        </td>
                        <td class="r mono">${{ number_format($mov->saldo_despues, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Totales del período</td>
                    <td class="r mono text-red">${{ number_format($totalDebitos, 0, ',', '.') }}</td>
                    <td class="r mono text-grn">${{ number_format($totalCreditos, 0, ',', '.') }}</td>
                    <td class="r mono">${{ number_format($cuenta->saldo, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        <div>Este extracto es de carácter educativo y no tiene validez legal. Generado por ContaEdu.</div>
        <div class="watermark">Banco simulado — {{ $cuenta->nombreBanco() }} | Cuenta {{ $cuenta->account_number }}</div>
    </div>
</div>
</body>
</html>
