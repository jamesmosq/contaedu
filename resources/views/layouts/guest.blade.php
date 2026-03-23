<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ContaEdu') }}</title>

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">

            {{-- ── Panel izquierdo — marca ─────────────────────────────── --}}
            <div class="hidden lg:flex lg:w-[480px] xl:w-[540px] bg-forest-950 flex-col justify-between p-12 shrink-0 relative overflow-hidden">

                {{-- Decoración de fondo --}}
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-32 -left-32 w-96 h-96 bg-forest-800 rounded-full opacity-30 blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 w-80 h-80 bg-gold-600 rounded-full opacity-10 blur-3xl"></div>
                </div>

                {{-- Logo --}}
                <div class="relative">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-gold-500 rounded-xl flex items-center justify-center shadow-gold">
                            <svg class="w-5 h-5 text-forest-950" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 0 0-.788 0l-7 3a1 1 0 0 0 0 1.84L5.25 8.051a.999.999 0 0 1 .356-.257l4-1.714a1 1 0 1 1 .788 1.838L7.667 9.088l1.94.831a1 1 0 0 0 .787 0l7-3a1 1 0 0 0 0-1.838l-7-3ZM3.31 9.397L5 10.12v4.102a8.969 8.969 0 0 0-1.05-.174 1 1 0 0 1-.89-.89 11.115 11.115 0 0 1 .25-3.762ZM9.3 16.573A9.026 9.026 0 0 0 7 14.935v-3.957l1.818.78a3 3 0 0 0 2.364 0l5.508-2.361a11.026 11.026 0 0 1 .25 3.762 1 1 0 0 1-.89.89 8.968 8.968 0 0 0-5.35 2.524 1 1 0 0 1-1.4 0ZM6 18a1 1 0 0 0 1-1v-2.065a8.935 8.935 0 0 0-2-.712V17a1 1 0 0 0 1 1Z"/>
                            </svg>
                        </div>
                        <span class="font-display text-2xl font-bold text-white">Conta<span class="text-gold-400">Edu</span></span>
                    </div>
                </div>

                {{-- Tagline central --}}
                <div class="relative">
                    <h1 class="font-display text-4xl xl:text-5xl font-bold text-white leading-tight mb-5">
                        Aprende contabilidad<br>
                        <em class="not-italic text-gold-400">como en la empresa real.</em>
                    </h1>
                    <p class="text-forest-300 text-lg leading-relaxed mb-8">
                        Simula el ciclo contable completo con el PUC colombiano: facturación, compras, asientos de doble partida y reportes financieros.
                    </p>

                    {{-- Features --}}
                    <div class="space-y-3">
                        @foreach([
                            ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'text' => 'PUC colombiano preconfigurado'],
                            ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'text' => 'Asientos automáticos de doble partida'],
                            ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'text' => 'Reportes: balance, estado de resultados, cartera'],
                            ['icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'text' => 'Empresa virtual aislada por estudiante'],
                        ] as $f)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gold-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                                </svg>
                                <span class="text-forest-200 text-sm">{{ $f['text'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="relative flex items-center gap-4 text-forest-500 text-xs">
                    <a href="{{ url('/') }}" class="flex items-center gap-1.5 text-forest-400 hover:text-gold-400 transition font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Inicio
                    </a>
                    <span>·</span>
                    <span>Colombia</span>
                    <span>·</span>
                    <span>NIIF / PUC</span>
                </div>
            </div>

            {{-- ── Panel derecho — formulario ──────────────────────────── --}}
            <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-10 bg-cream-50">
                <div class="w-full max-w-md">

                    {{-- Logo móvil --}}
                    <div class="lg:hidden mb-8 text-center">
                        <div class="inline-flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 bg-forest-800 rounded-xl flex items-center justify-center">
                                <svg class="w-4 h-4 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 0 0-.788 0l-7 3a1 1 0 0 0 0 1.84L5.25 8.051a.999.999 0 0 1 .356-.257l4-1.714a1 1 0 1 1 .788 1.838L7.667 9.088l1.94.831a1 1 0 0 0 .787 0l7-3a1 1 0 0 0 0-1.838l-7-3ZM3.31 9.397L5 10.12v4.102a8.969 8.969 0 0 0-1.05-.174 1 1 0 0 1-.89-.89 11.115 11.115 0 0 1 .25-3.762ZM9.3 16.573A9.026 9.026 0 0 0 7 14.935v-3.957l1.818.78a3 3 0 0 0 2.364 0l5.508-2.361a11.026 11.026 0 0 1 .25 3.762 1 1 0 0 1-.89.89 8.968 8.968 0 0 0-5.35 2.524 1 1 0 0 1-1.4 0ZM6 18a1 1 0 0 0 1-1v-2.065a8.935 8.935 0 0 0-2-.712V17a1 1 0 0 0 1 1Z"/>
                                </svg>
                            </div>
                            <span class="font-display text-2xl font-bold text-forest-900">Conta<span class="text-gold-500">Edu</span></span>
                        </div>
                    </div>

                    {{-- Card del formulario --}}
                    <div class="bg-white rounded-3xl shadow-card border border-cream-200 px-8 py-8">
                        {{ $slot }}
                    </div>

                    <div class="flex items-center justify-center gap-4 mt-5">
                        <a href="{{ url('/') }}" class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-forest-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                            </svg>
                            Volver al inicio
                        </a>
                        <span class="text-slate-300">·</span>
                        <p class="text-xs text-slate-400">No usar datos reales</p>
                    </div>
                </div>
            </div>

        </div>
    </body>
</html>
