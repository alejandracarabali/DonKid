<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Mis grupos</h1>
        <p class="text-soft">Asistencia de hoy</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        {{-- Fase 5: en primaria no hay Bitácora; el director ve aquí las solicitudes de las familias. --}}
        @unless (\App\Support\Navigation::offersDailyLog(auth()->user()))
            <x-family-requests />
        @endunless

        {{-- RF-07/RF-08: materias o dimensiones que dicta (en primaria también están en Notas). --}}
        @php $assignments = \App\Http\Controllers\Educator\GradeController::assignmentsOf(auth()->user()); @endphp
        @if ($assignments->isNotEmpty() && \App\Support\Navigation::offersDailyLog(auth()->user()))
            <h2 class="text-xl font-semibold">Notas e informes</h2>
            @include('educator.grades._assignments', ['classrooms' => $assignments])
            <h2 class="pt-3 text-xl font-semibold">Asistencia</h2>
        @endif

        @if ($classrooms->isEmpty())
            <div class="card py-6 text-center">
                <x-mascot class="mx-auto" />
                <h2 class="mt-2 text-xl">Aún no tienes grupos asignados</h2>
                <p class="text-soft">La coordinación te asigna los grupos.</p>
            </div>
        @else
            <ul class="space-y-3">
                @foreach ($classrooms as $classroom)
                    @php
                        $total = (int) $classroom->children_count;
                        $recorded = (int) $classroom->recorded_count;
                    @endphp
                    <li>
                        <a href="{{ route('educator.groups.attendance', $classroom) }}"
                           class="card flex min-h-[64px] items-center gap-3 hover:border-action">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border-2 border-navy bg-amber font-display text-xl text-navy"
                                  aria-hidden="true">{{ mb_substr($classroom->name, 0, 1) }}</span>
                            <span>
                                <span class="block text-[17px] font-bold">{{ $classroom->name }}</span>
                                <span class="block text-sm text-soft">
                                    {{ $total }} {{ $total === 1 ? 'estudiante' : 'estudiantes' }} · asistencia {{ $recorded }} de {{ $total }}
                                </span>
                            </span>
                            @if ($total === 0)
                                <span class="tag tag-pending ms-auto">Sin estudiantes</span>
                            @elseif ($recorded === $total)
                                <span class="tag tag-ok ms-auto">✓ Al día</span>
                            @else
                                <span class="tag tag-pending ms-auto">○ Pendiente</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>