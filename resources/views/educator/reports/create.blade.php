<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $child->first_name }} {{ $child->last_name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('educator.reports.store', $child) }}"
                  class="bg-white p-6 rounded shadow space-y-6"
                  x-data="{
                      mood: '{{ old('mood', $report?->mood?->value) }}',
                      meal: '{{ old('meal_status', $report?->meal_status?->value) }}'
                  }">
                @csrf

                <div>
                    <p class="font-medium mb-2">¿Cómo llegó el ánimo?</p>
                    <input type="hidden" name="mood" :value="mood">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($moods as $m)
                            <button type="button" @click="mood = '{{ $m->value }}'"
                                    :class="mood === '{{ $m->value }}' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'bg-gray-50'"
                                    class="px-4 py-3 rounded text-center min-w-20">
                                <span class="block text-3xl">{{ $m->icon() }}</span>
                                <span class="text-sm">{{ $m->label() }}</span>
                            </button>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('mood')" class="mt-2" />
                </div>

                <div>
                    <p class="font-medium mb-2">¿Cómo comió?</p>
                    <input type="hidden" name="meal_status" :value="meal">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($meals as $m)
                            <button type="button" @click="meal = '{{ $m->value }}'"
                                    :class="meal === '{{ $m->value }}' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'bg-gray-50'"
                                    class="px-4 py-3 rounded text-center min-w-24">
                                <span class="block text-3xl">{{ $m->icon() }}</span>
                                <span class="text-sm">{{ $m->label() }}</span>
                            </button>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('meal_status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nap_minutes" value="Siesta (minutos)" />
                    <x-text-input id="nap_minutes" name="nap_minutes" type="number" min="0" max="480"
                                  class="mt-1 block w-32"
                                  :value="old('nap_minutes', $report?->nap_minutes)" />
                    <x-input-error :messages="$errors->get('nap_minutes')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="note" value="Nota breve (opcional)" />
                    <textarea id="note" name="note" rows="2" maxlength="280"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('note', $report?->note) }}</textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-2" />
                </div>

                <x-primary-button>Enviar a la familia</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>