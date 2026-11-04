<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar niño</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('admin.children.update', $child) }}">
                @csrf
                @method('PATCH')
                @include('admin.children._form')

                <div class="mt-6 flex items-center gap-4">
                    <x-primary-button>Guardar cambios</x-primary-button>
                    <a href="{{ route('admin.children.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>