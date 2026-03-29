<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ContaEdu') }} — @yield('title', 'Panel')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts

        {{-- SweetAlert2: flash messages globales --}}
        @if(session('success') || session('error') || session('warning') || session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @if(session('success'))
                    Swal.fire({ icon: 'success', title: '¡Listo!', text: @json(session('success')), confirmButtonColor: '#10472a', timer: 5000, timerProgressBar: true });
                @elseif(session('error'))
                    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#dc2626' });
                @elseif(session('warning'))
                    Swal.fire({ icon: 'warning', title: 'Atención', text: @json(session('warning')), confirmButtonColor: '#d97706', timer: 5000, timerProgressBar: true });
                @elseif(session('info'))
                    Swal.fire({ icon: 'info', title: 'Información', text: @json(session('info')), confirmButtonColor: '#1e3a5f', timer: 5000, timerProgressBar: true });
                @endif
            });
        </script>
        @endif
    </body>
</html>
