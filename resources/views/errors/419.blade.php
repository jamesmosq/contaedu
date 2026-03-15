<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión expirada — ContaEdu</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 w-full max-w-md p-8 text-center">

        <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>

        <h1 class="text-xl font-bold text-slate-800 mb-2">Sesión expirada</h1>
        <p class="text-slate-500 text-sm mb-6">
            Tu sesión ha expirado por inactividad o la página lleva demasiado tiempo abierta.
            Recarga para continuar — no perderás tus datos guardados.
        </p>

        <button onclick="window.location.reload()"
            class="w-full px-4 py-2.5 bg-brand-800 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition mb-3">
            Recargar página
        </button>

        <a href="{{ url('/') }}"
            class="block w-full px-4 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition">
            Ir al inicio
        </a>

    </div>
</body>
</html>
