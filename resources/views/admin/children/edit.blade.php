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

        {{-- RF-53: retirar inhabilita al niño; su información no se borra. --}}
        <div class="max-w-xl mx-auto mt-6 sm:px-6 lg:px-8 bg-white p-6 shadow-sm sm:rounded-lg">
            <h3 class="font-semibold text-gray-800">Retirar de la institución</h3>
            <p class="mt-1 text-sm text-gray-600">
                {{ $child->first_name }} dejará de aparecer en grupos, bitácora y avisos.
                Su historial se conserva y puedes reactivarlo si regresa.
            </p>

            <form method="POST" action="{{ route('admin.children.withdraw', $child) }}" class="mt-4">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="withdrawn_at" value="Fecha de retiro" />
                    <x-text-input id="withdrawn_at" name="withdrawn_at" type="date" class="block mt-1 w-full"
                                  :value="old('withdrawn_at', today()->toDateString())" required />
                    <x-input-error :messages="$errors->get('withdrawn_at')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="withdrawal_reason" value="Motivo (opcional)" />
                    <x-text-input id="withdrawal_reason" name="withdrawal_reason" type="text" class="block mt-1 w-full"
                                  maxlength="200" :value="old('withdrawal_reason')" />
                    <x-input-error :messages="$errors->get('withdrawal_reason')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-danger-button>Retirar a {{ $child->first_name }}</x-danger-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>