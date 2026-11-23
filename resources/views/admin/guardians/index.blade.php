<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Familias</h2>
            <a href="{{ route('admin.guardians.create') }}"
               class="btn btn-sm">
                + Nueva familia
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
                <div class="mb-3 rounded-lg bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between sm:gap-4">
                    <div>
                        <p class="font-medium">{{ $guardian->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $guardian->email }} · {{ $guardian->children_count }} niños
                        </p>
                    </div>

                    <div x-data="{ confirming: false }" class="mt-3 sm:mt-0">
                        <div x-show="! confirming" class="flex gap-2">
                            <x-icon-button icon="edit" label="Editar" :context="$guardian->name" :href="route('admin.guardians.edit', $guardian)" />
                            <x-icon-button icon="trash" variant="danger" label="Quitar" :context="$guardian->name" x-on:click="confirming = true" />
                        </div>
                        {{-- Confirmación en la misma fila (reemplaza el confirm() del navegador). --}}
                        <form x-show="confirming" x-cloak method="POST" action="{{ route('admin.guardians.destroy', $guardian) }}"
                              class="flex flex-wrap items-center gap-2" role="alertdialog" aria-label="Confirmar que quitas {{ $guardian->name }}">
                            @csrf
                            @method('DELETE')
                            <p class="w-full text-sm font-bold text-bad sm:w-auto">¿Quitar la cuenta de {{ $guardian->name }}? No podrá volver a entrar.</p>
                            <button type="submit" class="min-h-[44px] rounded-xl border-2 border-bad bg-bad px-4 text-sm font-bold text-white">Sí, quitar</button>
                            <button type="button" x-on:click="confirming = false" class="min-h-[44px] rounded-xl border-2 border-navy px-4 text-sm font-bold text-navy">Cancelar</button>
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