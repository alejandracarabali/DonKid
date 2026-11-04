<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Avisos</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif

            @forelse ($announcements as $announcement)
                <div class="bg-white p-4 rounded shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-lg">{{ $announcement->title }}</h3>
                        @unless ($announcement->read_by_me)
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Nuevo</span>
                        @endunless
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ $announcement->classroom?->name ?? 'Para todas las familias' }}
                        · {{ $announcement->published_at->translatedFormat('j \d\e F') }}
                    </p>
                    <p class="mt-2 text-gray-700 whitespace-pre-line">{{ $announcement->body }}</p>

                    @unless ($announcement->read_by_me)
                        <form method="POST" action="{{ route('guardian.announcements.read', $announcement) }}" class="mt-3">
                            @csrf
                            <x-primary-button>Ya lo leí</x-primary-button>
                        </form>
                    @endunless
                </div>
            @empty
                <p class="text-gray-600">Aún no hay avisos.</p>
            @endforelse

            {{ $announcements->links() }}
        </div>
    </div>
</x-app-layout>