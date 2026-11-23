<x-guest-layout>
    <h1 class="text-2xl font-semibold">Inscripción</h1>
    <p class="mt-1 text-soft">{{ $school->name }} · Cuéntanos sobre tu hijo o hija y te contactaremos.</p>

    <form method="POST" action="{{ route('enrollment.store', $school) }}" class="mt-4 space-y-6"
          enctype="multipart/form-data" novalidate>
        @csrf

        {{-- Trampa anti-bots: invisible para las personas; si un bot la llena, se rechaza. --}}
        <div class="hidden" aria-hidden="true">
            <label for="website">No llenes este campo</label>
            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        @php($select = 'block mt-1 w-full min-h-[48px] rounded-xl border-2 border-line bg-white px-3 text-base focus:border-action focus:ring-action')

        {{-- Niño o niña --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold">Niño o niña</legend>

            <div>
                <x-input-label for="child_first_name" value="Nombre" />
                <x-text-input id="child_first_name" name="child_first_name" type="text" class="mt-1 block w-full"
                    maxlength="60" :value="old('child_first_name')" required autofocus />
                <x-input-error :messages="$errors->get('child_first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="child_last_name" value="Apellido" />
                <x-text-input id="child_last_name" name="child_last_name" type="text" class="mt-1 block w-full"
                    maxlength="60" :value="old('child_last_name')" required />
                <x-input-error :messages="$errors->get('child_last_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="child_birth_date" value="Fecha de nacimiento" />
                <x-text-input id="child_birth_date" name="child_birth_date" type="date" class="mt-1 block w-full"
                    :max="today()->subDay()->toDateString()" :value="old('child_birth_date')" required />
                <x-input-error :messages="$errors->get('child_birth_date')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="desired_level" value="Nivel al que aspira" />
                <select id="desired_level" name="desired_level" class="{{ $select }}" required>
                    <option value="">Elige el nivel</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->value }}" @selected(old('desired_level') === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('desired_level')" class="mt-2" />
            </div>
        </fieldset>

        {{-- Acudiente --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold">Tus datos (acudiente)</legend>

            <div>
                <x-input-label for="guardian_name" value="Nombre completo" />
                <x-text-input id="guardian_name" name="guardian_name" type="text" class="mt-1 block w-full"
                    maxlength="80" autocomplete="name" :value="old('guardian_name')" required />
                <x-input-error :messages="$errors->get('guardian_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_relationship" value="Parentesco" />
                <select id="guardian_relationship" name="guardian_relationship" class="{{ $select }}" required>
                    <option value="">Elige el parentesco</option>
                    @foreach ($relationships as $relationship)
                        <option @selected(old('guardian_relationship') === $relationship)>{{ $relationship }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('guardian_relationship')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_document_type" value="Tipo de documento" />
                <select id="guardian_document_type" name="guardian_document_type" class="{{ $select }}" required>
                    <option value="">Elige el tipo</option>
                    @foreach ($documentTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('guardian_document_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('guardian_document_type')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_document_number" value="Número de documento" />
                <x-text-input id="guardian_document_number" name="guardian_document_number" type="text" class="mt-1 block w-full"
                    maxlength="20" autocomplete="off" :value="old('guardian_document_number')" required />
                <x-input-error :messages="$errors->get('guardian_document_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_email" value="Correo" />
                <x-text-input id="guardian_email" name="guardian_email" type="email" inputmode="email" class="mt-1 block w-full"
                    maxlength="120" autocomplete="email" :value="old('guardian_email')" required />
                <x-input-error :messages="$errors->get('guardian_email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_phone" value="Teléfono" />
                <x-text-input id="guardian_phone" name="guardian_phone" type="tel" inputmode="tel" class="mt-1 block w-full"
                    maxlength="20" autocomplete="tel" :value="old('guardian_phone')" required />
                <x-input-error :messages="$errors->get('guardian_phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="guardian_address" value="Dirección" />
                <x-text-input id="guardian_address" name="guardian_address" type="text" class="mt-1 block w-full"
                    maxlength="200" autocomplete="street-address" :value="old('guardian_address')" required />
                <x-input-error :messages="$errors->get('guardian_address')" class="mt-2" />
            </div>
        </fieldset>

        {{-- Documentos obligatorios. Se guardan en un disco privado: solo coordinación los abre. --}}
        <fieldset class="space-y-4">
            <legend class="text-lg font-semibold">Documentos</legend>
            <p class="text-sm text-soft">PDF o foto clara, máximo 4 MB cada uno. Solo coordinación puede verlos.</p>

            @foreach ($enrollmentDocuments as $doc)
                <div>
                    <x-input-label for="doc_{{ $doc->value }}" :value="$doc->label().($doc->isRequired() ? '' : ' (opcional)')" />
                    <input id="doc_{{ $doc->value }}" name="documents[{{ $doc->value }}]" type="file"
                           accept="{{ $doc->accept() }}" @required($doc->isRequired())
                           class="mt-1 block w-full text-sm file:mr-3 file:min-h-[44px] file:rounded-xl file:border-2 file:border-navy file:bg-amber file:px-4 file:font-bold file:text-navy">
                    <x-input-error :messages="$errors->get('documents.'.$doc->value)" class="mt-2" />
                </div>
            @endforeach
            <p class="text-sm text-soft">Si hay un error en el formulario, vuelve a adjuntar los documentos.</p>
        </fieldset>

        {{-- RF-37: autorización de tratamiento de datos (Ley 1581). Dos casillas: los datos de salud son sensibles. --}}
        <fieldset class="space-y-3">
            <legend class="text-lg font-semibold">Autorización de datos</legend>

            <label class="flex items-start gap-3">
                <input type="checkbox" name="consent_data" value="1" @checked(old('consent_data')) required
                       class="mt-1 h-6 w-6 shrink-0 rounded border-2 border-line text-action focus:ring-action">
                <span class="text-sm">
                    Como acudiente y representante legal del niño o niña, autorizo a {{ $school->name }} para recoger y tratar
                    mis datos personales y los de mi hijo o hija, con las finalidades descritas en la
                    <a href="{{ route('enrollment.policy', $school) }}" target="_blank" rel="noopener" class="font-bold text-action underline">política de tratamiento de datos</a>.
                    Puedo conocer, actualizar, rectificar y suprimir mis datos, y revocar esta autorización, escribiendo a coordinación.
                </span>
            </label>
            <x-input-error :messages="$errors->get('consent_data')" />

            {{-- RF-67: uso de imagen. Opcional: no impide la matrícula. --}}
            <label class="flex items-start gap-3">
                <input type="checkbox" name="consent_image" value="1" @checked(old('consent_image'))
                       class="mt-1 h-6 w-6 shrink-0 rounded border-2 border-line text-action focus:ring-action">
                <span class="text-sm">
                    Autorizo que el colegio tome fotos de mi hijo o hija en las actividades y las comparta solo con las familias
                    de su grupo que también lo autorizaron. Es opcional y puedo retirar esta autorización cuando quiera desde la app.
                </span>
            </label>                

            <label class="flex items-start gap-3">
                <input type="checkbox" name="consent_health" value="1" @checked(old('consent_health'))
                       @required(config('privacy.health_documents_required'))
                       class="mt-1 h-6 w-6 shrink-0 rounded border-2 border-line text-action focus:ring-action">
                <span class="text-sm">
                    Autorizo de forma expresa el tratamiento de los datos de salud de mi hijo o hija (carné de vacunas,
                    certificado de EPS, alergias, condiciones y medicamentos) para cuidar su salud y atender emergencias.
                    Sé que no estoy obligado a entregar datos sensibles.
                </span>
            </label>
            <x-input-error :messages="$errors->get('consent_health')" />
        </fieldset>

        <x-input-error :messages="$errors->get('website')" />

        <x-primary-button class="sm:w-full">Enviar solicitud</x-primary-button>
        <p class="text-center text-sm text-soft">Coordinación revisa cada solicitud y te escribe al correo.</p>
    </form>
</x-guest-layout>
