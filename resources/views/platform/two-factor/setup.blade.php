<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Activa la verificación en dos pasos</h1>
        <p class="text-soft">Tu cuenta administra todos los colegios: además de la contraseña, pediremos un código de tu celular.</p>
    </x-slot>

    <div class="mx-auto max-w-xl space-y-4 px-4 py-6">
        <ol class="card list-decimal space-y-3 ps-8">
            <li>Instala en tu celular una app autenticadora: Google Authenticator, Microsoft Authenticator o Authy.</li>
            <li>
                En la app, elige <strong>"Introducir una clave de configuración"</strong> y escribe:
                <p class="mt-2 select-all rounded-xl border-2 border-navy bg-white p-3 text-center font-mono text-lg font-bold tracking-wider">{{ $secret }}</p>
                <p class="mt-1 text-sm text-soft">Tipo: basada en el tiempo. Si estás en el celular, también puedes
                    <a href="{{ $uri }}" class="font-bold text-action underline">abrir la app directamente</a>.</p>
            </li>
            <li>Escribe aquí el código de 6 dígitos que muestra la app.</li>
        </ol>

        <form method="POST" action="{{ route('platform.two-factor.confirm') }}" class="card">
            @csrf
            <x-input-label for="code" value="Código de la app" />
            <x-text-input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                          maxlength="7" class="mt-1 block w-full text-center text-2xl tracking-widest" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
            <button class="btn mt-4">Activar</button>
        </form>
    </div>
</x-app-layout>
