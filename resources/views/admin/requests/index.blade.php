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
                <div class="rounded-lg bg-white p-4 shadow-sm">
                    <p>
                        <strong>{{ $request->child?->first_name }} {{ $request->child?->last_name }}</strong>
                        <span class="text-sm text-soft">· {{ $request->child?->classroom?->name ?? 'Sin grupo' }}</span>
                        <span class="tag">{{ $request->type->label() }}</span>
                        <span class="tag">{{ $request->status->label() }}</span>
                    </p>
                    <p class="mt-1">{{ $request->summary() }}{{ $request->date ? ' · '.$request->date->format('d/m/Y') : '' }}</p>
                    @if ($request->note)
                        <p class="text-sm text-soft">{{ $request->note }}</p>
                    @endif
                    <p class="text-sm text-soft">De {{ $request->guardian?->name }} · {{ $request->created_at->format('d/m/Y H:i') }}</p>
                </div>
            @empty
                <p class="text-gray-600">No hay solicitudes con esos filtros.</p>
            @endforelse

            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
