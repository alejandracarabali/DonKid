<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('guardian.requests.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Solicitudes</a>
        <h1 class="text-[26px] font-semibold">Solicitudes de {{ $child->first_name }}</h1>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @if (session('status'))
            <div class="rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">✓ {{ session('status') }}</div>
        @endif

        @if (session('pickup_code'))
            <div class="card text-center" role="status">
                <p>Comparte este código con quien recogerá a {{ $child->first_name }}:</p>
                <p class="my-2 rounded-[14px] border-[3px] border-navy p-4 font-display text-4xl font-bold tracking-widest">{{ session('pickup_code') }}</p>
                <p class="text-sm text-soft">El colegio le pedirá el código y su documento al recoger.</p>
            </div>
        @endif

        @foreach ($types as $type)
            <a href="{{ route('guardian.requests.create', [$child, $type->value]) }}" class="card flex min-h-[64px] items-center justify-between hover:bg-canvas">
                <span><span class="block font-bold">{{ $type->label() }}</span><span class="block text-sm text-soft">{{ $type->hint() }}</span></span>
                <span class="text-2xl" aria-hidden="true">+</span>
            </a>
        @endforeach

        <h2 class="pt-3 text-xl">Enviadas</h2>
        @forelse ($sent as $request)
            <div class="card">
                <span class="tag">{{ $request->type->label() }}</span>
                <span class="tag {{ $request->status === \App\Enums\FamilyRequestStatus::Sent ? '' : 'bg-ok-bg text-ok border border-ok' }}">{{ $request->status->label() }}</span>
                @if ($request->date)
                    <span class="text-sm text-soft">{{ $request->date->format('d/m/Y') }}</span>
                @endif
                <p class="mt-1 font-bold">{{ $request->summary() }}</p>
                @if ($request->isPickupByOther())
                    <p class="text-sm text-soft">Código {{ $request->pickup_code }}</p>
                @endif
            </div>
        @empty
            <p class="text-soft">Aún no has enviado solicitudes.</p>
        @endforelse
    </div>
</x-app-layout>
