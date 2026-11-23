<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Actividades de la semana</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-50 text-green-800 p-3 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-4 rounded shadow flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('educator.activities.index', ['semana' => $start->copy()->subWeek()->toDateString(), 'grupo' => $selected?->id]) }}"
                       class="px-3 py-1 border rounded hover:bg-gray-50">← Anterior</a>
                    <span class="font-medium">
                        {{ $start->translatedFormat('j M') }} – {{ $end->translatedFormat('j M Y') }}
                    </span>
                    <a href="{{ route('educator.activities.index', ['semana' => $start->copy()->addWeek()->toDateString(), 'grupo' => $selected?->id]) }}"
                       class="px-3 py-1 border rounded hover:bg-gray-50">Siguiente →</a>
                </div>

                <form method="GET" action="{{ route('educator.activities.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="semana" value="{{ $start->toDateString() }}">
                    <label for="grupo" class="sr-only">Grupo</label>
                    <select id="grupo" name="grupo" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos mis grupos</option>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" @selected($selected?->id === $classroom->id)>
                                {{ $classroom->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div>
                <a href="{{ route('educator.activities.create') }}"
                   class="btn btn-sm">
                    + Nueva actividad
                </a>
            </div>

            @forelse ($activities as $date => $items)
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F') }}
                    </h3>
                    <ul class="divide-y">
                        @foreach ($items as $activity)
                            <li class="py-2 flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium">{{ $activity->title }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $activity->starts_at->format('H:i') }} –
                                        @if ($activity->ends_at->isSameDay($activity->starts_at))
                                            {{ $activity->ends_at->format('H:i') }}
                                        @else
                                            {{ $activity->ends_at->translatedFormat('j M, H:i') }}
                                        @endif
                                        · {{ $activity->classroom->name }}
                                    </p>
                                    @if ($activity->description)
                                        <p class="text-sm text-gray-700 mt-1">{{ $activity->description }}</p>
                                    @endif
                                    <x-activity-attachment :activity="$activity" />
                                    @if ($activity->isDone())
                                        <p class="mt-1"><span class="tag bg-ok-bg text-ok border border-ok">✓ Hecha</span></p>
                                    @endif
                                </div>

                                <div x-data="{ confirming: false }" class="shrink-0">
                                    <div x-show="! confirming" class="flex gap-2">
                                        {{-- RF-32: registrar que se hizo, con nota y fotos. --}}
                                        <x-icon-button icon="check" :variant="$activity->isDone() ? 'soft' : 'primary'"
                                                       :label="$activity->isDone() ? 'Ver registro' : 'Registrar como hecha'" :context="$activity->title"
                                                       :href="route('educator.activities.record', $activity)" />
                                        <x-icon-button icon="edit" label="Editar" :context="$activity->title" :href="route('educator.activities.edit', $activity)" />
                                        <x-icon-button icon="trash" variant="danger" label="Eliminar" :context="$activity->title" x-on:click="confirming = true" />
                                    </div>
                                    {{-- Confirmación en la misma fila (reemplaza el confirm() del navegador). --}}
                                    <form x-show="confirming" x-cloak method="POST" action="{{ route('educator.activities.destroy', $activity) }}"
                                          class="flex flex-wrap items-center justify-end gap-2" role="alertdialog" aria-label="Confirmar que eliminas {{ $activity->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <p class="w-full text-right text-sm font-bold text-bad">¿Eliminar esta actividad?</p>
                                        <button type="submit" class="min-h-[44px] rounded-xl border-2 border-bad bg-bad px-4 text-sm font-bold text-white">Sí, eliminar</button>
                                        <button type="button" x-on:click="confirming = false" class="min-h-[44px] rounded-xl border-2 border-navy px-4 text-sm font-bold text-navy">Cancelar</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="bg-white p-6 rounded shadow text-gray-700">
                    No hay actividades esta semana.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>