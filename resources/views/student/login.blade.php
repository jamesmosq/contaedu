<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Acceso Estudiante</h2>
        <p class="text-sm text-slate-500 mt-1">Ingresa con tu número de cédula</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('student.login') }}">
        @csrf

        <div class="space-y-4">
            <div>
                <x-input-label for="cedula" value="Número de cédula" />
                <x-text-input
                    id="cedula"
                    class="block mt-1 w-full"
                    type="text"
                    name="cedula"
                    :value="old('cedula')"
                    required
                    autofocus
                    placeholder="cc1023456789"
                />
                <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-brand-700 transition">
                ¿Eres docente?
            </a>
            <x-primary-button>
                Ingresar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
