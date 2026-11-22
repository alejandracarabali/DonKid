{{-- Hilo de mensajes. $mine = id del usuario conectado (sus mensajes van a la derecha). --}}
<div class="space-y-2" aria-label="Conversación">
    @forelse ($messages as $message)
        @php($own = $message->sender_id === $mine)
        <div class="flex {{ $own ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[85%] rounded-2xl border-2 px-3 py-2 {{ $own ? 'border-amber bg-amber/10' : 'border-line bg-white' }}">
                <p class="whitespace-pre-line">{{ $message->body }}</p>
                <p class="mt-1 text-xs text-soft">
                    {{ $own ? 'Tú' : $message->sender?->name }} · {{ $message->created_at->timezone(config('messaging.timezone'))->format('d/m g:i a') }}
                    @if ($own && $message->isPending())
                        · Se entrega a las {{ \App\Support\MessagingHours::opensAtLabel() }}
                    @elseif ($own && $message->read_at)
                        · ✓ Leído
                    @endif
                </p>
            </div>
        </div>
    @empty
        <p class="card text-soft">Aún no hay mensajes. Escribe el primero.</p>
    @endforelse
</div>
