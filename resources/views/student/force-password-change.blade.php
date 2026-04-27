<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Cambia tu contraseña</h2>
        <p class="text-sm text-slate-500 mt-1">Tu contraseña fue restablecida por el coordinador. Debes crear una nueva para continuar.</p>
    </div>

    <form method="POST" action="{{ route('student.password.force-change.update') }}">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <x-input-label for="password" value="Nueva contraseña" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                Guardar y continuar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
