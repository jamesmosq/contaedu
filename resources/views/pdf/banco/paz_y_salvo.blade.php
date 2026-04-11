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
        .meta-table { margin: 0 0 0 auto; border: 1px solid #d4f0e1; }
        .meta-table th { background: #edf8f2; padding: 3px 8px; font-size: 9px; color: #10472a; font-weight: 600; }
        .meta-table td { padding: 3px 8px; font-size: 10px; }

        .paz-banner { background: #10472a; color: #fff; text-align: center; padding: 18px; margin: 18px 0; border-radius: 4px; }
        .paz-banner h2 { font-size: 22px; font-weight: bold; letter-spacing: 0.05em; text-transform: uppercase; }
        .paz-banner p { font-size: 11px; color: #a7f3d0; margin-top: 6px; }

        .paz-body { line-height: 1.8; font-size: 11px; margin: 14px 0; }
        .paz-body p { margin-bottom: 12px; }
        .cert-field { display: inline-block; border-bottom: 1px solid #10472a; min-width: 120px; font-weight: bold; color: #10472a; }
        .info-box { border: 2px solid #10472a; background: #edf8f2; padding: 12px 16px; margin: 16px 0; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 5px 8px; font-size: 10.5px; border-bottom: 1px solid #d4f0e1; }
        .info-box tr:last-child td { border-bottom: none; }
        .label { font-weight: bold; color: #10472a; width: 45%; }
        .value { color: #1e293b; }

        .firma-section { margin-top: 40px; display: table; width: 100%; }
        .firma-col { display: table-cell; width: 50%; text-align: center; }
        .firma-line { border-top: 1px solid #334155; width: 200px; margin: 0 auto 5px; }
        .firma-label { font-size: 9.5px; color: #475569; }
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
            <div class="doc-title">Paz y Salvo Bancario</div>
            <div style="font-size:12px; font-weight:bold; color:#1e293b; margin:3px 0;">No. {{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</div>
            <table class="meta-table">
                <tr><th>Fecha</th><td>{{ $document->generado_at->format('d/m/Y') }}</td></tr>
                <tr><th>Ciudad</th><td>{{ $config?->city ?? 'Colombia' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="paz-banner">
        <h2>PAZ Y SALVO</h2>
        <p>Se certifica que la empresa no registra obligaciones pendientes con esta entidad</p>
    </div>

    <div class="paz-body">
        <p>
            El <strong>{{ $cuenta->nombreBanco() }}</strong> certifica que la empresa
            <span class="cert-field">{{ $config?->company_name ?? '—' }}</span>,
            identificada con NIT <span class="cert-field">{{ $config?->nit ?? '—' }}</span>,
            se encuentra <strong>A PAZ Y SALVO</strong> con esta entidad financiera,
            no registrando ningún tipo de obligación, deuda, saldo en mora ni compromiso pendiente de pago.
        </p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td class="label">Empresa</td>
                <td class="value">{{ $config?->company_name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">NIT</td>
                <td class="value">{{ $config?->nit ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Número de cuenta</td>
                <td class="value" style="font-family:'Courier New',monospace;">{{ $cuenta->account_number }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de cuenta</td>
                <td class="value">{{ ucfirst($cuenta->account_type) }}</td>
            </tr>
            <tr>
                <td class="label">Saldo al cierre</td>
                <td class="value" style="font-weight:bold; color:#10472a;">$0</td>
            </tr>
            <tr>
                <td class="label">Sobregiro pendiente</td>
                <td class="value" style="font-weight:bold; color:#10472a;">$0</td>
            </tr>
            <tr>
                <td class="label">Estado</td>
                <td class="value" style="color:#16a34a; font-weight:bold;">SIN OBLIGACIONES</td>
            </tr>
        </table>
    </div>

    <div class="paz-body">
        <p>
            Este documento se expide a solicitud del interesado el día
            <span class="cert-field">{{ $document->generado_at->format('d') }}</span> del mes de
            <span class="cert-field">{{ $document->generado_at->translatedFormat('F') }}</span>
            del año <span class="cert-field">{{ $document->generado_at->format('Y') }}</span>,
            en la ciudad de {{ $config?->city ?? 'Colombia' }}.
        </p>
    </div>

    <div class="firma-section">
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Oficial de Cumplimiento</div>
            <div style="font-size:9px; color:#334155; margin-top:3px;">{{ $cuenta->nombreBanco() }}</div>
        </div>
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Gerente General</div>
            <div style="font-size:9px; color:#334155; margin-top:3px;">{{ $cuenta->nombreBanco() }}</div>
        </div>
    </div>

    <div class="footer">
        <div>Documento educativo sin validez legal. Generado por ContaEdu — Simulador de Negocios.</div>
    </div>
</div>
</body>
</html>
