<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Escala de desempeños</h2>
        <p class="text-soft">Rango de nota de cada desempeño, según el sistema de evaluación (SIEE) de tu colegio.</p>
    </x-slot>

    <div class="py-8">
        <form method="POST" action="{{ route('admin.performance.update') }}" class="card mx-auto max-w-xl space-y-3">
            @csrf
            @method('PUT')
            @if (session('status'))
                <p class="rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @foreach ($levels as $level)
                <fieldset class="grid grid-cols-3 items-end gap-3">
                    <legend class="sr-only">{{ $level->name }}</legend>
                    <p class="pb-3 font-bold">{{ $level->name }}</p>
                    <div>
                        <x-input-label for="min{{ $level->id }}" value="Desde" />
                        <x-text-input id="min{{ $level->id }}" name="levels[{{ $level->id }}][min]" type="number" step="0.1" min="1" max="5"
                                      class="mt-1 block w-full" :value="old('levels.'.$level->id.'.min', number_format($level->min, 1))" required />
                    </div>
                    <div>
                        <x-input-label for="max{{ $level->id }}" value="Hasta" />
                        <x-text-input id="max{{ $level->id }}" name="levels[{{ $level->id }}][max]" type="number" step="0.1" min="1" max="5"
                                      class="mt-1 block w-full" :value="old('levels.'.$level->id.'.max', number_format($level->max, 1))" required />
                    </div>
                </fieldset>
                <x-input-error :messages="array_merge($errors->get('levels.'.$level->id.'.min'), $errors->get('levels.'.$level->id.'.max'))" />
            @endforeach

            <p class="text-sm text-soft">Los rangos deben ir de 1.0 a 5.0, seguidos y sin cruzarse.</p>
            <button class="btn">Guardar escala</button>
        </form>
    </div>
</x-app-layout>
