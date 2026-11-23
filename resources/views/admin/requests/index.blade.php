<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Solicitudes de familias</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-4 sm:px-6 lg:px-8">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo" class="block mt-1 min-h-[48px] rounded-xl border-2 border-line bg-white px-3">
                        <option value="">Todos</option>
                        @foreach (\App\Enums\FamilyRequestType::cases() as $t)
                            <option value="{{ $t->value }}" @selected($type === $t)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado" class="block mt-1 min-h-[48px] rounded-xl border-2 border-line bg-white px-3">
                        <option value="">Todos</option>
                        @foreach (\App\Enums\FamilyRequestStatus::cases() as $s)
                            <option value="{{ $s->value }}" @selected($status === $s)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-sm">Filtrar</button>
            </form>

            @forelse ($requests as $request)
                <x-request-card :request="$request" />
            @empty
                <p class="text-gray-600">No hay solicitudes con esos filtros.</p>
            @endforelse

            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
