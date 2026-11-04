<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bitácora de {{ $child->first_name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($reports as $report)
                <div class="bg-white p-4 rounded shadow">
                    <p class="text-sm text-gray-500">
                        {{ $report->report_date->translatedFormat('l j \d\e F') }}
                    </p>
                    <div class="flex flex-wrap gap-4 mt-2 text-lg">
                        @if ($report->mood)
                            <span>{{ $report->mood->icon() }} {{ $report->mood->label() }}</span>
                        @endif
                        @if ($report->meal_status)
                            <span>{{ $report->meal_status->icon() }} {{ $report->meal_status->label() }}</span>
                        @endif
                        @if ($report->nap_minutes !== null)
                            <span>😴 Siesta: {{ $report->nap_minutes }} min</span>
                        @endif
                    </div>
                    @if ($report->note)
                        <p class="mt-2 text-gray-700">{{ $report->note }}</p>
                    @endif
                </div>
            @empty
                <p class="text-gray-600">Todavía no hay reportes de {{ $child->first_name }}.</p>
            @endforelse

            {{ $reports->links() }}
        </div>
    </div>
</x-app-layout>