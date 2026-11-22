<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Inicio</h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Cifras del colegio. Cada tarjeta lleva a su listado. --}}
        <section aria-label="Resumen del colegio" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <a href="{{ route($stat['route']) }}"
                   class="card block transition hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-action">
                    <p class="text-4xl font-bold text-ink">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-sm text-soft">{{ $stat['label'] }}</p>
                </a>
            @endforeach
        </section>

        {{-- RF-34: enlace público para que las familias nuevas se inscriban. --}}
        @if ($school = auth()->user()->school)
            <section class="card">
                <h3 class="text-lg font-bold text-ink">Enlace de inscripción</h3>
                <p class="mt-1 text-sm text-soft">Compártelo con las familias nuevas. Las solicitudes llegan para que las revises.</p>
                <p class="mt-2 break-all font-bold text-action">
                    <a href="{{ route('enrollment.create', $school) }}" class="underline" target="_blank" rel="noopener">{{ route('enrollment.create', $school) }}</a>
                </p>
            </section>
        @endif

        {{-- Avance de bitácoras de hoy, una barra por grupo. --}}
        <section class="card">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h3 class="text-lg font-bold text-ink">Bitácoras de hoy</h3>
                <p class="text-sm text-soft">{{ $sent }} de {{ $total }} enviadas</p>
            </div>

            @forelse ($classrooms as $classroom)
                @php
                    $percent = $classroom->children_count > 0
                        ? (int) round($classroom->sent_count * 100 / $classroom->children_count)
                        : 0;
                @endphp

                <div class="mt-4">
                    <div class="flex justify-between text-sm">
                        <span class="font-semibold text-ink">{{ $classroom->name }}</span>
                        <span class="text-soft">{{ $classroom->sent_count }} de {{ $classroom->children_count }}</span>
                    </div>
                    <div class="mt-1 h-3 rounded-full bg-line"
                         role="progressbar"
                         aria-label="Bitácoras enviadas en {{ $classroom->name }}"
                         aria-valuemin="0"
                         aria-valuemax="{{ $classroom->children_count }}"
                         aria-valuenow="{{ $classroom->sent_count }}">
                        <div class="h-3 rounded-full {{ $percent === 100 ? 'bg-ok' : 'bg-amber' }}"
                             style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @empty
                <p class="mt-2 text-sm text-soft">
                    Aún no hay grupos.
                    <a href="{{ route('admin.classrooms.create') }}" class="font-semibold underline">Crea el primero</a>.
                </p>
            @endforelse

            @if ($classrooms->isNotEmpty() && \App\Support\Navigation::offersDailyLog(auth()->user()))
                <a href="{{ route('educator.reports.index') }}" class="mt-4 inline-block text-sm font-semibold underline">
                    Ver detalle por estudiante
                </a>
            @endif
        </section>

        {{-- Acciones rápidas --}}
        <section class="card">
            <h3 class="text-lg font-bold text-ink">Acciones rápidas</h3>
            <div class="mt-3 flex flex-wrap gap-3">
                <a href="{{ route('admin.guardians.create') }}" class="btn btn-secondary">+ Nueva familia</a>
                <a href="{{ route('admin.educators.create') }}" class="btn btn-secondary">+ Nuevo docente</a>
                <a href="{{ route('admin.children.create') }}" class="btn btn-secondary">+ Nuevo estudiante</a>
            </div>
        </section>
    </div>
</x-app-layout>