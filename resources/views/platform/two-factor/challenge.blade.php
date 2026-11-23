<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Verificación en dos pasos</h1>
        <p class="text-soft">Escribe el código de 6 dígitos de tu app autenticadora.</p>
    </x-slot>

    <div class="mx-auto max-w-md px-4 py-6">
        <form method="POST" action="{{ route('platform.two-factor.verify') }}" class="card">
            @csrf
            <x-input-label for="code" value="Código" />
            <x-text-input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                          maxlength="20" class="mt-1 block w-full text-center text-2xl tracking-widest" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
            <p class="mt-2 text-sm text-soft">¿Perdiste el celular? Escribe uno de tus códigos de recuperación.</p>
            <button class="btn mt-4">Continuar</button>
        </form>
    </div>
</x-app-layout>
