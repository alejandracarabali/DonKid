<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800">Crea tu contraseña nueva</h1>
    <p class="mt-1 mb-4 text-sm text-gray-600">
        Por tu seguridad, cambia la contraseña temporal antes de continuar.
    </p>

    <form method="POST" action="{{ route('password.force.update') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Contraseña que te dieron" />
            <x-text-input id="current_password" name="current_password" type="password"
                class="block mt-1 w-full" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Contraseña nueva" />
            <x-text-input id="password" name="password" type="password"
                class="block mt-1 w-full" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Repite la contraseña nueva" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="block mt-1 w-full" required autocomplete="new-password" />
        </div>

        <div class="mt-6 flex items-center justify-between">
            <x-primary-button>Guardar y entrar</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="text-sm text-gray-600 underline">Salir</button>
    </form>
</x-guest-layout>