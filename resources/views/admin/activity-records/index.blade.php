<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold">Registro de actividades</h1>
        <p class="text-soft">Lo que cada grupo hizo, con la nota de la docente y las fotos.</p>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-0">
        {{-- Filtros --}}
        <form method="GET" class="grid gap-3 rounded-2xl border-2 border-line bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            <div>
                <x-input-label for="grupo" value="Grupo" />
                <select id="grupo" name="grupo" class="mt-1 block min-h-[48px] w-full rounded-xl border-2 border-line bg-white px-3 text-base focus:border-action focus:ring-action">
                    <option value="">Todos</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected(request('grupo') == $classroom->id)>{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="desde" value="Desde" />
                <x-text-input id="desde" name="desde" type="date" class="mt-1 block w-full" :value="$from->toDateString()" />
            </div>
            <div>
                <x-input-label for="hasta" value="Hasta" />
                <x-text-input id="hasta" name="hasta" type="date" class="mt-1 block w-full" :value="$to->toDateString()" />
            </div>
            <button type="submit" class="min-h-[48px] rounded-xl border-2 border-navy bg-amber px-5 font-bold text-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-action focus-visible:ring-offset-2">Filtrar</button>
        </form>
        <x-input-error :messages="$errors->get('hasta')" class="mt-2" />

        {{-- Resumen --}}
        <dl class="mt-4 grid grid-cols-3 gap-3 text-center">
            @foreach ([['Hechas', $summary['done']], ['Con fotos', $summary['photos']], ['Grupos', $summary['groups']]] as [$label, $n])
                <div class="flex flex-col-reverse rounded-2xl bg-white p-3 shadow-sm">
                    <dt class="text-sm text-soft">{{ $label }}</dt>
                    <dd class="text-3xl font-semibold text-navy">{{ $n }}</dd>
                </div>
            @endforeach
        </dl>

        {{-- Lista agrupada por día --}}
        @forelse ($activities->getCollection()->groupBy(fn ($a) => $a->starts_at->toDateString()) as $day => $items)
            @php $date = \Illuminate\Support\Carbon::parse($day); @endphp
            <section class="mt-6" aria-labelledby="d-{{ $day }}">
                <h2 id="d-{{ $day }}" class="flex items-center gap-2 text-lg font-semibold first-letter:uppercase">
                    {{ $date->translatedFormat('l j \d\e F') }}
                    @if ($date->isToday())<span class="rounded-full bg-amber px-2.5 py-0.5 text-xs font-bold text-navy">Hoy</span>@endif
                </h2>

                <ul class="mt-2 space-y-3">
                    @foreach ($items as $activity)
                        <li class="rounded-2xl bg-white p-4 shadow-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-lg bg-navy px-2.5 py-0.5 text-sm font-bold text-white">{{ $activity->starts_at->format('H:i') }} – {{ $activity->ends_at->format('H:i') }}</span>
                                <span class="rounded-full border border-line px-2.5 py-0.5 text-xs font-bold text-soft">{{ $activity->classroom->name }}</span>
                            </div>

                            <h3 class="mt-2 text-lg font-semibold text-navy">{{ $activity->title }}</h3>

                            @if ($activity->done_note)
                                <p class="mt-1 rounded-xl bg-canvas px-3 py-2"><span class="font-bold">Registro:</span> {{ $activity->done_note }}</p>
                            @else
                                <p class="mt-1 text-sm text-soft">Sin nota de registro.</p>
                            @endif

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-sm text-soft">Registró {{ $activity->doneBy?->name ?? 'una cuenta que ya no existe' }} · {{ $activity->done_at->format('d/m/Y H:i') }}</p>
                                @if ($activity->photos->isNotEmpty())
                                    <ul class="flex items-center gap-1" aria-label="{{ $activity->photos->count() }} fotos">
                                        @foreach ($activity->photos->take(3) as $photo)
                                            <li>
                                                <a href="{{ route('activities.photos.show', [$activity, $photo]) }}" target="_blank" rel="noopener">
                                                    <img src="{{ route('activities.photos.show', [$activity, $photo]) }}" alt="Foto {{ $loop->iteration }} de {{ $activity->title }}"
                                                         class="h-11 w-11 rounded-lg border-2 border-line object-cover" loading="lazy">
                                                </a>
                                            </li>
                                        @endforeach
                                        @if ($activity->photos->count() > 3)
                                            <li class="grid h-11 w-11 place-items-center rounded-lg bg-canvas text-sm font-bold text-navy">+{{ $activity->photos->count() - 3 }}</li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <div class="mt-8 rounded-2xl border-2 border-dashed border-line p-8 text-center text-soft">
                <p class="text-lg font-semibold text-navy">No hay actividades registradas en estas fechas</p>
                <p>Cuando las docentes marquen actividades como hechas, aparecerán aquí.</p>
            </div>
        @endforelse

        <div class="mt-6">{{ $activities->links() }}</div>
    </div>
</x-app-layout>