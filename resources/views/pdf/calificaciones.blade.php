<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 14mm 18mm; size: A4 landscape; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

        .header { border-bottom: 2px solid #10472a; padding-bottom: 12px; margin-bottom: 14px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; width: 60%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .institution-name { font-size: 16px; font-weight: bold; color: #10472a; }
        .report-title { font-size: 12px; font-weight: bold; color: #165e36; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 3px; }
        .meta { font-size: 9px; color: #64748b; margin-top: 3px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #165e36; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; }
        thead th.center { text-align: center; }
        tbody tr:nth-child(even) { background: #f8fafb; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        tbody td.center { text-align: center; }

        .score-high   { color: #10472a; font-weight: bold; }
        .score-mid    { color: #d97706; }
        .score-low    { color: #dc2626; }
        .score-empty  { color: #94a3b8; }

        .promedio-high { background: #dcfce7; color: #15803d; font-weight: bold; border-radius: 3px; padding: 1px 5px; }
        .promedio-mid  { background: #fef9c3; color: #854d0e; font-weight: bold; border-radius: 3px; padding: 1px 5px; }
        .promedio-low  { background: #fee2e2; color: #991b1b; font-weight: bold; border-radius: 3px; padding: 1px 5px; }

        .footer { margin-top: 14px; font-size: 8px; color: #94a3b8; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="institution-name">{{ $institution->name }}</div>
            <div class="meta">Reporte de calificaciones — {{ $groupName }}</div>
        </div>
        <div class="header-right">
            <div class="report-title">Calificaciones</div>
            <div class="meta">Generado: {{ $generatedAt }}</div>
            <div class="meta">Escala: 1.0 — 5.0</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Estudiante</th>
                <th>Empresa</th>
                <th>Grupo</th>
                @foreach($modules as $label)
                    <th class="center">{{ $label }}</th>
                @endforeach
                <th class="center">Promedio</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $prom = $row['promedio'];
                    $promClass = $prom === null ? '' : ($prom >= 4 ? 'promedio-high' : ($prom >= 3 ? 'promedio-mid' : 'promedio-low'));
                @endphp
                <tr>
                    <td>{{ $row['tenant']->id }}</td>
                    <td>{{ $row['tenant']->student_name }}</td>
                    <td>{{ $row['tenant']->company_name }}</td>
                    <td>{{ $row['tenant']->group?->name ?? '—' }}</td>
                    @foreach(array_keys($modules) as $mod)
                        @php $val = $row['scores'][$mod]; @endphp
                        <td class="center {{ $val === null ? 'score-empty' : ($val >= 4 ? 'score-high' : ($val >= 3 ? 'score-mid' : 'score-low')) }}">
                            {{ $val !== null ? number_format($val, 1) : '—' }}
                        </td>
                    @endforeach
                    <td class="center">
                        @if($prom !== null)
                            <span class="{{ $promClass }}">{{ number_format($prom, 1) }}</span>
                        @else
                            <span class="score-empty">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($modules) + 1 }}" style="text-align:center;padding:16px;color:#94a3b8;">
                        No hay estudiantes con calificaciones en este período.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">ContaEdu — {{ $institution->name }} — {{ $generatedAt }}</div>
</body>
</html>
