<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ContaEdu — Plataforma Contable Educativa</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-slate-800">

    {{-- Navbar --}}
    <nav class="border-b border-slate-100 bg-white/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <span class="text-brand-900 font-bold text-xl">Conta<span class="text-accent-600">Edu</span></span>
            <div class="flex items-center gap-3">
                <a href="{{ route('student.login') }}" class="text-sm text-slate-600 hover:text-brand-800 transition font-medium">
                    Soy estudiante
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    Acceso docente
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 text-white">
        <div class="max-w-6xl mx-auto px-6 py-24 lg:py-32">
            <div class="max-w-3xl">
                <span class="inline-block px-3 py-1 bg-accent-500/20 text-accent-300 text-xs font-semibold rounded-full uppercase tracking-widest mb-6">
                    Plataforma educativa · Colombia
                </span>
                <h1 class="text-4xl lg:text-6xl font-extrabold leading-tight mb-6">
                    Aprende contabilidad<br>
                    <span class="text-accent-400">como en la empresa real.</span>
                </h1>
                <p class="text-brand-200 text-lg lg:text-xl mb-10 leading-relaxed">
                    Simula el ciclo contable completo con el PUC colombiano. Facturación, compras, doble partida y reportes financieros en tu propia empresa virtual.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('student.login') }}" class="px-6 py-3 bg-accent-500 hover:bg-accent-400 text-white font-semibold rounded-lg transition text-sm">
                        Comenzar como estudiante →
                    </a>
                    <a href="{{ route('login') }}" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-lg transition text-sm border border-white/20">
                        Acceso para docentes
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="py-20 bg-slate-50">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-slate-800 mb-3">Todo el ciclo contable en un solo lugar</h2>
                <p class="text-slate-500 text-lg">Diseñado para estudiantes de administración y contabilidad colombiana.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['PUC Colombiano', 'Plan Único de Cuentas precargado con todas las clases 1 a 6. Agrega subcuentas auxiliares según tu empresa.', 'bg-brand-100 text-brand-700'],
                    ['Facturación completa', 'Emite facturas de venta, notas crédito y recibos de caja. Asientos contables generados automáticamente.', 'bg-accent-100 text-accent-700'],
                    ['Ciclo de compras', 'Órdenes de compra, facturas de proveedor y pagos. Control de inventario e IVA descontable.', 'bg-brand-100 text-brand-700'],
                    ['Doble partida', 'Cada operación genera su asiento automáticamente. Validación de débitos = créditos en tiempo real.', 'bg-accent-100 text-accent-700'],
                    ['Reportes financieros', 'Libro diario, mayor, balance de comprobación, estado de resultados y balance general. Exporta a PDF.', 'bg-brand-100 text-brand-700'],
                    ['Auditoría docente', 'El docente supervisa todas las empresas del grupo, califica módulos y compara el desempeño en tiempo real.', 'bg-accent-100 text-accent-700'],
                ] as [$title, $desc, $badge])
                    <div class="bg-white rounded-xl border border-slate-200 p-6 hover:shadow-md transition">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold {{ $badge }} mb-4">{{ $title }}</span>
                        <p class="text-slate-600 text-sm leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-16 bg-white border-t border-slate-100">
        <div class="max-w-2xl mx-auto px-6 text-center">
            <h2 class="text-2xl font-bold text-slate-800 mb-3">¿Listo para comenzar?</h2>
            <p class="text-slate-500 mb-8">Ingresa con las credenciales que te proporcionó tu docente.</p>
            <a href="{{ route('student.login') }}" class="px-8 py-3 bg-brand-800 text-white font-semibold rounded-lg hover:bg-brand-700 transition">
                Ingresar al sistema →
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-50 border-t border-slate-100 py-8">
        <div class="max-w-6xl mx-auto px-6 flex items-center justify-between text-sm text-slate-400">
            <span>ContaEdu © {{ date('Y') }}</span>
            <span>Plataforma contable educativa · Colombia</span>
        </div>
    </footer>

</body>
</html>
