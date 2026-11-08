<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Actividades de la semana</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white p-4 rounded shadow flex flex-wrap items-center gap-2">
                <a href="{{ route('guardian.activities.index', ['semana' => $start->copy()->subWeek()->toDateString()]) }}"
                   class="px-3 py-1 border rounded hover:bg-gray-50">← Anterior</a>
                <span class="font-medium">
                    {{ $start->translatedFormat('j M') }} – {{ $end->translatedFormat('j M Y') }}
                </span>
                <a href="{{ route('guardian.activities.index', ['semana' => $start->copy()->addWeek()->toDateString()]) }}"
                   class="px-3 py-1 border rounded hover:bg-gray-50">Siguiente →</a>
            </div>

            @forelse ($activities as $date => $items)
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l j \d\e F') }}
                    </h3>
                    <ul class="divide-y">
                        @foreach ($items as $activity)
                            <li class="py-2">
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
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="bg-white p-6 rounded shadow text-gray-700">
                    No hay actividades programadas esta semana.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>