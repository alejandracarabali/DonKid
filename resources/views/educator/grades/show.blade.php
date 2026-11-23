@php
    $isDimension = $subject->kind === \App\Enums\SubjectKind::Dimension;
    $readOnly = $period?->isClosed();
@endphp
<x-app-layout>
    <x-slot name="header">
        <a href="{{ route(\App\Support\Navigation::offersDailyLog(auth()->user()) ? 'educator.groups.index' : 'educator.grades.index') }}"
           class="inline-flex min-h-[44px] items-center font-bold text-action">← Volver</a>
        <h1 class="text-[26px] font-semibold">{{ $subject->name }} · {{ $classroom->name }}</h1>
        <p class="text-soft">{{ $isDimension ? 'Informe del periodo por cada niño.' : 'La nota del periodo es el promedio de las actividades.' }}</p>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @if (session('status'))
            <div class="rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">✓ {{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border-2 border-bad bg-bad-bg px-3 py-2 font-bold text-bad" role="alert">
                @foreach (collect($errors->all())->unique() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        @if (! $period)
            <div class="card py-6 text-center">
                <h2 class="text-xl">Todavía no hay año escolar</h2>
                <p class="text-soft">La coordinación debe crear el año escolar y sus periodos.</p>
            </div>
        @else
            {{-- Periodos --}}
            <nav aria-label="Periodos" class="flex flex-wrap gap-2">
                @foreach ($year->periods as $p)
                    <a href="{{ route('educator.grades.show', [$classroom, $subject, 'periodo' => $p->number]) }}"
                       @class(['inline-flex min-h-[44px] items-center rounded-xl px-3 font-bold',
                               'bg-amber text-navy' => $p->is($period), 'border-2 border-line' => ! $p->is($period)])
                       @if ($p->is($period)) aria-current="page" @endif>{{ $p->name() }}@if ($p->isClosed()) · cerrado @endif</a>
                @endforeach
            </nav>
            <p class="text-sm text-soft">
                {{ $period->starts_on->format('d/m/Y') }} al {{ $period->ends_on->format('d/m/Y') }}
                @if ($period->grades_due_on) · Fecha límite de notas: {{ $period->grades_due_on->format('d/m/Y') }} @endif
            </p>
            @if ($readOnly)
                <p class="rounded-xl border-2 border-line bg-white px-3 py-2 font-bold">Este periodo está cerrado: solo puedes consultar.</p>
            @endif

            @if ($children->isEmpty())
                <div class="card py-6 text-center">
                    <h2 class="text-xl">Este grupo aún no tiene estudiantes</h2>
                </div>
            @elseif ($isDimension)
                {{-- RF-08: informe escrito por niño --}}
                <form method="POST" action="{{ route('educator.grades.reports', [$classroom, $subject]) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="periodo" value="{{ $period->number }}">
                    @foreach ($children as $child)
                        <div class="card">
                            <label for="r{{ $child->id }}" class="text-[17px] font-bold">{{ $child->first_name }} {{ $child->last_name }}</label>
                            <textarea id="r{{ $child->id }}" name="reports[{{ $child->id }}]" rows="3" maxlength="2000"
                                      class="mt-2 block w-full rounded-xl border-line" @disabled($readOnly)
                                      placeholder="Cómo avanzó en esta dimensión durante el periodo">{{ old('reports.'.$child->id, $reports[$child->id] ?? '') }}</textarea>
                        </div>
                    @endforeach
                    @unless ($readOnly)
                        <button type="submit" class="btn w-full">Guardar informes</button>
                    @endunless
                </form>
            @else
                {{-- RF-07: actividades del periodo --}}
                <section class="card">
                    <h2 class="text-lg font-semibold">Actividades del periodo</h2>
                    @if ($assessments->isEmpty())
                        <p class="text-soft">Agrega la primera actividad (quiz, taller, exposición...) para empezar a poner notas.</p>
                    @else
                        <ul class="mt-2 divide-y divide-line">
                            @foreach ($assessments as $assessment)
                                <li class="flex min-h-[44px] items-center gap-3">
                                    <span class="grow">{{ $loop->iteration }}. {{ $assessment->title }}</span>
                                    @unless ($readOnly)
                                        <form method="POST" action="{{ route('educator.grades.assessments.destroy', [$classroom, $subject, $assessment]) }}"
                                              onsubmit="return confirm('¿Quitar esta actividad? Se borran sus notas.')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="periodo" value="{{ $period->number }}">
                                            <button class="text-sm text-bad underline">Quitar</button>
                                        </form>
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @unless ($readOnly)
                        <form method="POST" action="{{ route('educator.grades.assessments.store', [$classroom, $subject]) }}" class="mt-3 flex flex-wrap items-end gap-2">
                            @csrf
                            <input type="hidden" name="periodo" value="{{ $period->number }}">
                            <div class="grow">
                                <x-input-label for="title" value="Nueva actividad" />
                                <x-text-input id="title" name="title" maxlength="80" class="mt-1 block w-full" placeholder="Ej. Quiz de fracciones" />
                            </div>
                            <button class="btn btn-sm">+ Agregar</button>
                        </form>
                    @endunless
                </section>

                @if ($assessments->isNotEmpty())
                    {{-- Planilla: una fila por estudiante, una columna por actividad --}}
                    <form method="POST" action="{{ route('educator.grades.scores', [$classroom, $subject]) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="periodo" value="{{ $period->number }}">
                        <div class="card overflow-x-auto p-0">
                            <table class="w-full text-left">
                                <thead class="border-b-2 border-line text-sm">
                                    <tr>
                                        <th scope="col" class="p-3">Estudiante</th>
                                        @foreach ($assessments as $assessment)
                                            <th scope="col" class="p-3 text-center" title="{{ $assessment->title }}">{{ $loop->iteration }}</th>
                                        @endforeach
                                        <th scope="col" class="p-3 text-center">Promedio</th>
                                        <th scope="col" class="p-3">Desempeño</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($children as $child)
                                        @php
                                            $average = $averages[$child->id] ?? null;
                                            $level = $average !== null ? \App\Models\PerformanceLevel::match($levels, $average) : null;
                                        @endphp
                                        <tr>
                                            <th scope="row" class="whitespace-nowrap p-3 font-bold">{{ $child->first_name }} {{ $child->last_name }}</th>
                                            @foreach ($assessments as $assessment)
                                                @php $value = old('scores.'.$assessment->id.'.'.$child->id, $scores[$assessment->id][$child->id] ?? null); @endphp
                                                <td class="p-2 text-center">
                                                    <label for="n{{ $assessment->id }}-{{ $child->id }}" class="sr-only">{{ $assessment->title }}, {{ $child->first_name }}</label>
                                                    <input id="n{{ $assessment->id }}-{{ $child->id }}" type="number" step="0.1" min="1" max="5" inputmode="decimal"
                                                           name="scores[{{ $assessment->id }}][{{ $child->id }}]"
                                                           value="{{ is_numeric($value) ? number_format((float) $value, 1, '.', '') : $value }}"
                                                           @class(['w-20 rounded-lg border-line text-center', 'border-bad' => $errors->has('scores.'.$assessment->id.'.'.$child->id)])
                                                           @disabled($readOnly)>
                                                </td>
                                            @endforeach
                                            <td class="p-3 text-center font-bold">{{ $average !== null ? number_format($average, 1) : '—' }}</td>
                                            <td class="p-3">{{ $level?->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-sm text-soft">Notas de 1.0 a 5.0. Deja la casilla vacía si el estudiante aún no tiene nota.</p>
                        @unless ($readOnly)
                            <button type="submit" class="btn w-full">Guardar notas</button>
                        @endunless
                    </form>
                @endif
            @endif
        @endif
    </div>
</x-app-layout>
