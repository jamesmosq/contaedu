<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ContaEdu') }} — @yield('title', 'Coordinador')</title>

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
            @include('layouts.coordinator-navigation')

            {{-- Contenido principal --}}
            <div class="flex-1 flex flex-col min-w-0">

                {{-- Banner auditoría --}}
                @if(session('audit_mode'))
                    <div class="bg-amber-500 text-amber-950 px-6 py-2 text-sm font-semibold flex items-center justify-between shrink-0">
                        <span>
                            Modo auditoría —
                            <strong>{{ session('audit_company_name') }}</strong>
                            ({{ session('audit_student_name') }}) — Solo lectura
                        </span>
                        <a href="{{ route('coordinator.auditar.stop') }}"
                           class="text-xs underline font-medium hover:text-amber-900">
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

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('notify', ({ type, message }) => {
                    const icons = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
                    const colors = { success: '#10472a', error: '#dc2626', warning: '#d97706', info: '#1e3a5f' };
                    Swal.fire({
                        icon: icons[type] || 'info',
                        title: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                        confirmButtonColor: colors[type] || '#10472a',
                    });
                });
            });
        </script>
    </body>
</html>
