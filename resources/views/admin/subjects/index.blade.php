<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Materias y dimensiones</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @foreach ($kinds as $kind)
                <section class="card">
                    <h3 class="text-lg font-semibold">{{ $kind === \App\Enums\SubjectKind::Subject ? 'Materias de primaria' : 'Dimensiones de preescolar' }}</h3>
                    <p class="text-sm text-soft">
                        {{ $kind === \App\Enums\SubjectKind::Subject ? 'Se califican con nota de 1.0 a 5.0.' : 'Se evalúan con un texto por niño.' }}
                        Desactivar no borra: conserva las notas que ya tenga.
                    </p>

                    <ul class="mt-3 divide-y divide-line">
                        @foreach ($subjects[$kind->value] ?? [] as $subject)
                            {{-- Desactivada: la fila se atenúa y el botón pasa a "Activar". --}}
                            <li @class(['flex items-center gap-2 py-3', 'opacity-60' => ! $subject->active])>
                                <form id="save-{{ $subject->id }}" method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="min-w-0 flex-1">
                                    @csrf
                                    @method('PATCH')
                                    <label for="s{{ $subject->id }}" class="sr-only">Nombre de {{ $subject->name }}</label>
                                    <input id="s{{ $subject->id }}" name="name" type="text" maxlength="80" required
                                           value="{{ $subject->name }}"
                                           class="block min-h-[48px] w-full rounded-xl border-2 border-line px-3 text-base focus:border-action focus:ring-action">
                                </form>
                                {{-- form="save-…" conecta el botón con el formulario de la izquierda aunque esté fuera de él. --}}
                                <x-icon-button type="submit" form="save-{{ $subject->id }}" icon="save" variant="soft" label="Guardar cambios" :context="$subject->name" />

                                <form method="POST" action="{{ route('admin.subjects.toggle', $subject) }}">
                                    @csrf
                                    <x-icon-button type="submit" icon="power" :variant="$subject->active ? 'danger' : 'soft'"
                                                   :label="$subject->active ? 'Desactivar' : 'Activar'" :context="$subject->name" />
                                </form>
                            </li>
                        @endforeach
                    </ul>

                    <form method="POST" action="{{ route('admin.subjects.store') }}" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <input type="hidden" name="kind" value="{{ $kind->value }}">
                        <div class="grow">
                            <x-input-label for="new_{{ $kind->value }}" :value="$kind === \App\Enums\SubjectKind::Subject ? 'Nueva materia' : 'Nueva dimensión'" />
                            <x-text-input id="new_{{ $kind->value }}" name="name" type="text" maxlength="80" class="mt-1 block w-full" />
                        </div>
                        <button class="btn btn-sm">+ Agregar</button>
                    </form>
                </section>
            @endforeach
            <x-input-error :messages="$errors->get('name')" />
        </div>
    </div>
</x-app-layout>
