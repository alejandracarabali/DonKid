<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('educator.reports.index') }}"
           class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Bitácora de hoy</a>
        <h1 class="text-[26px] font-semibold">{{ $child->first_name }} {{ $child->last_name }}</h1>
        <p class="text-soft">¿Cómo estuvo hoy?</p>
    </x-slot>

    @php
        $savedMood = old('mood', $report?->mood?->value);
        $savedMeal = old('meal_status', $report?->meal_status?->value);
        $savedNap = old('nap_minutes', $report?->nap_minutes);
        // Siesta: null = "sin registrar" (no es lo mismo que 0 minutos).
        $napValue = ($savedNap === null || $savedNap === '') ? null : (int) $savedNap;
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-0">
        {{-- @js convierte valores PHP a JavaScript de forma segura (comillas, tildes, etc.). --}}
        <form method="POST" action="{{ route('educator.reports.store', $child) }}"
              class="card space-y-5"
              x-data="{
                  mood: @js($savedMood),
                  meal: @js($savedMeal),
                  nap: @js($napValue),
                  note: @js(old('note', $report?->note ?? '')),
              }">
            @csrf

            {{-- Ánimo --}}
            <div>
                <h2 id="lbl-mood" class="mb-2 text-[17px] font-medium">Ánimo</h2>
                <input type="hidden" name="mood" :value="mood">
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-5" role="group" aria-labelledby="lbl-mood">
                    @foreach ($moods as $m)
                        <button type="button" class="opt"
                                @click="mood = '{{ $m->value }}'"
                                aria-pressed="{{ $savedMood === $m->value ? 'true' : 'false' }}"
                                :aria-pressed="(mood === '{{ $m->value }}').toString()">
                            <span class="text-3xl" aria-hidden="true">{{ $m->icon() }}</span>
                            {{ $m->label() }}
                        </button>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('mood')" class="mt-2" />
            </div>

            {{-- Comida --}}
            <div>
                <h2 id="lbl-meal" class="mb-2 text-[17px] font-medium">Comida</h2>
                <input type="hidden" name="meal_status" :value="meal">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" role="group" aria-labelledby="lbl-meal">
                    @foreach ($meals as $m)
                        <button type="button" class="opt"
                                @click="meal = '{{ $m->value }}'"
                                aria-pressed="{{ $savedMeal === $m->value ? 'true' : 'false' }}"
                                :aria-pressed="(meal === '{{ $m->value }}').toString()">
                            <span class="text-3xl" aria-hidden="true">{{ $m->icon() }}</span>
                            {{ $m->label() }}
                        </button>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('meal_status')" class="mt-2" />
            </div>

            {{-- Siesta: botones de ±15 minutos, más fáciles que escribir un número en el celular. --}}
            <div>
                <h2 id="lbl-nap" class="mb-2 text-[17px] font-medium">Siesta</h2>
                <input type="hidden" name="nap_minutes" :value="nap ?? ''">
                <div class="flex items-center gap-3" role="group" aria-labelledby="lbl-nap">
                    <button type="button"
                            class="grid h-12 w-12 place-items-center rounded-full border-2 border-ink bg-white text-2xl font-bold"
                            @click="if (nap !== null) nap = Math.max(0, nap - 15)"
                            aria-label="Menos 15 minutos">−</button>
                    <output class="min-w-[130px] text-center font-display text-2xl font-semibold" aria-live="polite"
                            x-text="nap === null ? 'Sin registrar' : nap + ' min'">
                        {{ $napValue === null ? 'Sin registrar' : $napValue.' min' }}
                    </output>
                    <button type="button"
                            class="grid h-12 w-12 place-items-center rounded-full border-2 border-ink bg-white text-2xl font-bold"
                            @click="nap = Math.min(480, (nap ?? 0) + 15)"
                            aria-label="Más 15 minutos">+</button>
                </div>
                <x-input-error :messages="$errors->get('nap_minutes')" class="mt-2" />
            </div>

            {{-- Nota --}}
            <div>
                <label for="note" class="mb-2 block text-[17px] font-medium">Nota breve <span class="text-soft">(opcional)</span></label>
                <textarea id="note" name="note" rows="3" maxlength="280" x-model="note"
                          placeholder="Algo que la familia deba saber"
                          class="block w-full rounded-xl border-2 border-line text-base focus:border-action focus:ring-action">{{ old('note', $report?->note) }}</textarea>
                <p class="text-end text-sm text-soft"><span x-text="note.length">{{ mb_strlen(old('note', $report?->note ?? '')) }}</span>/280</p>
                <x-input-error :messages="$errors->get('note')" class="mt-2" />
            </div>

            <x-primary-button class="sm:w-full">Enviar a la familia</x-primary-button>
        </form>
    </div>
</x-app-layout>