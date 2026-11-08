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
                   class="inline-block px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">
                    Nueva actividad
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
                                        {{ $activity->starts_at->format('H:i') }} – {{ $activity->ends_at->format('H:i') }}
                                        · {{ $activity->classroom->name }}
                                    </p>
                                    @if ($activity->description)
                                        <p class="text-sm text-gray-700 mt-1">{{ $activity->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-sm shrink-0">
                                    <a href="{{ route('educator.activities.edit', $activity) }}"
                                       class="text-blue-700 hover:underline">Editar</a>
                                    <form method="POST" action="{{ route('educator.activities.destroy', $activity) }}"
                                          onsubmit="return confirm('¿Eliminar esta actividad?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-700 hover:underline">Eliminar</button>
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