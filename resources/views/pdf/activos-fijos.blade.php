<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #334155; }
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 14px; }
        .company { font-size: 13px; font-weight: bold; color: #1e3a8a; }
        .subtitle { font-size: 10px; color: #64748b; }
        .report-title { font-size: 12px; font-weight: bold; color: #1e40af; margin-top: 4px; }
        .period { font-size: 8px; color: #94a3b8; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th { background: #f1f5f9; color: #475569; font-size: 7.5px; text-transform: uppercase; padding: 4px 6px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 3px 6px; border-bottom: 1px solid #f1f5f9; font-size: 8.5px; }
        tfoot td { background: #f1f5f9; font-weight: bold; border-top: 1px solid #cbd5e1; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Courier New', monospace; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
        .badge-activo { background: #dcfce7; color: #15803d; }
        .badge-dep { background: #f1f5f9; color: #475569; }
        .badge-baja { background: #fee2e2; color: #b91c1c; }
        .progress-bar { background: #e2e8f0; border-radius: 3px; height: 6px; width: 60px; display: inline-block; position: relative; vertical-align: middle; }
        .progress-fill { background: #3b82f6; border-radius: 3px; height: 6px; display: block; }
        .section-title { font-size: 10px; font-weight: bold; color: #1e3a8a; margin-bottom: 6px; }
        .summary-grid { display: table; width: 100%; margin-bottom: 14px; }
        .summary-cell { display: table-cell; width: 25%; padding: 8px; border: 1px solid #e2e8f0; text-align: center; }
        .summary-val { font-size: 12px; font-weight: bold; color: #1e3a8a; }
        .summary-lbl { font-size: 7.5px; color: #94a3b8; margin-top: 2px; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 7.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; text-align: center; }
        .note-box { border: 1px solid #bfdbfe; background: #eff6ff; padding: 6px 8px; margin-bottom: 10px; font-size: 8px; color: #1e40af; border-radius: 3px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">{{ $config?->razon_social ?? config('app.name') }}</div>
        @if($config?->nit) <div class="subtitle">NIT: {{ $config->nit }}</div> @endif
        @if($config?->ciiu_code) <div class="subtitle">CIIU {{ $config->ciiu_code }} — {{ $config->ciiu_description }}</div> @endif
        <div class="report-title">Registro de Activos Fijos — Propiedades, Planta y Equipo</div>
        <div class="period">Generado el {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    @php
        $totalCosto      = $assets->sum('cost');
        $totalAcum       = $assets->sum('accumulated_depreciation');
        $totalVLibros    = $assets->sum(fn($a) => $a->bookValue());
        $totalSalvamento = $assets->sum('salvage_value');
        $activos         = $assets->where('status.value', 'activo')->count();
        $depreciados     = $assets->where('status.value', 'totalmente_depreciado')->count();
        $bajas           = $assets->where('status.value', 'dado_de_baja')->count();
    @endphp

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
        Método de depreciación: <strong>Línea recta</strong> — Cuota mensual = (Costo − Valor de salvamento) ÷ Vida útil en meses.
        Normas contables colombianas: equipos de cómputo 3 años, muebles y equipo de oficina 10 años, vehículos 5 años, maquinaria 10 años, edificios 20 años.
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
                <th class="text-center">Progreso</th>
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
                    <td class="text-center">
                        {{ number_format($asset->depreciationProgress(), 1) }}%
                    </td>
                    <td>
                        @php $sv = $asset->status->value; @endphp
                        <span class="badge {{ $sv === 'activo' ? 'badge-activo' : ($sv === 'dado_de_baja' ? 'badge-baja' : 'badge-dep') }}">
                            {{ $asset->status->label() }}
                        </span>
                    </td>
                    <td>{{ $asset->last_depreciation_date?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align:center;color:#94a3b8;padding:12px;">Sin activos registrados</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"><strong>TOTALES</strong></td>
                <td class="text-right mono">${{ number_format($totalCosto, 0, ',', '.') }}</td>
                <td class="text-right mono">${{ number_format($totalSalvamento, 0, ',', '.') }}</td>
                <td></td>
                <td class="text-right mono">${{ number_format($totalAcum, 0, ',', '.') }}</td>
                <td class="text-right mono">${{ number_format($totalVLibros, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size:8px;color:#475569;">
        Activos en uso: <strong>{{ $activos }}</strong> &nbsp;|&nbsp;
        Totalmente depreciados: <strong>{{ $depreciados }}</strong> &nbsp;|&nbsp;
        Dados de baja: <strong>{{ $bajas }}</strong>
    </div>

    <div class="footer">
        {{ $config?->razon_social ?? config('app.name') }} — Registro de Activos Fijos — ContaEdu (plataforma educativa)
    </div>

</body>
</html>
