<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Año escolar</h2>
            <a href="{{ route('admin.school-years.create') }}" class="btn btn-sm">+ Nuevo año escolar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @forelse ($years as $year)
                <section class="card">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-lg font-semibold">
                            {{ $year->year }}
                            @if ($year->is_current)
                                <span class="tag bg-ok-bg text-ok border border-ok">Actual</span>
                            @endif
                        </h3>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('admin.school-years.edit', $year) }}" class="text-sm underline">Editar fechas</a>
                            @unless ($year->is_current)
                                <form method="POST" action="{{ route('admin.school-years.current', $year) }}">
                                    @csrf
                                    <button class="text-sm underline">Marcar como actual</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                    <p class="text-sm text-soft">{{ $year->starts_on->format('d/m/Y') }} a {{ $year->ends_on->format('d/m/Y') }}</p>

                    <ul class="mt-3 space-y-2">
                        @foreach ($year->periods as $period)
                            @php($isNow = $year->is_current && $year->periodOn()?->is($period))
                            <li class="rounded-lg border-2 p-3 {{ $isNow ? 'border-amber bg-amber/10' : 'border-line' }}">
                                <p class="font-bold">{{ $period->name() }} @if ($isNow)<span class="tag bg-amber text-navy">En curso</span>@endif</p>
                                <p class="text-sm text-soft">
                                    {{ $period->starts_on->format('d/m/Y') }} a {{ $period->ends_on->format('d/m/Y') }}
                                    · Notas hasta el {{ $period->grades_due_on->format('d/m/Y') }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <div class="card py-6 text-center">
                    <x-mascot class="mx-auto" />
                    <p class="mt-2">Aún no hay año escolar. Créalo para empezar a registrar notas.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
