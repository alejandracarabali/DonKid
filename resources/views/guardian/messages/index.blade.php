<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Mensajes</h1>
        <p class="text-soft">Escríbele al director de grupo de tu hijo o hija.</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @forelse ($children as $child)
            <a href="{{ route('guardian.messages.show', $child) }}" class="card flex items-center justify-between gap-3 hover:bg-canvas">
                <div>
                    <p class="font-bold">{{ $child->first_name }} {{ $child->last_name }}</p>
                    <p class="text-sm text-soft">
                        {{ $child->classroom?->director ? 'Director de grupo: '.$child->classroom->director->name : 'Aún sin director de grupo' }}
                    </p>
                </div>
                @if ($unread[$child->id] ?? 0)
                    <span class="tag bg-amber text-navy">{{ $unread[$child->id] }} {{ $unread[$child->id] === 1 ? 'nuevo' : 'nuevos' }}</span>
                @endif
            </a>
        @empty
            <p class="card text-soft">Aún no tienes hijos vinculados.</p>
        @endforelse
    </div>
</x-app-layout>
