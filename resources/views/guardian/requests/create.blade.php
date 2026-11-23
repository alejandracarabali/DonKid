<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('guardian.requests.child', $child) }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Solicitudes</a>
        <h1 class="text-[26px] font-semibold">{{ $type->label() }} · {{ $child->first_name }}</h1>
    </x-slot>

    @php($select = 'block mt-1 w-full min-h-[48px] rounded-xl border-2 border-line bg-white px-3')
    @php($area = 'block mt-1 w-full rounded-xl border-2 border-line text-base focus:border-action focus:ring-action')

    <div class="mx-auto max-w-xl px-4 py-4">
        <form method="POST" action="{{ route('guardian.requests.store', $child) }}" class="card space-y-4"
              x-data="{ kind: @js(old('reason', 'other_person')) }">
            @csrf
            <input type="hidden" name="type" value="{{ $type->value }}">

            @if ($type !== \App\Enums\FamilyRequestType::Recommendation)
                <div>
                    <x-input-label for="date" value="Fecha" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', today()->toDateString())" required />
                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                </div>
            @endif

            @if ($type === \App\Enums\FamilyRequestType::Excuse)
                <div>
                    <x-input-label for="reason" value="Motivo" />
                    <select id="reason" name="reason" class="{{ $select }}" required>
                        @foreach (\App\Models\FamilyRequest::EXCUSE_REASONS as $reason)
                            <option @selected(old('reason') === $reason)>{{ $reason }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                </div>
            @endif

            @if ($type === \App\Enums\FamilyRequestType::Permission)
                <div>
                    <x-input-label for="reason" value="¿Qué necesitas?" />
                    <select id="reason" name="reason" class="{{ $select }}" x-model="kind" required>
                        @foreach (\App\Models\FamilyRequest::PERMISSION_KINDS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="time" value="Hora aproximada (opcional)" />
                    <x-text-input id="time" name="time" type="time" class="mt-1 block w-full" :value="old('time')" />
                    <x-input-error :messages="$errors->get('time')" class="mt-2" />
                </div>
                {{-- Solo si recoge otra persona. x-bind:disabled evita enviar estos campos en "salida anticipada". --}}
                <fieldset class="space-y-4" x-show="kind === 'other_person'">
                    <div>
                        <x-input-label for="pickup_name" value="Nombre de quien recoge" />
                        <x-text-input id="pickup_name" name="pickup_name" type="text" maxlength="80" class="mt-1 block w-full"
                                      :value="old('pickup_name')" x-bind:disabled="kind !== 'other_person'" />
                        <x-input-error :messages="$errors->get('pickup_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="pickup_relationship" value="Parentesco" />
                        <select id="pickup_relationship" name="pickup_relationship" class="{{ $select }}" x-bind:disabled="kind !== 'other_person'">
                            @foreach (\App\Models\FamilyRequest::RELATIONSHIPS as $relationship)
                                <option @selected(old('pickup_relationship') === $relationship)>{{ $relationship }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="pickup_phone" value="Teléfono" />
                        <x-text-input id="pickup_phone" name="pickup_phone" type="tel" inputmode="tel" maxlength="20" class="mt-1 block w-full"
                                      :value="old('pickup_phone')" x-bind:disabled="kind !== 'other_person'" />
                        <x-input-error :messages="$errors->get('pickup_phone')" class="mt-2" />
                    </div>
                </fieldset>
            @endif

            <div>
                <x-input-label for="note" :value="$type === \App\Enums\FamilyRequestType::Recommendation ? 'Para el cuidado de '.$child->first_name : 'Nota (opcional)'" />
                <textarea id="note" name="note" rows="3" maxlength="280" class="{{ $area }}"
                          placeholder="{{ $type === \App\Enums\FamilyRequestType::Recommendation ? 'Ej.: hoy tiene tos, evitar el patio con viento' : 'Ej.: llegará a las 10:00' }}"
                          @required($type === \App\Enums\FamilyRequestType::Recommendation)>{{ old('note') }}</textarea>
                <x-input-error :messages="$errors->get('note')" class="mt-2" />
            </div>

            <button class="btn">Enviar al colegio</button>
        </form>
    </div>
</x-app-layout>
