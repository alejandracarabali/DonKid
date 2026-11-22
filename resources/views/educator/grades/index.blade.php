<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Notas</h1>
        <p class="text-soft">Elige el grupo y la materia.</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @if ($classrooms->isEmpty())
            <div class="card py-6 text-center">
                <x-mascot class="mx-auto" />
                <h2 class="mt-2 text-xl">Aún no tienes materias asignadas</h2>
                <p class="text-soft">La coordinación te asigna las materias de cada grupo.</p>
            </div>
        @else
            @include('educator.grades._assignments')
        @endif
    </div>
</x-app-layout>
