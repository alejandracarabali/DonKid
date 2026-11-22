<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.school-years.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Año escolar</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Periodos {{ $schoolYear->year }}</h2>
        <p class="text-soft">{{ $schoolYear->starts_on->format('d/m/Y') }} a {{ $schoolYear->ends_on->format('d/m/Y') }}</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.school-years.update', $schoolYear) }}" class="space-y-4">
                @csrf
                @method('PUT')

                @foreach ($schoolYear->periods as $period)
                    @php($n = $period->number)
                    <fieldset class="card">
                        <legend class="px-1 text-lg font-semibold">{{ $period->name() }}</legend>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <x-input-label for="p{{ $n }}_starts" value="Inicio" />
                                <x-text-input id="p{{ $n }}_starts" name="periods[{{ $n }}][starts_on]" type="date" class="mt-1 block w-full"
                                    :value="old('periods.'.$n.'.starts_on', $period->starts_on->toDateString())" required />
                            </div>
                            <div>
                                <x-input-label for="p{{ $n }}_ends" value="Fin" />
                                <x-text-input id="p{{ $n }}_ends" name="periods[{{ $n }}][ends_on]" type="date" class="mt-1 block w-full"
                                    :value="old('periods.'.$n.'.ends_on', $period->ends_on->toDateString())" required />
                            </div>
                            <div>
                                <x-input-label for="p{{ $n }}_due" value="Notas hasta" />
                                <x-text-input id="p{{ $n }}_due" name="periods[{{ $n }}][grades_due_on]" type="date" class="mt-1 block w-full"
                                    :value="old('periods.'.$n.'.grades_due_on', $period->grades_due_on->toDateString())" required />
                            </div>
                        </div>
                        <x-input-error :messages="array_merge($errors->get('periods.'.$n.'.starts_on'), $errors->get('periods.'.$n.'.ends_on'), $errors->get('periods.'.$n.'.grades_due_on'))" class="mt-2" />
                    </fieldset>
                @endforeach

                <button class="btn">Guardar fechas</button>
            </form>
        </div>
    </div>
</x-app-layout>
