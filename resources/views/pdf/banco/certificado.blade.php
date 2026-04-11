<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 20mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }
        .header { border-bottom: 2px solid #10472a; padding-bottom: 14px; margin-bottom: 20px; display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: top; width: 55%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .empresa-name { font-size: 17px; font-weight: bold; color: #10472a; }
        .subtitle { font-size: 10px; color: #475569; margin-top: 2px; }
        .doc-title { font-size: 13px; font-weight: bold; color: #165e36; text-transform: uppercase; }
        .banco-name { font-size: 18px; font-weight: bold; color: #1e293b; margin: 3px 0; }
        .meta-table { margin: 0 0 0 auto; border: 1px solid #d4f0e1; }
        .meta-table th { background: #edf8f2; padding: 3px 8px; font-size: 9px; color: #10472a; font-weight: 600; }
        .meta-table td { padding: 3px 8px; font-size: 10px; }

        .cert-body { border: 1px solid #d4f0e1; background: #f8fffe; padding: 20px 24px; margin: 14px 0; line-height: 1.7; font-size: 11px; }
        .cert-field { display: inline-block; border-bottom: 1px solid #10472a; min-width: 120px; font-weight: bold; color: #10472a; }
        .firma-section { margin-top: 40px; display: table; width: 100%; }
        .firma-col { display: table-cell; width: 50%; text-align: center; }
        .firma-line { border-top: 1px solid #334155; width: 200px; margin: 0 auto 5px; }
        .firma-label { font-size: 9.5px; color: #475569; }
        .sello-box { border: 2px solid #10472a; padding: 10px 20px; display: inline-block; text-align: center; margin-top: 8px; }
        .sello-label { font-size: 9px; color: #10472a; font-weight: bold; text-transform: uppercase; }
        .footer { margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 8.5px; color: #94a3b8; text-align: center; }
        .page { padding: 28px 36px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="empresa-name">{{ $cuenta->nombreBanco() }}</div>
            <div class="subtitle">Simulador Bancario Educativo</div>
            <div class="subtitle" style="margin-top:6px; font-size:9px; color:#94a3b8;">ContaEdu — Sistema de Formación Contable</div>
        </div>
        <div class="header-right">
            <div class="doc-title">Certificado Bancario</div>
            <div class="banco-name" style="font-size:13px;">No. {{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</div>
            <table class="meta-table">
                <tr><th>Fecha</th><td>{{ $document->generado_at->format('d/m/Y') }}</td></tr>
                <tr><th>Ciudad</th><td>{{ $config?->city ?? 'Colombia' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="cert-body">
        <p style="margin-bottom:14px;">
            El <span class="cert-field">{{ $cuenta->nombreBanco() }}</span>, certifica que la empresa
            <span class="cert-field">{{ $config?->company_name ?? '—' }}</span>,
            identificada con NIT <span class="cert-field">{{ $config?->nit ?? '—' }}</span>,
            es cliente de esta entidad y titular de la siguiente cuenta:
        </p>

        <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
            <tr style="background:#edf8f2;">
                <td style="padding:6px 10px; font-size:10px; font-weight:bold; color:#10472a; width:40%;">Tipo de cuenta</td>
                <td style="padding:6px 10px; font-size:10px;">{{ ucfirst($cuenta->account_type) }}</td>
            </tr>
            <tr>
                <td style="padding:6px 10px; font-size:10px; font-weight:bold; color:#10472a; border-bottom:1px solid #edf8f2;">Número de cuenta</td>
                <td style="padding:6px 10px; font-size:10px; font-family:'Courier New',monospace;">{{ $cuenta->account_number }}</td>
            </tr>
            <tr style="background:#edf8f2;">
                <td style="padding:6px 10px; font-size:10px; font-weight:bold; color:#10472a;">Saldo a la fecha</td>
                <td style="padding:6px 10px; font-size:12px; font-weight:bold; color:#10472a; font-family:'Courier New',monospace;">${{ number_format($cuenta->saldo, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding:6px 10px; font-size:10px; font-weight:bold; color:#10472a; border-bottom:1px solid #edf8f2;">Fecha de apertura</td>
                <td style="padding:6px 10px; font-size:10px;">{{ \Carbon\Carbon::parse($cuenta->fecha_apertura)->format('d/m/Y') }}</td>
            </tr>
            <tr style="background:#edf8f2;">
                <td style="padding:6px 10px; font-size:10px; font-weight:bold; color:#10472a;">Estado</td>
                <td style="padding:6px 10px; font-size:10px; color:{{ $cuenta->bloqueada ? '#dc2626' : '#16a34a' }}; font-weight:bold;">
                    {{ $cuenta->bloqueada ? 'BLOQUEADA' : 'ACTIVA' }}
                </td>
            </tr>
        </table>

        <p style="margin-bottom:10px;">
            Esta certificación se expide a solicitud del interesado el día
            <span class="cert-field">{{ $document->generado_at->format('d') }}</span> del mes de
            <span class="cert-field">{{ $document->generado_at->translatedFormat('F') }}</span> del año
            <span class="cert-field">{{ $document->generado_at->format('Y') }}</span>.
        </p>
    </div>

    <div class="firma-section">
        <div class="firma-col">
            <div class="sello-box">
                <div class="sello-label">{{ $cuenta->nombreBanco() }}</div>
                <div style="font-size:8px; color:#64748b; margin-top:3px;">Oficina Principal</div>
            </div>
        </div>
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Gerente de Cuenta</div>
            <div style="font-size:9px; color:#334155; margin-top:3px;">{{ $cuenta->nombreBanco() }}</div>
        </div>
    </div>

    <div class="footer">
        <div>Documento educativo sin validez legal. Generado por ContaEdu — Simulador de Negocios.</div>
    </div>
</div>
</body>
</html>
