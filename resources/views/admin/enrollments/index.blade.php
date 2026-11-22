<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inscripciones</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <p class="mb-4 rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @if (session('temporal_password'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4 text-yellow-900" role="alert">
                    <p class="font-medium">Se creó la cuenta de la familia. Entrégale estos datos: solo se muestran ahora.</p>
                    <p class="mt-1">Correo: <strong>{{ session('temporal_password.email') }}</strong></p>
                    <p>Contraseña temporal: <strong>{{ session('temporal_password.password') }}</strong></p>
                </div>
            @endif

            {{-- Filtro por estado --}}
            <nav class="mb-4 flex gap-2" aria-label="Estado de las solicitudes">
                @foreach (\App\Enums\EnrollmentStatus::cases() as $s)
                    <a href="{{ route('admin.enrollments.index', ['estado' => $s->value]) }}"
                       @if ($s === $status) aria-current="page" @endif
                       class="btn btn-sm {{ $s === $status ? '' : 'btn-secondary' }}">
                        {{ $s->label() }} ({{ $counts[$s->value] ?? 0 }})
                    </a>
                @endforeach
            </nav>

            @forelse ($applications as $application)
                <a href="{{ route('admin.enrollments.show', $application) }}"
                   class="mb-3 flex items-center justify-between rounded-lg bg-white p-4 shadow-sm hover:bg-canvas">
                    <div>
                        <p class="font-medium">{{ $application->child_first_name }} {{ $application->child_last_name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $application->reference() }} · {{ $application->desired_level?->label() }}
                            · Acudiente: {{ $application->guardian_name }} · {{ $application->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="text-sm underline">Revisar</span>
                </a>
            @empty
                <p class="text-gray-600">No hay solicitudes {{ mb_strtolower($status->label()) }}s.</p>
            @endforelse

            {{ $applications->links() }}
        </div>
    </div>
</x-app-layout>
