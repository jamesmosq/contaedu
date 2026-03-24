@props(['title' => 'Panel'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ContaEdu') }} — {{ $title }}</title>

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-cream-50 text-slate-800">

        <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

            {{-- Overlay móvil --}}
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 z-20 bg-black/50 lg:hidden"
                 style="display:none"></div>

            {{-- Sidebar --}}
            @include('layouts.tenant-navigation')

            {{-- Contenido principal --}}
            <div class="flex-1 flex flex-col min-w-0">

                {{-- Banner: modo demo del docente --}}
                @if(session('demo_mode'))
                    <div class="bg-indigo-600 text-white px-4 py-2 flex items-center justify-between text-sm font-medium shrink-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                            <span>Empresa de demostración — <strong>{{ session('demo_company_name') }}</strong> — Acceso completo de docente</span>
                        </div>
                        <a href="{{ route('teacher.demo.exit', session('demo_tenant_id')) }}" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-semibold transition">
                            Salir
                        </a>
                    </div>
                @endif

                {{-- Banner: modo referencia del estudiante (solo lectura) --}}
                @if(session('reference_mode'))
                    <div class="bg-violet-600 text-white px-4 py-2 flex items-center justify-between text-sm font-medium shrink-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.574-3.007-9.964-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Empresa de referencia — <strong>{{ session('reference_company_name') }}</strong> — Solo lectura</span>
                        </div>
                        <a href="{{ route('student.referencias.exit', session('reference_tenant_id')) }}" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-semibold transition">
                            Salir
                        </a>
                    </div>
                @endif

                {{-- Banner: modo auditoría del docente (solo lectura) --}}
                @if(session('audit_mode') && !session('reference_mode'))
                    <div class="bg-amber-500 text-white px-4 py-2 flex items-center justify-between text-sm font-medium shrink-0">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.574-3.007-9.964-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Modo auditoría — empresa de <strong>{{ session('audit_student_name') }}</strong> — Solo lectura</span>
                        </div>
                        <a href="{{ route('teacher.auditar.stop') }}" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-semibold transition">
                            Salir
                        </a>
                    </div>
                @endif

                {{-- Topbar móvil --}}
                <div class="lg:hidden flex items-center justify-between px-4 py-3 bg-forest-900 shrink-0">
                    <span class="font-display text-lg font-bold text-white">Conta<span class="text-gold-400">Edu</span></span>
                    <button @click="sidebarOpen = true" class="p-2 rounded-lg text-forest-300 hover:text-white hover:bg-forest-800 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

                {{-- Header de página --}}
                @isset($header)
                    <header class="bg-white border-b border-cream-200 shadow-card-sm shrink-0">
                        <div class="px-6 py-4">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                {{-- Contenido --}}
                <main class="flex-1">
                    {{ $slot }}
                </main>

            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
