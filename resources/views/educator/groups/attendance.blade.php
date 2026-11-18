<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('educator.groups.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action">← Mis grupos</a>
        <h1 class="text-[26px] font-semibold">{{ $classroom->name }}</h1>
        <p class="text-soft">Asistencia de hoy</p>
    </x-slot>

    @php
        $total = $children->count();
        $recorded = collect($marks)->filter()->count();
    @endphp

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @if (session('status'))
            <div class="rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">
                ✓ {{ session('status') }}
            </div>
        @endif

        @if ($total === 0)
            <div class="card py-6 text-center">
                <x-mascot class="mx-auto" />
                <h2 class="mt-2 text-xl">Este grupo aún no tiene estudiantes</h2>
                <p class="text-soft">La coordinación los asigna.</p>
            </div>
        @else
            <form method="POST" action="{{ route('educator.groups.attendance.store', $classroom) }}"
                  class="space-y-3"
                  x-data="{
                      marks: @js((object) $marks),
                      total: {{ $total }},
                      get done() { return Object.values(this.marks).filter(Boolean).length },
                      // Solo llena a quien falta: no cambia a quien ya está en Tarde o Ausente.
                      allPresent() { for (const id in this.marks) { this.marks[id] ??= 'present' } },
                  }">
                @csrf

                @error('status')
                    <p class="rounded-xl border-2 border-bad bg-bad-bg px-3 py-2 font-bold text-bad" role="alert">{{ $message }}</p>
                @enderror

                {{-- Avance --}}
                <div class="card">
                    <p class="font-bold"><span x-text="done">{{ $recorded }}</span> de {{ $total }} registrados</p>
                    <div class="mt-2 h-3.5 overflow-hidden rounded-full bg-line"
                         role="progressbar" aria-label="Asistencia registrada hoy"
                         aria-valuemin="0" aria-valuemax="{{ $total }}"
                         aria-valuenow="{{ $recorded }}" :aria-valuenow="done">
                        <div @class(['h-full transition-all', 'bg-ok' => $recorded === $total, 'bg-amber' => $recorded !== $total])
                             :class="{ 'bg-ok': done === total, 'bg-amber': done !== total }"
                             style="width: {{ round($recorded / $total * 100) }}%"
                             :style="`width: ${done / total * 100}%`"></div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-3" @click="allPresent()">
                        Marcar todos presentes
                    </button>
                </div>

                {{-- Un grupo de opciones por estudiante --}}
                @foreach ($children as $child)
                    <fieldset class="card">
                        <legend class="sr-only">Asistencia de {{ $child->first_name }} {{ $child->last_name }}</legend>
                        <div class="flex items-center gap-3 text-[17px] font-bold" aria-hidden="true">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border-2 border-navy bg-amber font-display text-xl text-navy">{{ mb_substr($child->first_name, 0, 1) }}</span>
                            <span>{{ $child->first_name }} {{ $child->last_name }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach ($statuses as $status)
                                <label class="opt min-h-[52px] justify-center">
                                    <input type="radio" class="sr-only"
                                           name="status[{{ $child->id }}]"
                                           value="{{ $status->value }}"
                                           x-model="marks[{{ $child->id }}]"
                                           @checked($marks[$child->id] === $status->value)>
                                    {{ $status->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('status.'.$child->id)
                            <p class="mt-1 text-sm font-bold text-bad">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endforeach

                <button type="submit" class="btn w-full">Guardar asistencia</button>
            </form>
        @endif
    </div>
</x-app-layout>