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
                                <x-activity-attachment :activity="$activity" />

                                {{-- RF-32: lo que se hizo, con la nota de la docente y las fotos. --}}
                                @if ($activity->isDone())
                                    <div class="mt-2 rounded-xl bg-canvas p-3">
                                        <p><span class="tag bg-ok-bg text-ok border border-ok">✓ Hecha</span></p>
                                        @if ($activity->done_note)
                                            <p class="mt-2">{{ $activity->done_note }}</p>
                                        @endif

                                        @if ($activity->photos->isNotEmpty())
                                            @if ($photoClassroomIds->contains($activity->classroom_id))
                                                <ul class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4">
                                                    @foreach ($activity->photos as $photo)
                                                        <li>
                                                            <a href="{{ route('activities.photos.show', [$activity, $photo]) }}" target="_blank" rel="noopener">
                                                                <img src="{{ route('activities.photos.show', [$activity, $photo]) }}"
                                                                     alt="Foto {{ $loop->iteration }} de {{ $activity->title }}"
                                                                     class="aspect-square w-full rounded-lg border-2 border-line object-cover" loading="lazy">
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="mt-2 text-sm text-soft">
                                                    Esta actividad tiene fotos. Para verlas,
                                                    <a href="{{ route('guardian.dashboard') }}" class="font-bold text-action underline">autoriza el uso de imagen</a> de tu hijo.
                                                </p>
                                            @endif
                                        @endif
                                    </div>
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