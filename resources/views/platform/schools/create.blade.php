<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('platform.schools.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Colegios</a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo colegio</h2>
    </x-slot>

    @php($select = 'block mt-1 w-full min-h-[48px] rounded-xl border-2 border-line bg-white px-3')

    <div class="py-8">
        <form method="POST" action="{{ route('platform.schools.store') }}" class="card mx-auto max-w-xl space-y-6">
            @csrf

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold">Colegio</legend>
                <div>
                    <x-input-label for="name" value="Nombre" />
                    <x-text-input id="name" name="name" type="text" maxlength="120" class="mt-1 block w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="slug" value="Dirección (slug, opcional)" />
                    <x-text-input id="slug" name="slug" type="text" maxlength="60" class="mt-1 block w-full" :value="old('slug')" placeholder="Se arma con el nombre" />
                    <p class="mt-1 text-sm text-soft">Se usa en el enlace de inscripción: /inscripcion/<em>slug</em></p>
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="type" value="Tipo de institución" />
                    <select id="type" name="type" class="{{ $select }}" required>
                        <option value="">Elige el tipo</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="plan" value="Plan (opcional)" />
                    <x-text-input id="plan" name="plan" type="text" maxlength="30" class="mt-1 block w-full" :value="old('plan')" />
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold">Primera cuenta de coordinación</legend>
                <div>
                    <x-input-label for="admin_name" value="Nombre" />
                    <x-text-input id="admin_name" name="admin_name" type="text" maxlength="80" class="mt-1 block w-full" :value="old('admin_name')" required />
                    <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="admin_email" value="Correo" />
                    <x-text-input id="admin_email" name="admin_email" type="email" maxlength="120" class="mt-1 block w-full" :value="old('admin_email')" required />
                    <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                </div>
            </fieldset>

            <button class="btn">Crear colegio</button>
        </form>
    </div>
</x-app-layout>
