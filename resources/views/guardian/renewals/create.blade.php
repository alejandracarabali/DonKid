<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('guardian.dashboard') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Inicio</a>
        <h1 class="text-[26px] font-semibold">Renovar matrícula {{ $year }}</h1>
        <p class="text-soft">{{ $child->first_name }} {{ $child->last_name }} · Revisa los datos y cambia solo lo necesario.</p>
    </x-slot>

    @php
        $health = $child->healthRecord;
        $select = 'block mt-1 w-full min-h-[48px] rounded-xl border-2 border-line bg-white px-3 text-base';
        $area = 'block mt-1 w-full rounded-xl border-2 border-line text-base focus:border-action focus:ring-action';
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6 lg:px-0">
        <form method="POST" action="{{ route('guardian.renewals.store', $child) }}" class="card space-y-6">
            @csrf

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold">Próximo año</legend>
                <div>
                    <x-input-label for="next_level" value="Nivel para {{ $year }}" />
                    <select id="next_level" name="next_level" class="{{ $select }}" required>
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected(old('next_level', $suggestedLevel?->value) === $level->value)>{{ $level->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('next_level')" class="mt-2" />
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold">Tus datos de contacto</legend>
                <div>
                    <x-input-label for="guardian_phone" value="Teléfono" />
                    <x-text-input id="guardian_phone" name="guardian_phone" type="tel" inputmode="tel" maxlength="20"
                        class="mt-1 block w-full" :value="old('guardian_phone', $phone)" required />
                    <x-input-error :messages="$errors->get('guardian_phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="guardian_address" value="Dirección" />
                    <x-text-input id="guardian_address" name="guardian_address" type="text" maxlength="200"
                        class="mt-1 block w-full" :value="old('guardian_address', $address)" required />
                    <x-input-error :messages="$errors->get('guardian_address')" class="mt-2" />
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold">Ficha de salud</legend>
                <p class="text-sm text-soft">Solo la ven coordinación y las docentes del grupo. Se guarda si marcas la autorización de datos de salud.</p>
                <div>
                    <x-input-label for="health_eps" value="EPS" />
                    <x-text-input id="health_eps" name="health[eps]" type="text" maxlength="80" class="mt-1 block w-full"
                        :value="old('health.eps', $health?->eps)" />
                </div>
                <div>
                    <x-input-label for="health_conditions" value="Condiciones especiales (opcional)" />
                    <textarea id="health_conditions" name="health[conditions]" rows="2" maxlength="500" class="{{ $area }}">{{ old('health.conditions', $health?->conditions) }}</textarea>
                </div>
                <div>
                    <x-input-label for="health_medications" value="Medicamentos (opcional)" />
                    <textarea id="health_medications" name="health[medications]" rows="2" maxlength="500" class="{{ $area }}">{{ old('health.medications', $health?->medications) }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="health_ec_name" value="Contacto de emergencia" />
                        <x-text-input id="health_ec_name" name="health[emergency_contact_name]" type="text" maxlength="80" class="mt-1 block w-full"
                            :value="old('health.emergency_contact_name', $health?->emergency_contact_name)" />
                    </div>
                    <div>
                        <x-input-label for="health_ec_phone" value="Teléfono de emergencia" />
                        <x-text-input id="health_ec_phone" name="health[emergency_contact_phone]" type="tel" inputmode="tel" maxlength="20" class="mt-1 block w-full"
                            :value="old('health.emergency_contact_phone', $health?->emergency_contact_phone)" />
                        <x-input-error :messages="$errors->get('health.emergency_contact_phone')" class="mt-2" />
                    </div>
                </div>
                <x-input-error :messages="$errors->get('health.*')" class="mt-2" />
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="text-lg font-semibold">Autorización de datos</legend>
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="consent_data" value="1" @checked(old('consent_data')) required
                           class="mt-1 h-6 w-6 shrink-0 rounded border-2 border-line text-action focus:ring-action">
                    <span class="text-sm">Autorizo el tratamiento de mis datos y los de mi hijo o hija para el año {{ $year }}, según la
                        <a href="{{ route('enrollment.policy', auth()->user()->school) }}" target="_blank" rel="noopener" class="font-bold text-action underline">política de tratamiento de datos</a>.</span>
                </label>
                <x-input-error :messages="$errors->get('consent_data')" />
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="consent_health" value="1" @checked(old('consent_health', $health !== null))
                           @required(config('privacy.health_documents_required'))
                           class="mt-1 h-6 w-6 shrink-0 rounded border-2 border-line text-action focus:ring-action">
                    <span class="text-sm">Autorizo de forma expresa el tratamiento de los datos de salud de mi hijo o hija para cuidar su salud y atender emergencias.</span>
                </label>
                <x-input-error :messages="$errors->get('consent_health')" />
            </fieldset>

            <button class="btn">Renovar matrícula</button>
        </form>
    </div>
</x-app-layout>
