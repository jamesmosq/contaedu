<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — ContaEdu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-cream-50 min-h-screen flex items-center justify-center p-6">
    <div class="text-center max-w-md w-full">

        {{-- Logo --}}
        <div class="flex items-center justify-center gap-2 mb-10">
            <div class="w-9 h-9 bg-forest-800 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 0 0-.788 0l-7 3a1 1 0 0 0 0 1.84L5.25 8.051a.999.999 0 0 1 .356-.257l4-1.714a1 1 0 1 1 .788 1.838L7.667 9.088l1.94.831a1 1 0 0 0 .787 0l7-3a1 1 0 0 0 0-1.838l-7-3ZM3.31 9.397L5 10.12v4.102a8.969 8.969 0 0 0-1.05-.174 1 1 0 0 1-.89-.89 11.115 11.115 0 0 1 .25-3.762ZM9.3 16.573A9.026 9.026 0 0 0 7 14.935v-3.957l1.818.78a3 3 0 0 0 2.364 0l5.508-2.361a11.026 11.026 0 0 1 .25 3.762 1 1 0 0 1-.89.89 8.968 8.968 0 0 0-5.35 2.524 1 1 0 0 1-1.4 0ZM6 18a1 1 0 0 0 1-1v-2.065a8.935 8.935 0 0 0-2-.712V17a1 1 0 0 0 1 1Z"/>
                </svg>
            </div>
            <span class="font-display text-2xl font-bold text-forest-900">Conta<span class="text-gold-500">Edu</span></span>
        </div>

        {{-- Código de error --}}
        <p class="font-display text-8xl font-bold text-forest-900 mb-2">@yield('code')</p>

        {{-- Título --}}
        <h1 class="text-2xl font-bold text-slate-800 mb-3">@yield('title')</h1>

        {{-- Descripción --}}
        <p class="text-slate-500 mb-8">@yield('description')</p>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @yield('actions')
        </div>

    </div>
</body>
</html>
