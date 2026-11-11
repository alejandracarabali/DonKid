<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bitácora de hoy</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @if (session('status'))
                <div class="p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif

            @forelse ($children as $child)
                @php($done = $child->dailyReports->isNotEmpty())
                <a href="{{ route('educator.reports.create', $child) }}"
                   class="flex items-center justify-between bg-white p-4 rounded shadow hover:bg-gray-50">
                    <span class="text-lg">{{ $child->first_name }} {{ $child->last_name }}</span>
                    <span class="{{ $done ? 'text-green-700' : 'text-gray-500' }}">
                        {{ $done ? '✓ Enviado' : 'Pendiente' }}
                    </span>
                </a>
            @empty
                <p class="text-gray-600">Aún no tienes niños asignados.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>