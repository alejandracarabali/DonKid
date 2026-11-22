<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $classroom->subjectKind() === \App\Enums\SubjectKind::Dimension ? 'Dimensiones' : 'Materias' }} de {{ $classroom->name }}
        </h2>
        <p class="text-soft">Marca lo que se dicta en este grupo y elige el docente de cada una.</p>
    </x-slot>

    <div class="py-8">
        @if (! $classroom->level)
            <p class="card mx-auto max-w-2xl">Este grupo no tiene nivel. <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="underline">Asígnale un nivel</a> para elegir sus materias.</p>
        @else
            <form method="POST" action="{{ route('admin.classrooms.subjects.update', $classroom) }}" class="card mx-auto max-w-2xl space-y-2">
                @csrf
                @method('PUT')
                @foreach ($subjects as $subject)
                    <div class="flex flex-wrap items-center gap-3 border-b border-line py-2">
                        <label class="flex grow items-center gap-2">
                            <input type="checkbox" name="subjects[{{ $subject->id }}][on]" value="1" @checked($assigned->has($subject->id))>
                            {{ $subject->name }}
                        </label>
                        <label for="e{{ $subject->id }}" class="sr-only">Docente de {{ $subject->name }}</label>
                        <select id="e{{ $subject->id }}" name="subjects[{{ $subject->id }}][educator_id]" class="rounded-md border-line">
                            <option value="">Sin docente</option>
                            @foreach ($educators as $educator)
                                <option value="{{ $educator->id }}" @selected($assigned->get($subject->id) === $educator->id)>{{ $educator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                @if ($subjects->isEmpty())
                    <p>No hay nada activo. <a href="{{ route('admin.subjects.index') }}" class="underline">Revisa las materias</a>.</p>
                @endif
                <x-input-error :messages="$errors->get('subjects.*')" />
                <div class="flex items-center gap-4 pt-2">
                    <button class="btn">Guardar</button>
                    <a href="{{ route('admin.classrooms.index') }}" class="text-sm underline">Cancelar</a>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
