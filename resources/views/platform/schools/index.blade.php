<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Colegios</h2>
            <a href="{{ route('platform.schools.create') }}" class="btn btn-sm">+ Nuevo colegio</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @if (session('temporal_password'))
                <div class="rounded-md bg-yellow-50 p-4 text-yellow-900" role="alert">
                    <p class="font-medium">Entrega estos datos a coordinación: solo se muestran ahora.</p>
                    <p class="mt-1">Correo: <strong>{{ session('temporal_password.email') }}</strong></p>
                    <p>Contraseña temporal: <strong>{{ session('temporal_password.password') }}</strong></p>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full rounded-lg bg-white text-left shadow-sm">
                    <thead class="bg-navy text-white">
                        <tr>
                            <th class="p-3">Colegio</th>
                            <th class="p-3">Tipo</th>
                            <th class="p-3">Plan</th>
                            <th class="p-3">Estudiantes activos</th>
                            <th class="p-3">Estado</th>
                            <th class="p-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schools as $school)
                            <tr class="border-t border-line">
                                <td class="p-3">
                                    <p class="font-medium">{{ $school->name }}</p>
                                    <p class="text-sm text-soft">/{{ $school->slug }}</p>
                                </td>
                                <td class="p-3">{{ $school->type->label() }}</td>
                                <td class="p-3">{{ $school->plan ?? '—' }}</td>
                                <td class="p-3">{{ $school->active_children_count }}</td>
                                <td class="p-3">
                                    @if ($school->isSuspended())
                                        <span class="tag bg-bad-bg text-bad border border-bad">Suspendido</span>
                                    @else
                                        <span class="tag bg-ok-bg text-ok border border-ok">Activo</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if ($school->isSuspended())
                                        <form method="POST" action="{{ route('platform.schools.reactivate', $school) }}">
                                            @csrf
                                            <button class="text-sm underline">Reactivar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('platform.schools.suspend', $school) }}"
                                              onsubmit="return confirm('¿Suspender {{ $school->name }}? Nadie del colegio podrá ingresar.')">
                                            @csrf
                                            <button class="text-sm text-bad underline">Suspender</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-6 text-center text-soft">Aún no hay colegios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
