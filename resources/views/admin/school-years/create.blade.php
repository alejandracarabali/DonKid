<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.school-years.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Año escolar</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo año escolar</h2>
    </x-slot>

    <div class="py-8">
        <form method="POST" action="{{ route('admin.school-years.store') }}" class="card mx-auto max-w-xl space-y-4">
            @csrf
            <div>
                <x-input-label for="year" value="Año" />
                <x-text-input id="year" name="year" type="number" class="mt-1 block w-full" :value="old('year', $year)" required />
                <x-input-error :messages="$errors->get('year')" class="mt-2" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="starts_on" value="Inicio de clases" />
                    <x-text-input id="starts_on" name="starts_on" type="date" class="mt-1 block w-full" :value="old('starts_on', $startsOn)" required />
                    <x-input-error :messages="$errors->get('starts_on')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="ends_on" value="Fin de clases" />
                    <x-text-input id="ends_on" name="ends_on" type="date" class="mt-1 block w-full" :value="old('ends_on', $endsOn)" required />
                    <x-input-error :messages="$errors->get('ends_on')" class="mt-2" />
                </div>
            </div>
            <p class="text-sm text-soft">Se crean {{ config('academic.periods') }} periodos de igual duración. En el siguiente paso puedes ajustar sus fechas.</p>
            <button class="btn">Crear año escolar</button>
        </form>
    </div>
</x-app-layout>
