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

        .ref-body { line-height: 1.8; font-size: 11px; margin: 14px 0; }
        .ref-body p { margin-bottom: 12px; }
        .cert-field { display: inline-block; border-bottom: 1px solid #10472a; min-width: 120px; font-weight: bold; color: #10472a; }
        .info-box { border: 1px solid #d4f0e1; background: #edf8f2; padding: 10px 14px; margin: 14px 0; font-size: 10.5px; }
        .firma-section { margin-top: 36px; display: table; width: 100%; }
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
            <div class="doc-title">Referencia Bancaria</div>
            <div style="font-size:12px; font-weight:bold; color:#1e293b; margin:3px 0;">Ref. No. {{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</div>
            <table class="meta-table">
                <tr><th>Fecha</th><td>{{ $document->generado_at->format('d/m/Y') }}</td></tr>
                <tr><th>Ciudad</th><td>{{ $config?->city ?? 'Colombia' }}</td></tr>
            </table>
        </div>
    </div>

    <div class="ref-body">
        <p><strong>Señores:</strong><br>A quien corresponda</p>

        <p>
            Por medio de la presente, el <strong>{{ $cuenta->nombreBanco() }}</strong> se permite informar que la empresa
            <span class="cert-field">{{ $config?->company_name ?? '—' }}</span>,
            identificada con NIT <span class="cert-field">{{ $config?->nit ?? '—' }}</span>,
            es cliente de nuestra entidad desde el
            <span class="cert-field">{{ \Carbon\Carbon::parse($cuenta->fecha_apertura)->format('d/m/Y') }}</span>
            y mantiene con nosotros una cuenta de <strong>{{ $cuenta->account_type }}</strong>.
        </p>

        <div class="info-box">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="padding:4px 8px; font-weight:bold; color:#10472a; width:45%;">Tipo de producto</td>
                    <td style="padding:4px 8px;">Cuenta {{ ucfirst($cuenta->account_type) }}</td>
                </tr>
                <tr style="background:#fff;">
                    <td style="padding:4px 8px; font-weight:bold; color:#10472a;">Número de cuenta</td>
                    <td style="padding:4px 8px; font-family:'Courier New',monospace;">{{ $cuenta->account_number }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 8px; font-weight:bold; color:#10472a;">Comportamiento</td>
                    <td style="padding:4px 8px; color:#16a34a; font-weight:bold;">SATISFACTORIO</td>
                </tr>
                <tr style="background:#fff;">
                    <td style="padding:4px 8px; font-weight:bold; color:#10472a;">Calificación crediticia</td>
                    <td style="padding:4px 8px;">A — Riesgo normal</td>
                </tr>
            </table>
        </div>

        <p>
            Durante su relación con el banco, el cliente ha demostrado un comportamiento <strong>satisfactorio</strong>
            en el manejo de sus productos financieros. Esta referencia se expide a solicitud del interesado
            para los fines que este estime conveniente.
        </p>

        <p>
            Se expide en la ciudad de <span class="cert-field">{{ $config?->city ?? 'Colombia' }}</span>,
            a los <span class="cert-field">{{ $document->generado_at->format('d') }}</span> días del mes de
            <span class="cert-field">{{ $document->generado_at->translatedFormat('F Y') }}</span>.
        </p>
    </div>

    <div class="firma-section">
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Director Comercial</div>
            <div style="font-size:9px; color:#334155; margin-top:3px;">{{ $cuenta->nombreBanco() }}</div>
        </div>
        <div class="firma-col">
            <div class="firma-line"></div>
            <div class="firma-label">Gerente Regional</div>
            <div style="font-size:9px; color:#334155; margin-top:3px;">{{ $cuenta->nombreBanco() }}</div>
        </div>
    </div>

    <div class="footer">
        <div>Documento educativo sin validez legal. Generado por ContaEdu — Simulador de Negocios.</div>
    </div>
</div>
</body>
</html>
