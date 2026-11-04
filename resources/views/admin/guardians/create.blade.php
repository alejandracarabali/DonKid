<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva familia</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('admin.guardians.store') }}">
                @csrf
                @include('admin.guardians._form')

                <div class="mt-6 flex items-center gap-4">
                    <x-primary-button>Crear cuenta</x-primary-button>
                    <a href="{{ route('admin.guardians.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>