<!DOCTYPE html>
<html lang="es" id="top">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ContaEdu — Plataforma Contable Educativa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ═══════════════════════════════════════════
           PALETA ContaEdu — tokens CSS reutilizables
           ═══════════════════════════════════════════ */
        :root {
            /* Verdes principales */
            --forest-950: #051a0f;
            --forest-900: #0a2e1a;
            --forest-800: #10472a;
            --forest-700: #165e36;
            --forest-600: #1e7d47;
            --forest-500: #279558;
            --forest-400: #3db872;
            --forest-200: #a7dfc0;
            --forest-100: #d4f0e1;
            --forest-50:  #edf8f2;

            /* Dorado / oro – acento */
            --gold-600:  #b8860b;
            --gold-500:  #d4a017;
            --gold-400:  #e8b828;
            --gold-300:  #f0cc5a;
            --gold-100:  #fdf3d0;
            --gold-50:   #fffcf0;

            /* Crema / neutros cálidos */
            --cream-50:  #fafaf7;
            --cream-100: #f3f2ec;
            --cream-200: #e8e6dc;
            --cream-300: #d5d2c3;

            /* Grises fríos */
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-300: #cbd5e1;
            --slate-100: #f1f5f9;
            --slate-50:  #f8fafc;

            /* Semánticos */
            --color-primary:   var(--forest-800);
            --color-accent:    var(--gold-500);
            --color-text:      var(--slate-900);
            --color-muted:     var(--slate-500);
            --color-surface:   #ffffff;
            --color-bg:        var(--cream-50);

            /* Radio */
            --r-sm:  8px;
            --r-md:  14px;
            --r-lg:  20px;
            --r-xl:  28px;
            --r-2xl: 36px;

            /* Sombras */
            --shadow-sm:  0 1px 4px rgba(10,46,26,.07);
            --shadow-md:  0 4px 20px rgba(10,46,26,.10);
            --shadow-lg:  0 12px 48px rgba(10,46,26,.15);
        }

        /* ── RESET ── */
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{
            font-family:'Plus Jakarta Sans',sans-serif;
            background:var(--color-bg);
            color:var(--color-text);
            line-height:1.6;
            overflow-x:hidden;
        }

        /* ── NAVBAR ── */
        .nav{
            background:rgba(250,250,247,.92);
            backdrop-filter:blur(16px);
            border-bottom:1px solid var(--cream-200);
            position:sticky;top:0;z-index:100;
            padding:0 1.5rem;
        }
        .nav-inner{
            max-width:1200px;margin:0 auto;
            display:flex;align-items:center;justify-content:space-between;
            height:68px;gap:1rem;
        }
        .brand{
            font-family:'Playfair Display',serif;
            font-size:1.35rem;font-weight:700;
            color:var(--forest-900);letter-spacing:-0.3px;
            text-decoration:none;
        }
        .brand em{font-style:normal;color:var(--gold-500)}
        .nav-links{display:flex;align-items:center;gap:12px}
        .nav-link{
            font-size:.875rem;font-weight:600;
            color:var(--slate-700);text-decoration:none;
            padding:8px 14px;border-radius:var(--r-sm);
            transition:color .2s,background .2s;
        }
        .nav-link:hover{color:var(--forest-800);background:var(--forest-50)}
        .nav-cta{
            background:var(--forest-800);color:#fff;
            padding:9px 20px;border-radius:var(--r-sm);
            font-size:.875rem;font-weight:700;
            text-decoration:none;
            border:2px solid var(--forest-800);
            transition:background .2s,border-color .2s;
        }
        .nav-cta:hover{background:var(--forest-700);border-color:var(--forest-700)}

        /* ── HERO ── */
        .hero{
            background:var(--forest-950);
            position:relative;overflow:hidden;
            padding:80px 1.5rem 100px;
        }
        /* Ledger lines — evocan el libro contable */
        .hero-ledger{
            position:absolute;inset:0;pointer-events:none;
            background-image:repeating-linear-gradient(
                180deg,
                transparent 0px,
                transparent 47px,
                rgba(255,255,255,.03) 47px,
                rgba(255,255,255,.03) 48px
            );
        }
        /* Orbes de luz */
        .hero::before{
            content:'';position:absolute;inset:0;
            background:
                radial-gradient(ellipse 55% 60% at 5% 50%,rgba(39,149,88,.18) 0%,transparent 70%),
                radial-gradient(ellipse 35% 50% at 90% 20%,rgba(212,160,23,.10) 0%,transparent 65%),
                radial-gradient(ellipse 40% 35% at 65% 90%,rgba(16,71,42,.40) 0%,transparent 60%);
        }
        /* Línea vertical dorada decorativa */
        .hero::after{
            content:'';
            position:absolute;left:0;top:0;bottom:0;width:4px;
            background:linear-gradient(180deg,transparent 0%,var(--gold-500) 30%,var(--gold-400) 70%,transparent 100%);
            opacity:.6;
        }
        .hero-inner{
            max-width:1200px;margin:0 auto;position:relative;z-index:1;
            display:grid;grid-template-columns:1fr 420px;gap:64px;align-items:center;
        }
        .hero-content{}
        .hero-tag{
            display:inline-flex;align-items:center;gap:8px;
            background:rgba(212,160,23,.12);border:1px solid rgba(212,160,23,.25);
            color:var(--gold-300);font-size:11px;font-weight:700;
            padding:5px 14px;border-radius:30px;letter-spacing:1.2px;
            text-transform:uppercase;margin-bottom:28px;
        }
        .hero h1{
            font-family:'Playfair Display',serif;
            font-size:clamp(2.6rem,6vw,4.4rem);
            font-weight:800;color:#fff;
            line-height:1.08;letter-spacing:-1px;
            margin-bottom:24px;max-width:720px;
        }
        .hero h1 em{
            font-style:italic;
            background:linear-gradient(90deg,var(--gold-400),var(--gold-300));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .hero-sub{
            color:var(--forest-200);font-size:1.1rem;
            max-width:580px;line-height:1.75;margin-bottom:52px;font-weight:400;
        }
        .hero-actions{display:flex;flex-wrap:wrap;gap:14px;align-items:center}
        .btn-gold{
            display:inline-flex;align-items:center;gap:8px;
            background:linear-gradient(135deg,var(--gold-500),var(--gold-400));
            color:var(--forest-950);font-weight:700;font-size:.9rem;
            padding:13px 26px;border-radius:var(--r-sm);
            text-decoration:none;transition:filter .2s,transform .15s;
            box-shadow:0 4px 20px rgba(212,160,23,.35);
        }
        .btn-gold:hover{filter:brightness(1.08);transform:translateY(-1px)}
        .btn-ghost{
            display:inline-flex;align-items:center;gap:8px;
            color:rgba(255,255,255,.75);font-weight:600;font-size:.875rem;
            padding:12px 22px;border-radius:var(--r-sm);
            text-decoration:none;border:1px solid rgba(255,255,255,.15);
            transition:background .2s,color .2s,border-color .2s;
        }
        .btn-ghost:hover{background:rgba(255,255,255,.08);color:#fff;border-color:rgba(255,255,255,.28)}
        /* Stats rápidos bajo el hero */
        .hero-stats{
            display:flex;flex-wrap:wrap;gap:0;
            margin-top:60px;padding-top:40px;
            border-top:1px solid rgba(255,255,255,.07);
        }
        .stat{padding:0 40px 0 0;margin-bottom:8px}
        .stat:first-child{padding-left:0}
        .stat-num{
            font-family:'Playfair Display',serif;
            font-size:2rem;font-weight:800;
            color:#fff;letter-spacing:-0.5px;line-height:1;
        }
        .stat-num span{color:var(--gold-400)}
        .stat-label{font-size:.78rem;color:var(--forest-200);margin-top:4px;font-weight:500;letter-spacing:.2px}

        /* ── FEATURES SECTION ── */
        .features{
            padding:96px 1.5rem;
            background:var(--cream-50);
            position:relative;
        }
        .features::before{
            content:'';position:absolute;top:0;left:0;right:0;height:1px;
            background:linear-gradient(90deg,transparent,var(--cream-300),transparent);
        }
        .section-inner{max-width:1200px;margin:0 auto}
        .section-tag{
            font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
            color:var(--forest-600);margin-bottom:12px;
        }
        .section-title{
            font-family:'Playfair Display',serif;
            font-size:clamp(1.8rem,3.5vw,2.6rem);
            font-weight:800;color:var(--forest-900);
            letter-spacing:-0.5px;line-height:1.15;margin-bottom:14px;
        }
        .section-sub{
            font-size:1rem;color:var(--slate-500);
            max-width:520px;line-height:1.75;margin-bottom:56px;
        }
        .features-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
            gap:1px;
            background:var(--cream-200);
            border:1px solid var(--cream-200);
            border-radius:var(--r-xl);
            overflow:hidden;
        }
        .feat-card{
            background:var(--cream-50);
            padding:36px 32px;
            position:relative;
            transition:background .25s;
            cursor:default;
        }
        .feat-card:hover{background:#fff}
        /* Línea de acento lateral */
        .feat-card::before{
            content:'';
            position:absolute;left:0;top:24px;bottom:24px;width:3px;
            border-radius:0 2px 2px 0;
            background:transparent;
            transition:background .25s;
        }
        .feat-card:hover::before{background:var(--gold-400)}
        .feat-icon{
            width:44px;height:44px;border-radius:var(--r-sm);
            display:flex;align-items:center;justify-content:center;
            margin-bottom:18px;flex-shrink:0;
        }
        .feat-icon svg{width:22px;height:22px;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
        .feat-tag{
            font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
            margin-bottom:8px;display:block;
        }
        .feat-card h3{
            font-family:'Playfair Display',serif;
            font-size:1.05rem;font-weight:700;
            color:var(--forest-900);margin-bottom:10px;line-height:1.3;
        }
        .feat-card p{font-size:.875rem;color:var(--slate-500);line-height:1.72}

        /* Variantes de color por feature */
        .fi-puc  .feat-icon{background:var(--forest-100)} .fi-puc  .feat-icon svg{stroke:var(--forest-700)} .fi-puc  .feat-tag{color:var(--forest-600)}
        .fi-fact .feat-icon{background:var(--gold-100)}   .fi-fact .feat-icon svg{stroke:var(--gold-600)}   .fi-fact .feat-tag{color:var(--gold-600)}
        .fi-comp .feat-icon{background:var(--forest-100)} .fi-comp .feat-icon svg{stroke:var(--forest-700)} .fi-comp .feat-tag{color:var(--forest-600)}
        .fi-part .feat-icon{background:var(--gold-100)}   .fi-part .feat-icon svg{stroke:var(--gold-600)}   .fi-part .feat-tag{color:var(--gold-600)}
        .fi-rep  .feat-icon{background:var(--forest-100)} .fi-rep  .feat-icon svg{stroke:var(--forest-700)} .fi-rep  .feat-tag{color:var(--forest-600)}
        .fi-aud  .feat-icon{background:var(--gold-100)}   .fi-aud  .feat-icon svg{stroke:var(--gold-600)}   .fi-aud  .feat-tag{color:var(--gold-600)}

        /* ── ROLES SECTION ── */
        .roles{padding:80px 1.5rem;background:#fff;border-top:1px solid var(--cream-200)}
        .roles-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;margin-top:48px}
        .role-card{
            border-radius:var(--r-lg);padding:36px 28px;
            position:relative;overflow:hidden;
            transition:transform .25s,box-shadow .25s;
            border:1px solid var(--cream-200);
        }
        .role-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
        .role-card-student{background:linear-gradient(145deg,var(--forest-950),var(--forest-800))}
        .role-card-teacher{background:linear-gradient(145deg,var(--gold-600),var(--gold-400))}
        /* Decoración geométrica */
        .role-card::after{
            content:'';position:absolute;
            width:160px;height:160px;border-radius:50%;
            bottom:-60px;right:-40px;
            border:2px solid currentColor;opacity:.08;
        }
        .role-card-student::after{color:#fff}
        .role-card-teacher::after{color:var(--forest-900)}
        .role-badge{
            display:inline-block;font-size:10px;font-weight:700;
            letter-spacing:1.2px;text-transform:uppercase;
            padding:4px 10px;border-radius:20px;margin-bottom:20px;
        }
        .role-card-student .role-badge{background:rgba(255,255,255,.12);color:rgba(255,255,255,.7)}
        .role-card-teacher .role-badge{background:rgba(10,46,26,.15);color:var(--forest-900)}
        .role-card h3{
            font-family:'Playfair Display',serif;
            font-size:1.35rem;font-weight:800;margin-bottom:12px;
        }
        .role-card-student h3{color:#fff}
        .role-card-teacher h3{color:var(--forest-900)}
        .role-card p{font-size:.875rem;line-height:1.72;margin-bottom:28px}
        .role-card-student p{color:var(--forest-200)}
        .role-card-teacher p{color:var(--forest-800)}
        .role-btn{
            display:inline-flex;align-items:center;gap:8px;
            padding:10px 20px;border-radius:var(--r-sm);
            font-size:.85rem;font-weight:700;text-decoration:none;
            transition:filter .2s,transform .15s;
        }
        .role-btn:hover{filter:brightness(1.08);transform:translateX(2px)}
        .btn-student{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)}
        .btn-student:hover{background:rgba(255,255,255,.2)}
        .btn-teacher{background:var(--forest-900);color:#fff}

        /* ── CTA SECTION ── */
        .cta-section{
            padding:80px 1.5rem;
            background:var(--cream-100);
            border-top:1px solid var(--cream-200);
            text-align:center;
        }
        .cta-box{
            max-width:580px;margin:0 auto;
        }
        .cta-box h2{
            font-family:'Playfair Display',serif;
            font-size:clamp(1.7rem,3vw,2.2rem);
            font-weight:800;color:var(--forest-900);
            letter-spacing:-.4px;margin-bottom:14px;
        }
        .cta-box p{color:var(--slate-500);font-size:1rem;margin-bottom:32px;line-height:1.72}
        .btn-primary{
            display:inline-flex;align-items:center;gap:8px;
            background:var(--forest-800);color:#fff;
            padding:14px 32px;border-radius:var(--r-sm);
            font-size:.9rem;font-weight:700;text-decoration:none;
            box-shadow:0 4px 16px rgba(10,46,26,.25);
            transition:background .2s,transform .15s,box-shadow .2s;
        }
        .btn-primary:hover{background:var(--forest-700);transform:translateY(-1px);box-shadow:0 8px 28px rgba(10,46,26,.3)}

        /* ── FOOTER ── */
        footer{
            background:var(--forest-950);
            border-top:1px solid rgba(255,255,255,.06);
            padding:32px 1.5rem;
        }
        .foot-inner{
            max-width:1200px;margin:0 auto;
            display:flex;flex-wrap:wrap;align-items:center;
            justify-content:space-between;gap:16px;
        }
        .foot-brand{
            font-family:'Playfair Display',serif;
            font-size:1.1rem;font-weight:700;color:#fff;
        }
        .foot-brand em{font-style:normal;color:var(--gold-400)}
        .foot-text{font-size:.8rem;color:rgba(255,255,255,.35);text-align:center}
        .foot-year{font-size:.8rem;color:rgba(255,255,255,.25)}

        /* ── HERO VISUAL (SVG ledger panel) ── */
        .hero-visual{
            position:relative;flex-shrink:0;
            animation:fadeUp .8s .4s ease both;
        }
        .ledger-panel{
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.09);
            border-radius:16px;overflow:hidden;
            backdrop-filter:blur(4px);
        }
        /* glow dorado sutil detrás del panel */
        .hero-visual::before{
            content:'';position:absolute;
            inset:-20px;border-radius:24px;
            background:radial-gradient(ellipse 70% 60% at 50% 50%,rgba(212,160,23,.08) 0%,transparent 70%);
            pointer-events:none;
        }

        /* ── BACK TO TOP ── */
        .back-top{
            position:fixed;bottom:28px;right:24px;z-index:200;
            width:44px;height:44px;border-radius:50%;
            background:var(--forest-800);
            border:2px solid var(--gold-500);
            display:flex;align-items:center;justify-content:center;
            cursor:pointer;text-decoration:none;
            box-shadow:0 4px 20px rgba(10,46,26,.4);
            opacity:0;transform:translateY(12px);
            transition:opacity .3s,transform .3s,background .2s,box-shadow .2s;
            pointer-events:none;
        }
        .back-top.visible{opacity:1;transform:translateY(0);pointer-events:auto}
        .back-top:hover{background:var(--gold-500);box-shadow:0 6px 28px rgba(212,160,23,.4)}
        .back-top svg{width:18px;height:18px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;transition:stroke .2s}
        .back-top:hover svg{stroke:var(--forest-950)}

        @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .hero-inner > *{animation:fadeUp .65s ease both}
        .hero-tag{animation-delay:.05s}
        .hero h1{animation-delay:.12s}
        .hero-sub{animation-delay:.22s}
        .hero-actions{animation-delay:.32s}
        .hero-stats{animation-delay:.42s}

        /* ── RESPONSIVE ── */
        @media(max-width:1024px){
            .hero-inner{grid-template-columns:1fr;gap:48px}
            .hero-visual{max-width:480px}
        }

        @media(max-width:768px){
            /* Navbar */
            .nav-inner{height:auto;padding:12px 0;flex-wrap:wrap;gap:8px}
            .nav-link{display:none}
            .nav-cta{padding:8px 16px}

            /* Hero */
            .hero{padding:52px 1.25rem 64px}
            .hero h1{letter-spacing:-.5px}
            .hero-sub{font-size:1rem;margin-bottom:36px}
            .hero::after{display:none}
            .hero-stats{margin-top:36px;padding-top:24px;gap:8px}
            .stat{padding:0 20px 0 0}
            .stat-num{font-size:1.5rem}
            .hero-visual{display:none}

            /* Sections */
            .features{padding:60px 1.25rem}
            .roles{padding:52px 1.25rem}
            .cta-section{padding:52px 1.25rem}

            /* Features grid */
            .features-grid{grid-template-columns:1fr;border-radius:var(--r-lg)}
            .feat-card{padding:28px 24px}

            /* Roles */
            .roles-grid{grid-template-columns:1fr}

            /* Back to top */
            .back-top{bottom:18px;right:16px;width:40px;height:40px}
        }

        @media(max-width:480px){
            .nav-link:last-child{display:none}
            .hero-actions{flex-direction:column;align-items:flex-start}
            .btn-gold,.btn-ghost{width:100%;justify-content:center}
            .stat{padding:0 16px 0 0}
            .foot-inner{flex-direction:column;align-items:center;text-align:center;gap:10px}
        }

    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="nav">
    <div class="nav-inner">
        <a href="#" class="brand">Conta<em>Edu</em></a>
        <div class="nav-links">
            <a href="{{ route('student.login') }}" class="nav-link">Soy estudiante</a>
            <a href="{{ route('login') }}" class="nav-cta">Acceso docente</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-ledger"></div>
    <div class="hero-inner">
        <div class="hero-content">

            <div class="hero-tag">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Plataforma educativa · Colombia
            </div>

            <h1>Aprende contabilidad<br>como en la <em>empresa real.</em></h1>

            <p class="hero-sub">
                Simula el ciclo contable completo con el PUC colombiano. Facturación, compras, doble partida y reportes financieros en tu propia empresa virtual.
            </p>

            <div class="hero-actions">
                <a href="{{ route('student.login') }}" class="btn-gold">
                    Comenzar como estudiante
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('login') }}" class="btn-ghost">
                    Acceso para docentes
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-num">6<span>+</span></div>
                    <div class="stat-label">Clases del PUC precargadas</div>
                </div>
                <div class="stat">
                    <div class="stat-num">13<span>+</span></div>
                    <div class="stat-label">Módulos del ciclo contable</div>
                </div>
                <div class="stat">
                    <div class="stat-num">100<span>%</span></div>
                    <div class="stat-label">Normativa colombiana</div>
                </div>
                <div class="stat">
                    <div class="stat-num">0<span> $</span></div>
                    <div class="stat-label">Para estudiantes SENA</div>
                </div>
            </div>

        </div><!-- /.hero-content -->

        <!-- SVG ILUSTRACIÓN — Libro Mayor estilo editorial -->
        <div class="hero-visual">
            <div class="ledger-panel">
                <svg viewBox="0 0 400 480" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:auto">

                    <!-- Panel header — barra dorada -->
                    <rect x="0" y="0" width="400" height="42" fill="#10472a"/>
                    <rect x="0" y="0" width="4" height="42" fill="#d4a017"/>
                    <text x="18" y="16" font-family="'Plus Jakarta Sans',sans-serif" font-size="9" fill="#a7dfc0" font-weight="600" letter-spacing="1.5">LIBRO MAYOR · EMPRESA VIRTUAL S.A.S.</text>
                    <text x="18" y="30" font-family="'Plus Jakarta Sans',sans-serif" font-size="9" fill="rgba(167,223,192,.5)" letter-spacing=".5">Período: Enero – Diciembre · PUC Colombia</text>
                    <!-- ícono candado verde -->
                    <circle cx="380" cy="21" r="11" fill="rgba(255,255,255,.06)"/>
                    <text x="380" y="25" font-family="sans-serif" font-size="11" text-anchor="middle" fill="#3db872">✓</text>

                    <!-- Encabezados de columna -->
                    <rect x="0" y="42" width="400" height="24" fill="rgba(255,255,255,.035)"/>
                    <line x1="0" y1="42" x2="400" y2="42" stroke="rgba(255,255,255,.08)" stroke-width="1"/>
                    <text x="12"  y="58" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.35)" font-weight="700" letter-spacing=".8">CÓD.</text>
                    <text x="72"  y="58" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.35)" font-weight="700" letter-spacing=".8">CUENTA</text>
                    <text x="248" y="58" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.35)" font-weight="700" letter-spacing=".8" text-anchor="end">DÉBITO</text>
                    <text x="318" y="58" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.35)" font-weight="700" letter-spacing=".8" text-anchor="end">CRÉDITO</text>
                    <text x="388" y="58" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.35)" font-weight="700" letter-spacing=".8" text-anchor="end">SALDO</text>

                    <!-- Separadores verticales -->
                    <line x1="64"  y1="66" x2="64"  y2="360" stroke="rgba(255,255,255,.05)" stroke-width="1"/>
                    <line x1="256" y1="66" x2="256" y2="360" stroke="rgba(255,255,255,.05)" stroke-width="1"/>
                    <line x1="326" y1="66" x2="326" y2="360" stroke="rgba(255,255,255,.05)" stroke-width="1"/>

                    <!-- FILAS DE DATOS -->
                    <!-- Clase 1 header -->
                    <rect x="0" y="66" width="400" height="20" fill="rgba(39,149,88,.12)"/>
                    <text x="12"  y="80" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="#3db872" font-weight="700">1</text>
                    <text x="72"  y="80" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="#3db872" font-weight="700">ACTIVO</text>

                    <!-- Fila 1105 -->
                    <rect x="0" y="86" width="400" height="19" fill="transparent"/>
                    <rect x="0" y="86" width="400" height="19" fill="rgba(255,255,255,.0)" class="row-hover"/>
                    <text x="12"  y="99" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">1105</text>
                    <text x="72"  y="99" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.75)">Caja general</text>
                    <text x="248" y="99" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">4.800.000</text>
                    <text x="318" y="99" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">—</text>
                    <text x="388" y="99" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#3db872"              text-anchor="end">4.800.000</text>
                    <line x1="0" y1="105" x2="400" y2="105" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Fila 1110 -->
                    <text x="12"  y="119" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">1110</text>
                    <text x="72"  y="119" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.75)">Bancos</text>
                    <text x="248" y="119" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">18.350.000</text>
                    <text x="318" y="119" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">—</text>
                    <text x="388" y="119" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#3db872"              text-anchor="end">18.350.000</text>
                    <line x1="0" y1="125" x2="400" y2="125" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Fila 1305 — resaltada dorada (cartera) -->
                    <rect x="0" y="125" width="400" height="19" fill="rgba(212,160,23,.07)"/>
                    <text x="12"  y="139" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">1305</text>
                    <text x="72"  y="139" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#f0cc5a">Clientes · cartera</text>
                    <text x="248" y="139" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">9.200.000</text>
                    <text x="318" y="139" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">1.100.000</text>
                    <text x="388" y="139" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#f0cc5a"              text-anchor="end">8.100.000</text>
                    <line x1="0" y1="144" x2="400" y2="144" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Fila 1435 -->
                    <text x="12"  y="158" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">1435</text>
                    <text x="72"  y="158" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.75)">Mercancías</text>
                    <text x="248" y="158" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">5.600.000</text>
                    <text x="318" y="158" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">—</text>
                    <text x="388" y="158" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#3db872"              text-anchor="end">5.600.000</text>
                    <line x1="0" y1="163" x2="400" y2="163" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Clase 2 header -->
                    <rect x="0" y="163" width="400" height="20" fill="rgba(212,160,23,.08)"/>
                    <text x="12"  y="177" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="#d4a017" font-weight="700">2</text>
                    <text x="72"  y="177" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="#d4a017" font-weight="700">PASIVO</text>

                    <!-- Fila 2205 -->
                    <text x="12"  y="198" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">2205</text>
                    <text x="72"  y="198" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.75)">Proveedores</text>
                    <text x="248" y="198" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">—</text>
                    <text x="318" y="198" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">7.800.000</text>
                    <text x="388" y="198" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#e8b828"              text-anchor="end">(7.800.000)</text>
                    <line x1="0" y1="203" x2="400" y2="203" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Fila 2335 -->
                    <text x="12"  y="217" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">2335</text>
                    <text x="72"  y="217" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.75)">Costos y gastos × pagar</text>
                    <text x="248" y="217" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">—</text>
                    <text x="318" y="217" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">2.100.000</text>
                    <text x="388" y="217" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#e8b828"              text-anchor="end">(2.100.000)</text>
                    <line x1="0" y1="222" x2="400" y2="222" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Clase 4 header -->
                    <rect x="0" y="222" width="400" height="20" fill="rgba(39,149,88,.12)"/>
                    <text x="12"  y="236" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="#3db872" font-weight="700">4</text>
                    <text x="72"  y="236" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="#3db872" font-weight="700">INGRESOS</text>

                    <!-- Fila 4135 -->
                    <text x="12"  y="258" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.55)">4135</text>
                    <text x="72"  y="258" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.75)">Ingresos por servicios</text>
                    <text x="248" y="258" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.4)"  text-anchor="end">—</text>
                    <text x="318" y="258" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.65)" text-anchor="end">22.600.000</text>
                    <text x="388" y="258" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="#3db872"              text-anchor="end">22.600.000</text>
                    <line x1="0" y1="263" x2="400" y2="263" stroke="rgba(255,255,255,.04)" stroke-width="1"/>

                    <!-- Línea de doble total -->
                    <line x1="0" y1="278" x2="400" y2="278" stroke="rgba(255,255,255,.12)" stroke-width="1"/>
                    <line x1="0" y1="281" x2="400" y2="281" stroke="rgba(255,255,255,.06)" stroke-width="1"/>

                    <!-- TOTALES -->
                    <rect x="0" y="281" width="400" height="24" fill="rgba(255,255,255,.03)"/>
                    <text x="72"  y="297" font-family="'Plus Jakarta Sans',sans-serif" font-size="8.5" fill="rgba(255,255,255,.7)" font-weight="700">TOTALES</text>
                    <text x="248" y="297" font-family="'Plus Jakarta Sans',sans-serif" font-size="9"   fill="#fff"   font-weight="700" text-anchor="end">37.950.000</text>
                    <text x="318" y="297" font-family="'Plus Jakarta Sans',sans-serif" font-size="9"   fill="#fff"   font-weight="700" text-anchor="end">33.600.000</text>
                    <!-- badge cuadre OK -->
                    <rect x="336" y="286" width="54" height="14" rx="7" fill="rgba(61,184,114,.2)"/>
                    <text x="363" y="296" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="#3db872" font-weight="700" text-anchor="middle">✓ CUADRE</text>

                    <!-- MINI CHART — barras de ingresos vs egresos -->
                    <line x1="0" y1="305" x2="400" y2="305" stroke="rgba(255,255,255,.07)" stroke-width="1"/>
                    <text x="12" y="320" font-family="'Plus Jakarta Sans',sans-serif" font-size="8" fill="rgba(255,255,255,.3)" font-weight="700" letter-spacing=".8">EVOLUCIÓN MENSUAL (COP)</text>

                    <!-- Barras ingresos (verde) -->
                    <rect x="20"  y="370" width="14" height="30" rx="3" fill="rgba(39,149,88,.35)"/>
                    <rect x="44"  y="358" width="14" height="42" rx="3" fill="rgba(39,149,88,.45)"/>
                    <rect x="68"  y="350" width="14" height="50" rx="3" fill="rgba(39,149,88,.55)"/>
                    <rect x="92"  y="342" width="14" height="58" rx="3" fill="rgba(39,149,88,.65)"/>
                    <rect x="116" y="338" width="14" height="62" rx="3" fill="rgba(39,149,88,.75)"/>
                    <rect x="140" y="330" width="14" height="70" rx="3" fill="rgba(39,149,88,.85)"/>
                    <!-- última barra resaltada -->
                    <rect x="164" y="322" width="14" height="78" rx="3" fill="#279558"/>
                    <rect x="164" y="322" width="14" height="4"  rx="2" fill="#3db872"/>

                    <!-- Barras egresos (dorado) -->
                    <rect x="20"  y="382" width="14" height="18" rx="3" fill="rgba(212,160,23,.4)"/>
                    <rect x="44"  y="378" width="14" height="22" rx="3" fill="rgba(212,160,23,.45)"/>
                    <rect x="68"  y="374" width="14" height="26" rx="3" fill="rgba(212,160,23,.5)"/>
                    <rect x="92"  y="370" width="14" height="30" rx="3" fill="rgba(212,160,23,.55)"/>
                    <rect x="116" y="366" width="14" height="34" rx="3" fill="rgba(212,160,23,.6)"/>
                    <rect x="140" y="364" width="14" height="36" rx="3" fill="rgba(212,160,23,.65)"/>
                    <rect x="164" y="358" width="14" height="42" rx="3" fill="#d4a017"/>

                    <!-- Eje X -->
                    <line x1="12" y1="400" x2="186" y2="400" stroke="rgba(255,255,255,.1)" stroke-width="1"/>
                    <text x="27"  y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.3)" text-anchor="middle">Jul</text>
                    <text x="51"  y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.3)" text-anchor="middle">Ago</text>
                    <text x="75"  y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.3)" text-anchor="middle">Sep</text>
                    <text x="99"  y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.3)" text-anchor="middle">Oct</text>
                    <text x="123" y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.3)" text-anchor="middle">Nov</text>
                    <text x="147" y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.3)" text-anchor="middle">Dic</text>
                    <text x="171" y="410" font-family="'Plus Jakarta Sans',sans-serif" font-size="7" fill="rgba(255,255,255,.5)" text-anchor="middle" font-weight="600">Ene</text>

                    <!-- Leyenda chart -->
                    <rect x="200" y="338" width="8" height="8" rx="2" fill="#279558"/>
                    <text x="212" y="345" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="rgba(255,255,255,.55)">Ingresos</text>
                    <rect x="200" y="354" width="8" height="8" rx="2" fill="#d4a017"/>
                    <text x="212" y="361" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="rgba(255,255,255,.55)">Egresos</text>

                    <!-- Utilidad neta callout -->
                    <rect x="196" y="370" width="190" height="38" rx="8" fill="rgba(39,149,88,.12)" stroke="rgba(39,149,88,.25)" stroke-width="1"/>
                    <text x="206" y="383" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="rgba(255,255,255,.5)" font-weight="600" letter-spacing=".6">UTILIDAD NETA PERÍODO</text>
                    <text x="206" y="398" font-family="'Plus Jakarta Sans',sans-serif" font-size="13"  fill="#3db872" font-weight="700">$4.350.000</text>

                    <!-- Pie del panel -->
                    <rect x="0" y="430" width="400" height="50" fill="rgba(10,46,26,.6)"/>
                    <line x1="0" y1="430" x2="400" y2="430" stroke="rgba(255,255,255,.07)" stroke-width="1"/>
                    <text x="12"  y="449" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="rgba(255,255,255,.35)" letter-spacing=".4">MODO SIMULADOR · Empresa Ejemplo S.A.S. · NIT 900.123.456-7</text>
                    <text x="12"  y="463" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="rgba(255,255,255,.2)"  letter-spacing=".3">Período 2024 · Generado por ContaEdu</text>
                    <!-- badge simulador -->
                    <rect x="320" y="435" width="68" height="16" rx="8" fill="rgba(212,160,23,.15)"/>
                    <text x="354" y="446" font-family="'Plus Jakarta Sans',sans-serif" font-size="7.5" fill="#d4a017" font-weight="700" text-anchor="middle" letter-spacing=".5">EDUCATIVO</text>

                </svg>
            </div><!-- /.ledger-panel -->
        </div><!-- /.hero-visual -->

    </div><!-- /.hero-inner -->
</section>

<!-- FEATURES -->
<section class="features">
    <div class="section-inner">
        <p class="section-tag">Funcionalidades</p>
        <h2 class="section-title">Todo el ciclo contable<br>en un solo lugar</h2>
        <p class="section-sub">Diseñado para estudiantes de administración y contabilidad colombiana. Cada módulo refleja la operación real de una pyme.</p>

        <div class="features-grid">

            <!-- PUC -->
            <div class="feat-card fi-puc">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M9 17H7A5 5 0 0 1 7 7h2"/><path d="M15 7h2a5 5 0 1 1 0 10h-2"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                </div>
                <span class="feat-tag">PUC Colombiano</span>
                <h3>Plan Único de Cuentas precargado</h3>
                <p>Todas las clases 1 a 6 listas para usar. Agrega subcuentas auxiliares específicas para tu empresa ficticia.</p>
            </div>

            <!-- Facturación -->
            <div class="feat-card fi-fact">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <span class="feat-tag">Facturación completa</span>
                <h3>Facturas, notas crédito y recibos</h3>
                <p>Emite documentos de venta y recibe las facturas de tus proveedores. Los asientos contables se generan automáticamente.</p>
            </div>

            <!-- Compras -->
            <div class="feat-card fi-comp">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
                <span class="feat-tag">Ciclo de compras</span>
                <h3>Órdenes, facturas de proveedor y pagos</h3>
                <p>Control de inventario, IVA descontable y conciliación de cuentas por pagar siguiendo el flujo real de una empresa colombiana.</p>
            </div>

            <!-- Doble partida -->
            <div class="feat-card fi-part">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <span class="feat-tag">Doble partida</span>
                <h3>Asientos automáticos con validación en vivo</h3>
                <p>Cada operación genera su asiento al instante. El sistema valida que débitos = créditos y avisa si hay descuadres.</p>
            </div>

            <!-- Reportes -->
            <div class="feat-card fi-rep">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <span class="feat-tag">Reportes financieros</span>
                <h3>Libro diario, mayor, balance y P&G</h3>
                <p>Estado de resultados y balance general listos para exportar a PDF. Formato alineado con estándares colombianos.</p>
            </div>

            <!-- Auditoría -->
            <div class="feat-card fi-aud">
                <div class="feat-icon">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <span class="feat-tag">Auditoría docente</span>
                <h3>Supervisión y calificación en tiempo real</h3>
                <p>El docente accede a todas las empresas del grupo en modo solo lectura, califica módulos y compara el desempeño del salón.</p>
            </div>

        </div>
    </div>
</section>

<!-- ROLES -->
<section class="roles">
    <div class="section-inner">
        <p class="section-tag">¿Quién accede?</p>
        <h2 class="section-title">Dos perfiles,<br>una sola plataforma</h2>

        <div class="roles-grid">

            <!-- Estudiante -->
            <div class="role-card role-card-student">
                <span class="role-badge">Estudiante · SENA / Universidad</span>
                <h3>Tu empresa, tu práctica</h3>
                <p>Trabaja en tu propia empresa ficticia aislada. Practica el ciclo contable completo desde el registro inicial hasta los estados financieros, sin afectar a tus compañeros.</p>
                <a href="{{ route('student.login') }}" class="role-btn btn-student">
                    Ingresar como estudiante
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Docente -->
            <div class="role-card role-card-teacher">
                <span class="role-badge">Docente</span>
                <h3>Control total del grupo</h3>
                <p>Crea grupos, asigna empresas, audita el trabajo de cada estudiante y lanza eventos pedagógicos (ajustes, errores a corregir) directamente desde tu panel.</p>
                <a href="{{ route('login') }}" class="role-btn btn-teacher">
                    Acceso docente
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="cta-box">
        <h2>¿Listo para comenzar?</h2>
        <p>Ingresa con las credenciales que te proporcionó tu docente y empieza a gestionar tu empresa contable virtual hoy mismo.</p>
        <a href="{{ route('student.login') }}" class="btn-primary">
            Ingresar al sistema
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="foot-inner">
        <span class="foot-brand">Conta<em>Edu</em></span>
        <p class="foot-text">Plataforma contable educativa · Colombia · PUC · Doble partida</p>
        <span class="foot-year">© {{ date('Y') }}</span>
    </div>
</footer>

<!-- BACK TO TOP -->
<a href="#top" class="back-top" id="backTop" aria-label="Volver arriba">
    <svg viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></svg>
</a>

<script>
    (function(){
        var btn = document.getElementById('backTop');
        window.addEventListener('scroll', function(){
            if(window.scrollY > 320){
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }, {passive:true});
    })();
</script>

</body>
</html>
