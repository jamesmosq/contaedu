<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Bienvenido</h2>
        <p class="text-sm text-slate-500 mt-1">Ingresa con tu correo institucional</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="space-y-4">
            <div>
                <x-input-label for="email" value="Correo electrónico" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('student.login') }}" class="text-sm text-slate-500 hover:text-brand-700 transition">
                ¿Eres estudiante?
            </a>
            <x-primary-button>
                Ingresar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
