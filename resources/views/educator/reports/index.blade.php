<x-app-layout>
    <x-slot name="header">
        <h1 class="text-[26px] font-semibold">Bitácora de hoy</h1>
        <p class="text-soft">
            {{ \Illuminate\Support\Str::ucfirst(today()->locale(app()->getLocale())->isoFormat('dddd D [de] MMMM')) }}
        </p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @if (session('status'))
            <div class="rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">
                ✓ {{ session('status') }}
            </div>
        @endif

        @if ($total === 0)
            <div class="card py-6 text-center">
                <x-mascot class="mx-auto" />
                <h2 class="mt-2 text-xl">Aún no tienes estudiantes asignados</h2>
                <p class="text-soft">La coordinación te asigna los grupos.</p>
            </div>
        @else
            {{-- Avance del día --}}
            <div class="card">
                @if ($sent === $total)
                    <div class="text-center">
                        <x-mascot class="mx-auto" sparkles />
                        <h2 class="mt-1 text-xl">¡Todo el grupo al día!</h2>
                    </div>
                @else
                    <p class="font-bold">{{ $sent }} de {{ $total }} enviados</p>
                @endif

                {{-- El ancho es dinámico, por eso va en style: Tailwind no puede generar "w-[37%]" al vuelo. --}}
                <div class="mt-2 h-3.5 overflow-hidden rounded-full bg-line"
                     role="progressbar" aria-label="Bitácoras enviadas hoy"
                     aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $sent }}">
                    <div @class(['h-full transition-all', 'bg-ok' => $sent === $total, 'bg-amber' => $sent !== $total])
                         style="width: {{ round($sent / $total * 100) }}%"></div>
                </div>
            </div>

            {{-- Lista: pendientes primero --}}
            <ul class="space-y-3">
                @foreach ($children as $child)
                    @php($done = $child->dailyReports->isNotEmpty())
                    <li>
                        <a href="{{ route('educator.reports.create', $child) }}"
                           class="card flex min-h-[64px] items-center gap-3 text-[17px] font-bold hover:border-action">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border-2 border-navy bg-amber font-display text-xl text-navy"
                                  aria-hidden="true">{{ mb_substr($child->first_name, 0, 1) }}</span>
                            <span>{{ $child->first_name }} {{ $child->last_name }}</span>
                            @if ($done)
                                <span class="tag tag-ok ms-auto">✓ Enviado</span>
                            @else
                                <span class="tag tag-pending ms-auto">○ Pendiente</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>