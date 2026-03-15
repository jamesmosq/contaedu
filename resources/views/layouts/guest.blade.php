<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ContaEdu') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            {{-- Panel izquierdo — marca --}}
            <div class="hidden lg:flex lg:w-1/2 bg-brand-900 flex-col justify-between p-12">
                <div>
                    <span class="text-white font-bold text-2xl tracking-tight">Conta<span class="text-accent-400">Edu</span></span>
                </div>
                <div>
                    <h1 class="text-white text-4xl font-bold leading-tight mb-4">
                        Aprende contabilidad<br>como en la empresa real.
                    </h1>
                    <p class="text-brand-200 text-lg">
                        Simula el ciclo contable completo: facturación, compras, asientos y reportes financieros con el PUC colombiano.
                    </p>
                </div>
                <div class="flex gap-6 text-brand-300 text-sm">
                    <span>PUC Colombiano</span>
                    <span>·</span>
                    <span>Doble Partida</span>
                    <span>·</span>
                    <span>Multi-empresa</span>
                </div>
            </div>

            {{-- Panel derecho — formulario --}}
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-slate-50">
                <div class="w-full max-w-md">
                    <div class="lg:hidden mb-8 text-center">
                        <span class="text-brand-900 font-bold text-2xl">Conta<span class="text-accent-600">Edu</span></span>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
