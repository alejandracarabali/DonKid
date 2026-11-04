<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Familias</h2>
            <a href="{{ route('admin.guardians.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                Nueva familia
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <p class="mb-4 rounded-md bg-green-50 p-3 text-green-800" role="status">
                    {{ session('status') }}
                </p>
            @endif

            @if (session('temporal_password'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4 text-yellow-900" role="alert">
                    <p class="font-medium">Entrega estos datos a la familia. Solo se muestran ahora.</p>
                    <p class="mt-1">Correo: <strong>{{ session('temporal_password.email') }}</strong></p>
                    <p>Contraseña temporal: <strong>{{ session('temporal_password.password') }}</strong></p>
                </div>
            @endif

            @forelse ($guardians as $guardian)
                <div class="mb-3 flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-medium">{{ $guardian->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $guardian->email }} · {{ $guardian->children_count }} niños
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.guardians.edit', $guardian) }}" class="text-sm underline">Editar</a>

                        <form method="POST" action="{{ route('admin.guardians.destroy', $guardian) }}"
                              onsubmit="return confirm('¿Quitar la cuenta de {{ $guardian->name }}? No podrá volver a entrar.')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-600 underline">Quitar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Aún no hay familias. Crea la primera.</p>
            @endforelse

            {{ $guardians->links() }}
        </div>
    </div>
</x-app-layout>