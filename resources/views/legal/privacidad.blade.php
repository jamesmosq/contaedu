<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Política de Privacidad — ContaEdu</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --forest-950: #051a0f;
            --forest-800: #10472a;
            --forest-700: #165e36;
            --forest-50:  #edf8f2;
            --gold-400:   #e8b828;
            --slate-900:  #0f172a;
            --slate-700:  #334155;
            --slate-500:  #64748b;
            --slate-100:  #f1f5f9;
            --cream-50:   #fafaf7;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--cream-50);
            color: var(--slate-900);
            line-height: 1.75;
        }
        nav {
            background: var(--forest-950);
            padding: 18px 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }
        .nav-brand em { font-style: normal; color: var(--gold-400); }
        .nav-back {
            font-size: .8rem;
            color: rgba(255,255,255,.5);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }
        .nav-back:hover { color: #fff; }
        .wrapper {
            max-width: 780px;
            margin: 0 auto;
            padding: 60px 1.5rem 80px;
        }
        .badge {
            display: inline-block;
            background: var(--forest-50);
            color: var(--forest-800);
            font-size: .75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 99px;
            border: 1px solid #c5e8d5;
            margin-bottom: 20px;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 800;
            color: var(--forest-800);
            line-height: 1.2;
            margin-bottom: 10px;
        }
        .meta {
            font-size: .85rem;
            color: var(--slate-500);
            margin-bottom: 48px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--forest-800);
            margin: 40px 0 12px;
        }
        h3 {
            font-size: .95rem;
            font-weight: 600;
            color: var(--slate-700);
            margin: 24px 0 8px;
        }
        p { color: var(--slate-700); margin-bottom: 14px; font-size: .95rem; }
        ul { padding-left: 20px; margin-bottom: 14px; }
        li { color: var(--slate-700); font-size: .95rem; margin-bottom: 6px; }
        .highlight {
            background: var(--forest-50);
            border-left: 3px solid var(--forest-800);
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            margin: 24px 0;
        }
        .highlight p { margin: 0; font-size: .9rem; }
        a { color: var(--forest-700); }
        footer {
            background: var(--forest-950);
            border-top: 1px solid rgba(255,255,255,.06);
            padding: 24px 1.5rem;
            text-align: center;
        }
        footer p { color: rgba(255,255,255,.3); font-size: .8rem; margin: 0; }
        footer a { color: rgba(255,255,255,.5); }
    </style>
</head>
<body>

<nav>
    <a href="{{ url('/') }}" class="nav-brand">Conta<em>Edu</em></a>
    <a href="{{ url('/') }}" class="nav-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver al inicio
    </a>
</nav>

<div class="wrapper">
    <span class="badge">Legal · Colombia</span>
    <h1>Política de Privacidad y Tratamiento de Datos Personales</h1>
    <p class="meta">Última actualización: {{ date('d \d\e F \d\e Y') }} · Vigente desde el 1 de enero de 2025</p>

    <div class="highlight">
        <p>En cumplimiento de la <strong>Ley 1581 de 2012</strong> (Habeas Data) y el <strong>Decreto 1377 de 2013</strong>, ContaEdu informa a sus usuarios la forma en que recopila, usa, almacena y protege sus datos personales.</p>
    </div>

    <h2>1. Responsable del tratamiento</h2>
    <p><strong>ContaEdu</strong> es la plataforma responsable del tratamiento de los datos personales recolectados a través de <strong>contaedu.app</strong>. Para consultas o ejercicio de derechos puede escribir a: <a href="mailto:privacidad@contaedu.app">privacidad@contaedu.app</a></p>

    <h2>2. Datos que recopilamos</h2>
    <h3>2.1 Datos proporcionados directamente</h3>
    <ul>
        <li>Nombre completo e identificación del usuario</li>
        <li>Correo electrónico institucional o personal</li>
        <li>Información de la institución educativa a la que pertenece</li>
        <li>Datos de acceso (usuario y contraseña cifrada)</li>
    </ul>
    <h3>2.2 Datos generados por el uso de la plataforma</h3>
    <ul>
        <li>Registros contables creados en el entorno de práctica (facturas, asientos, reportes)</li>
        <li>Actividad dentro de la plataforma (fecha y hora de acceso, módulos visitados)</li>
        <li>Métricas de rendimiento técnico (tiempos de carga, errores del sistema)</li>
    </ul>

    <h2>3. Finalidades del tratamiento</h2>
    <ul>
        <li>Prestar el servicio educativo de práctica contable en entorno virtual</li>
        <li>Permitir al docente el seguimiento y evaluación del progreso del estudiante</li>
        <li>Enviar comunicaciones relacionadas con el servicio (mantenimientos, actualizaciones)</li>
        <li>Mejorar la plataforma mediante análisis de uso agregado y anonimizado</li>
        <li>Cumplir obligaciones legales y contractuales</li>
    </ul>

    <h2>4. Derechos del titular</h2>
    <p>De conformidad con la Ley 1581 de 2012, el titular de los datos tiene derecho a:</p>
    <ul>
        <li><strong>Conocer</strong> los datos personales que ContaEdu tiene sobre usted</li>
        <li><strong>Actualizar y rectificar</strong> información inexacta o incompleta</li>
        <li><strong>Solicitar la supresión</strong> de datos cuando no exista obligación legal de conservarlos</li>
        <li><strong>Revocar la autorización</strong> otorgada para el tratamiento</li>
        <li><strong>Presentar quejas</strong> ante la Superintendencia de Industria y Comercio (SIC)</li>
    </ul>
    <p>Para ejercer estos derechos escriba a <a href="mailto:privacidad@contaedu.app">privacidad@contaedu.app</a> indicando su nombre completo, número de identificación y la solicitud específica. Respondemos en un plazo máximo de <strong>15 días hábiles</strong>.</p>

    <h2>5. Almacenamiento y seguridad</h2>
    <p>Los datos se almacenan en servidores ubicados en la nube con medidas de seguridad técnicas y organizativas que incluyen:</p>
    <ul>
        <li>Cifrado de contraseñas con algoritmo Bcrypt</li>
        <li>Transmisión de datos bajo protocolo HTTPS (TLS)</li>
        <li>Aislamiento por esquema de base de datos por estudiante (arquitectura multi-tenant)</li>
        <li>Acceso restringido al personal autorizado mediante control de roles</li>
        <li>Copias de seguridad periódicas de la información</li>
    </ul>

    <h2>6. Compartición de datos con terceros</h2>
    <p>ContaEdu <strong>no vende ni comparte</strong> datos personales con terceros con fines comerciales. Los datos pueden ser procesados por proveedores de infraestructura tecnológica (servidores en la nube, monitoreo de errores) que actúan como encargados del tratamiento bajo estrictos acuerdos de confidencialidad.</p>

    <h2>7. Cookies y tecnologías similares</h2>
    <p>La plataforma utiliza cookies de sesión estrictamente necesarias para mantener la autenticación del usuario. No se utilizan cookies de seguimiento publicitario ni se comparte información con redes de publicidad.</p>

    <h2>8. Retención de datos</h2>
    <p>Los datos se conservan durante el tiempo en que la institución educativa mantenga un contrato vigente con ContaEdu y hasta por <strong>dos (2) años adicionales</strong> tras su terminación, salvo obligación legal de mayor plazo o solicitud de supresión por parte del titular.</p>

    <h2>9. Menores de edad</h2>
    <p>ContaEdu es una plataforma dirigida a estudiantes de educación técnica, tecnológica y universitaria. El uso por parte de menores de 18 años requiere autorización expresa del padre, madre o tutor legal, otorgada a través de la institución educativa.</p>

    <h2>10. Modificaciones a esta política</h2>
    <p>ContaEdu se reserva el derecho de actualizar esta política. Los cambios sustanciales serán notificados a los usuarios a través de la plataforma con al menos <strong>10 días hábiles</strong> de antelación.</p>

    <h2>11. Vigencia de la autorización</h2>
    <p>Al registrarse o usar la plataforma, el usuario otorga su autorización para el tratamiento de datos conforme a esta política. Esta autorización puede ser revocada en cualquier momento mediante solicitud escrita.</p>
</div>

<footer>
    <p>© {{ date('Y') }} ContaEdu · <a href="{{ url('/terminos') }}">Términos de Uso</a> · <a href="{{ url('/privacidad') }}">Privacidad</a> · Colombia</p>
</footer>

</body>
</html>
