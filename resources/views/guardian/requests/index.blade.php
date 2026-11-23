<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Solicitudes</h1>
        <p class="text-soft">Avísale al colegio con pocos toques.</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @forelse ($children as $child)
            <a href="{{ route('guardian.requests.child', $child) }}" class="card flex min-h-[64px] items-center justify-between hover:bg-canvas">
                <span class="font-bold">{{ $child->first_name }} {{ $child->last_name }}</span>
                <span aria-hidden="true">›</span>
            </a>
        @empty
            <p class="card text-soft">Aún no tienes hijos vinculados.</p>
        @endforelse
    </div>
</x-app-layout>
