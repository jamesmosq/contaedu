<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 15mm 18mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }
        .page { padding: 22px 30px; }

        /* ── Cabecera ── */
        .header { border-bottom: 2px solid #10472a; padding-bottom: 12px; margin-bottom: 14px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; width: 55%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .empresa-name { font-size: 16px; font-weight: bold; color: #10472a; }
        .subtitle { font-size: 9.5px; color: #475569; margin-top: 2px; }
        .report-title { font-size: 12px; font-weight: bold; color: #165e36; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px; }
        .period { font-size: 9px; color: #64748b; }
        .period table { margin: 4px 0 0 auto; border: 1px solid #d4f0e1; width: auto; }
        .period th { background: #edf8f2; padding: 3px 8px; text-align: left; font-size: 8.5px; color: #10472a; font-weight: 600; }
        .period td { padding: 3px 8px; font-size: 9px; color: #1e293b; font-weight: normal; border-bottom: none; }

        /* ── Resumen (cards) ── */
        .summary-grid { display: table; width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 6px; }
        .summary-cell { display: table-cell; width: 25%; padding: 8px 10px; border: 1px solid #d4f0e1; text-align: center; background: #edf8f2; }
        .summary-val { font-size: 13px; font-weight: bold; color: #10472a; }
        .summary-lbl { font-size: 7.5px; color: #64748b; margin-top: 2px; }

        /* ── Tablas ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead th { background: #165e36; color: #ffffff; padding: 5px 7px; text-align: left; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        thead th.text-right  { text-align: right; }
        thead th.text-center { text-align: center; }
        tbody td { padding: 3.5px 7px; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
        tfoot td { padding: 4px 7px; font-weight: bold; background: #edf8f2; border-top: 2px solid #d4f0e1; font-size: 9px; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Courier New', monospace; }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 7.5px; font-weight: bold; }
        .badge-activo { background: #dcfce7; color: #166534; }
        .badge-dep    { background: #f1f5f9;  color: #475569; }
        .badge-baja   { background: #fee2e2;  color: #b91c1c; }

        /* ── Nota educativa ── */
        .note-box { border: 1px solid #d4f0e1; background: #edf8f2; padding: 7px 10px; margin-bottom: 12px; font-size: 8.5px; color: #10472a; }

        /* ── Footer ── */
        .footer { margin-top: 16px; border-top: 1px solid #d4f0e1; padding-top: 7px; font-size: 7.5px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    @php
        $totalCosto      = $assets->sum('cost');
        $totalAcum       = $assets->sum('accumulated_depreciation');
        $totalVLibros    = $assets->sum(fn($a) => $a->bookValue());
        $totalSalvamento = $assets->sum('salvage_value');
        $activos         = $assets->where('status.value', 'activo')->count();
        $depreciados     = $assets->where('status.value', 'totalmente_depreciado')->count();
        $bajas           = $assets->where('status.value', 'dado_de_baja')->count();
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
        <div class="header-right">
            <div class="report-title">Activos Fijos</div>
            <div style="font-size:10px; color:#165e36; margin-bottom:4px;">Propiedades, Planta y Equipo</div>
            <div class="period">
                <table>
                    <tr>
                        <th>Generado</th>
                        <td>{{ now()->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="summary-grid">
        <div class="summary-cell">
            <div class="summary-val">{{ $assets->count() }}</div>
            <div class="summary-lbl">Total activos</div>
        </div>
        <div class="summary-cell">
            <div class="summary-val">${{ number_format($totalCosto, 0, ',', '.') }}</div>
            <div class="summary-lbl">Costo histórico total</div>
        </div>
        <div class="summary-cell">
            <div class="summary-val">${{ number_format($totalAcum, 0, ',', '.') }}</div>
            <div class="summary-lbl">Dep. acumulada total</div>
        </div>
        <div class="summary-cell">
            <div class="summary-val">${{ number_format($totalVLibros, 0, ',', '.') }}</div>
            <div class="summary-lbl">Valor en libros total</div>
        </div>
    </div>

    <div class="note-box">
        Método: <strong>Línea recta</strong> — Cuota mensual = (Costo − Salvamento) ÷ Vida útil en meses.
        Normas colombianas: cómputo 3 años, muebles/equipo 10 años, vehículos 5 años, maquinaria 10 años, edificios 20 años.
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre del activo</th>
                <th>Categoría</th>
                <th>Fecha adq.</th>
                <th class="text-right">Costo</th>
                <th class="text-right">Salvamento</th>
                <th class="text-right">Cuota mensual</th>
                <th class="text-right">Dep. acumulada</th>
                <th class="text-right">Valor en libros</th>
                <th class="text-center">% Dep.</th>
                <th>Estado</th>
                <th>Últ. dep.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
                <tr>
                    <td class="mono">{{ $asset->code }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category->label() }}</td>
                    <td>{{ $asset->acquisition_date->format('d/m/Y') }}</td>
                    <td class="text-right mono">${{ number_format($asset->cost, 0, ',', '.') }}</td>
                    <td class="text-right mono">${{ number_format($asset->salvage_value, 0, ',', '.') }}</td>
                    <td class="text-right mono">${{ number_format($asset->monthlyDepreciation(), 0, ',', '.') }}</td>
                    <td class="text-right mono">${{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                    <td class="text-right mono">${{ number_format($asset->bookValue(), 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($asset->depreciationProgress(), 1) }}%</td>
                    <td>
                        @php $sv = $asset->status->value; @endphp
                        <span class="badge {{ $sv === 'activo' ? 'badge-activo' : ($sv === 'dado_de_baja' ? 'badge-baja' : 'badge-dep') }}">
                            {{ $asset->status->label() }}
                        </span>
                    </td>
                    <td>{{ $asset->last_depreciation_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align:center; color:#94a3b8; padding:12px;">Sin activos registrados</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><strong>TOTALES</strong></td>
                <td class="text-right mono"><strong>${{ number_format($totalCosto, 0, ',', '.') }}</strong></td>
                <td class="text-right mono"><strong>${{ number_format($totalSalvamento, 0, ',', '.') }}</strong></td>
                <td></td>
                <td class="text-right mono"><strong>${{ number_format($totalAcum, 0, ',', '.') }}</strong></td>
                <td class="text-right mono"><strong>${{ number_format($totalVLibros, 0, ',', '.') }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size:8.5px; color:#475569; padding: 5px 0;">
        En uso: <strong>{{ $activos }}</strong> &nbsp;|&nbsp;
        Totalmente depreciados: <strong>{{ $depreciados }}</strong> &nbsp;|&nbsp;
        Dados de baja: <strong>{{ $bajas }}</strong>
    </div>

    <div class="footer">
        {{ $config?->razon_social ?? config('app.name') }} — Registro de Activos Fijos — ContaEdu (plataforma educativa)
    </div>

</div>
</body>
</html>
