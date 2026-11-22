<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Mensajes</h1>
        <p class="text-soft">De las familias de tu grupo. Los enviados fuera de horario llegan a las 6:00 a. m.</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @forelse ($conversations as $conversation)
            <a href="{{ route('educator.messages.show', $conversation) }}" class="card flex items-center justify-between gap-3 hover:bg-canvas">
                <div>
                    <p class="font-bold">{{ $conversation->child?->first_name }} {{ $conversation->child?->last_name }}</p>
                    <p class="text-sm text-soft">{{ $conversation->guardian?->name }} · {{ $conversation->last_message_at?->timezone(config('messaging.timezone'))->format('d/m g:i a') }}</p>
                </div>
                @if ($conversation->unread_count)
                    <span class="tag bg-amber text-navy">{{ $conversation->unread_count }} {{ $conversation->unread_count === 1 ? 'nuevo' : 'nuevos' }}</span>
                @endif
            </a>
        @empty
            <div class="card py-6 text-center">
                <x-mascot class="mx-auto" />
                <p class="mt-2 text-soft">Aún no tienes mensajes de las familias.</p>
            </div>
        @endforelse

        {{ $conversations->links() }}
    </div>
</x-app-layout>
