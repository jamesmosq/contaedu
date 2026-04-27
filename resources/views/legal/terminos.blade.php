<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Términos de Uso — ContaEdu</title>
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
    <h1>Términos y Condiciones de Uso</h1>
    <p class="meta">Última actualización: {{ date('d \d\e F \d\e Y') }} · Vigente desde el 1 de enero de 2025</p>

    <div class="highlight">
        <p>Al acceder o usar ContaEdu usted acepta estos Términos en su totalidad. Si no está de acuerdo, no debe usar la plataforma. El uso continuado después de cambios implica aceptación de los mismos.</p>
    </div>

    <h2>1. Descripción del servicio</h2>
    <p><strong>ContaEdu</strong> es una plataforma de software educativo que permite a estudiantes de contabilidad practicar el ciclo contable completo en un entorno virtual simulado. El servicio incluye módulos de facturación, compras, libro diario, plan de cuentas, conciliación bancaria, activos fijos y reportes financieros, entre otros.</p>
    <p>ContaEdu se presta como un servicio SaaS (Software como Servicio) bajo un modelo de suscripción institucional. Los datos contables generados dentro de la plataforma son datos de práctica educativa y <strong>no tienen validez fiscal ni legal</strong>.</p>

    <h2>2. Usuarios y roles</h2>
    <h3>2.1 Instituciones educativas y coordinadores</h3>
    <p>Las instituciones acceden al servicio mediante un contrato de suscripción. El coordinador designado es responsable de la administración de docentes y estudiantes dentro de la plataforma y del cumplimiento de estos términos por parte de sus usuarios.</p>

    <h3>2.2 Docentes</h3>
    <p>Los docentes tienen acceso al panel de seguimiento, evaluación de estudiantes, banco de ejercicios y herramientas pedagógicas. Son responsables del uso adecuado de la plataforma en el contexto de su labor educativa.</p>

    <h3>2.3 Estudiantes</h3>
    <p>Los estudiantes acceden con credenciales proporcionadas por su docente. Cada estudiante dispone de un entorno de práctica aislado e independiente. Las credenciales son de uso personal e intransferible.</p>

    <h2>3. Obligaciones del usuario</h2>
    <p>Al usar ContaEdu, el usuario se compromete a:</p>
    <ul>
        <li>Usar la plataforma exclusivamente con fines educativos legítimos</li>
        <li>No compartir sus credenciales de acceso con terceros</li>
        <li>No intentar acceder a los entornos de otros usuarios sin autorización</li>
        <li>No cargar, transmitir o almacenar contenido ilegal, ofensivo o que viole derechos de terceros</li>
        <li>No realizar ingeniería inversa, descompilar ni intentar extraer el código fuente de la plataforma</li>
        <li>No usar la plataforma para procesar datos fiscales reales o emitir documentos con validez tributaria</li>
        <li>Notificar de inmediato cualquier uso no autorizado de su cuenta</li>
    </ul>

    <h2>4. Propiedad intelectual</h2>
    <p>Todos los derechos de propiedad intelectual sobre la plataforma ContaEdu —incluyendo el software, diseño, marca, logotipos, banco de ejercicios y materiales pedagógicos— son propiedad exclusiva de sus desarrolladores.</p>
    <p>Los datos contables generados por los estudiantes dentro de la plataforma son propiedad del usuario que los creó, aunque ContaEdu puede usarlos de forma anonimizada y agregada para mejorar el servicio.</p>

    <h2>5. Disponibilidad del servicio</h2>
    <p>ContaEdu procura mantener una disponibilidad del servicio del <strong>99%</strong> mensual. Sin embargo, no garantiza disponibilidad ininterrumpida y se reserva el derecho de realizar mantenimientos programados, informados con antelación a través de la plataforma.</p>
    <p>ContaEdu no es responsable de interrupciones causadas por fallas de terceros (proveedores de internet, servicios en la nube, fuerza mayor).</p>

    <h2>6. Limitación de responsabilidad</h2>
    <p>ContaEdu es una herramienta de práctica educativa. En ningún caso será responsable por:</p>
    <ul>
        <li>Decisiones tomadas con base en los datos de práctica generados en la plataforma</li>
        <li>Pérdida de datos por causas fuera de su control (fuerza mayor, ataques externos)</li>
        <li>Daños indirectos, lucro cesante o daño emergente derivados del uso o imposibilidad de uso del servicio</li>
        <li>Errores en los datos ingresados por los usuarios</li>
    </ul>

    <h2>7. Suspensión y terminación</h2>
    <p>ContaEdu podrá suspender o cancelar el acceso de un usuario o institución sin previo aviso en caso de:</p>
    <ul>
        <li>Incumplimiento de estos Términos</li>
        <li>Falta de pago de la suscripción institucional</li>
        <li>Uso fraudulento o actividad que ponga en riesgo la seguridad de otros usuarios</li>
    </ul>
    <p>Al terminar la relación contractual, los datos de práctica estarán disponibles para exportación durante <strong>30 días calendario</strong>, transcurridos los cuales podrán ser eliminados definitivamente.</p>

    <h2>8. Privacidad y protección de datos</h2>
    <p>El tratamiento de datos personales se rige por nuestra <a href="{{ url('/privacidad') }}">Política de Privacidad</a>, la cual hace parte integral de estos Términos.</p>

    <h2>9. Ley aplicable y jurisdicción</h2>
    <p>Estos Términos se rigen por las leyes de la <strong>República de Colombia</strong>. Cualquier controversia derivada de su interpretación o ejecución será resuelta ante los jueces competentes de Colombia, con sede en la ciudad donde ContaEdu tenga su domicilio principal.</p>

    <h2>10. Contacto</h2>
    <p>Para consultas sobre estos Términos puede escribir a: <a href="mailto:legal@contaedu.app">legal@contaedu.app</a></p>
</div>

<footer>
    <p>© {{ date('Y') }} ContaEdu · <a href="{{ url('/terminos') }}">Términos de Uso</a> · <a href="{{ url('/privacidad') }}">Privacidad</a> · Colombia</p>
</footer>

</body>
</html>
