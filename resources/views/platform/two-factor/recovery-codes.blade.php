<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">¡Listo, verificación activada!</h1>
    </x-slot>

    <div class="mx-auto max-w-xl space-y-4 px-4 py-6">
        <div class="rounded-xl border-2 border-amber bg-amber/10 p-4" role="alert">
            <p class="font-bold">Guarda estos códigos de recuperación en un lugar seguro.</p>
            <p class="text-sm">Si pierdes el celular, cada código te deja entrar una vez. No se volverán a mostrar.</p>
        </div>

        <ul class="card grid grid-cols-2 gap-2 font-mono text-lg font-bold">
            @foreach ($codes as $code)
                <li class="select-all rounded-lg bg-canvas p-2 text-center">{{ $code }}</li>
            @endforeach
        </ul>

        <a href="{{ route('platform.schools.index') }}" class="btn">Ya los guardé, continuar</a>
    </div>
</x-app-layout>
