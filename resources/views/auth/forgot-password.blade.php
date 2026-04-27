<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Restablecer contraseña</h2>
        <p class="text-sm text-slate-500 mt-1">Ingresa tu correo y te enviaremos un enlace para crear una nueva contraseña.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-brand-700 transition">
                Volver al inicio de sesión
            </a>
            <x-primary-button>
                Enviar enlace
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
