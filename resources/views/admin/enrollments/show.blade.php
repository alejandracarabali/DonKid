<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('admin.enrollments.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Inscripciones</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $application->child_first_name }} {{ $application->child_last_name }}
        </h2>
        <p class="text-soft">{{ $application->reference() }} · {{ $application->status->label() }}</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto space-y-4 sm:px-6 lg:px-8">

            <section class="card">
                <h3 class="text-lg font-semibold">Niño o niña</h3>
                <dl class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <div><dt class="text-sm text-soft">Fecha de nacimiento</dt>
                        <dd>{{ $application->child_birth_date->format('d/m/Y') }} ({{ $application->child_birth_date->age }} años)</dd></div>
                    <div><dt class="text-sm text-soft">Nivel al que aspira</dt><dd>{{ $application->desired_level?->label() }}</dd></div>
                </dl>
            </section>

            <section class="card">
                <h3 class="text-lg font-semibold">Acudiente</h3>
                <dl class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <div><dt class="text-sm text-soft">Nombre</dt><dd>{{ $application->guardian_name }} ({{ $application->guardian_relationship }})</dd></div>
                    <div><dt class="text-sm text-soft">Documento</dt>
                        <dd>{{ $application->guardian_document_type->label() }} {{ $application->guardian_document_number }}</dd></div>
                    <div><dt class="text-sm text-soft">Correo</dt><dd>{{ $application->guardian_email }}</dd></div>
                    <div><dt class="text-sm text-soft">Teléfono</dt><dd>{{ $application->guardian_phone }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-soft">Dirección</dt><dd>{{ $application->guardian_address }}</dd></div>
                </dl>
            </section>

            <section class="card">
                <h3 class="text-lg font-semibold">Documentos</h3>
                <ul class="mt-2 space-y-2">
                    @foreach ($application->documents as $doc)
                        <li>
                            <a href="{{ route('admin.enrollments.document', [$application, $doc]) }}" target="_blank" rel="noopener"
                               class="font-bold text-action underline">{{ $doc->type->label() }}</a>
                            <span class="text-sm text-soft">· {{ number_format($doc->size / 1024, 0) }} KB</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="card">
                <h3 class="text-lg font-semibold">Autorización de datos</h3>
                <p class="mt-1 text-sm">
                    General: {{ $application->data_consent_at?->format('d/m/Y H:i') ?? 'No' }}
                    · Salud: {{ $application->health_consent_at?->format('d/m/Y H:i') ?? 'No autorizó' }}
                    · Versión {{ $application->consent_policy_version }} · IP {{ $application->consent_ip }}
                </p>
            </section>

            @if ($application->status === \App\Enums\EnrollmentStatus::Pending)
                <section class="card">
                    <h3 class="text-lg font-semibold">Aprobar</h3>
                    <p class="text-sm text-soft">Se crea el niño en el grupo elegido y la cuenta de la familia (o se vincula a la que ya tenga).</p>
                    <form method="POST" action="{{ route('admin.enrollments.approve', $application) }}" class="mt-3">
                        @csrf
                        <x-input-label for="classroom_id" value="Grupo" />
                        <select id="classroom_id" name="classroom_id" required
                                class="block mt-1 w-full min-h-[48px] rounded-xl border-2 border-line bg-white px-3">
                            <option value="">Elige el grupo</option>
                            @foreach ($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>
                                    {{ $classroom->name }}{{ $classroom->level ? ' · '.$classroom->level->label() : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
                        <button class="btn mt-4">Aprobar matrícula</button>
                    </form>
                </section>

                <section class="card">
                    <h3 class="text-lg font-semibold">Rechazar</h3>
                    <form method="POST" action="{{ route('admin.enrollments.reject', $application) }}" class="mt-3">
                        @csrf
                        <x-input-label for="rejection_reason" value="Motivo" />
                        <x-text-input id="rejection_reason" name="rejection_reason" type="text" maxlength="200"
                                      class="block mt-1 w-full" :value="old('rejection_reason')" />
                        <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
                        <button class="btn btn-danger mt-4">Rechazar solicitud</button>
                    </form>
                </section>
            @elseif ($application->status === \App\Enums\EnrollmentStatus::Approved && $application->child)
                <p class="card">Matriculado en {{ $application->child->classroom?->name ?? 'sin grupo' }}
                    el {{ $application->reviewed_at->format('d/m/Y') }}.</p>
            @else
                <p class="card">Rechazada el {{ $application->reviewed_at?->format('d/m/Y') }}: {{ $application->rejection_reason }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
