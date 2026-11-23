{{--
    Tarjeta de una solicitud de familia, con lo importante resaltado.
    Uso:
      <x-request-card :request="$request" />                 (coordinación: solo consulta)
      <x-request-card :request="$request" actionable />      (director de grupo: puede marcarla)
--}}
@props(['request', 'actionable' => false])

@php
    $isNew = $request->status === \App\Enums\FamilyRequestStatus::Sent;
    $isPermission = $request->type === \App\Enums\FamilyRequestType::Permission;
    $typeClasses = match ($request->type) {
        \App\Enums\FamilyRequestType::Excuse => 'border-navy bg-canvas text-navy',
        \App\Enums\FamilyRequestType::Permission => 'border-navy bg-navy text-white',
        \App\Enums\FamilyRequestType::Recommendation => 'border-line bg-white text-soft',
    };
    $child = $request->child;
@endphp

<article @class(['rounded-2xl bg-white p-4 shadow-sm border-l-[6px]', 'border-amber' => $isNew, 'border-line' => ! $isNew])>
    {{-- 1. Quién y en qué estado --}}
    <header class="flex flex-wrap items-center gap-2">
        <h3 class="text-lg font-semibold text-navy">{{ $child?->first_name }} {{ $child?->last_name }}</h3>
        <span class="rounded-full border border-line px-2.5 py-0.5 text-xs font-bold text-soft">{{ $child?->classroom?->name ?? 'Sin grupo' }}</span>
        <span @class(['ms-auto rounded-full border-2 px-3 py-0.5 text-sm font-bold',
                      'border-navy bg-amber text-navy' => $isNew,
                      'border-ok bg-ok-bg text-ok' => ! $isNew])>{{ $isNew ? 'Nueva' : $request->status->label() }}</span>
    </header>

    {{-- 2. Qué es y para cuándo --}}
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="rounded-lg border-2 px-2.5 py-0.5 text-sm font-bold {{ $typeClasses }}">{{ $request->type->label() }}</span>
        <p class="text-lg font-bold text-ink">{{ $request->headline() }}</p>
        @if ($request->date)
            <span @class(['rounded-lg px-2.5 py-0.5 text-sm font-bold', 'bg-amber text-navy' => $request->date->isToday(), 'bg-canvas text-navy' => ! $request->date->isToday()])>
                {{ $request->date->isToday() ? 'Hoy' : $request->date->format('d/m/Y') }}
            </span>
        @endif
    </div>

    {{-- 3. El dato clave, de un vistazo --}}
    @if ($request->highlight())
        <p class="mt-3 inline-flex items-center gap-2 rounded-xl border-2 border-amber bg-amber/20 px-3 py-2 text-base font-bold text-navy">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            {{ $request->highlight() }}
        </p>
    @endif

    @if ($request->isPickupByOther())
        <p class="mt-2 text-sm text-soft">
            Código de recogida:
            <span class="rounded-lg bg-navy px-2.5 py-1 font-mono text-base font-bold tracking-widest text-white">{{ $request->pickup_code }}</span>
            · Tel. {{ $request->pickup_phone }} — pídeselo junto con el documento.
        </p>
    @endif

    @if ($request->note)
        <p class="mt-2 text-ink">{{ $request->note }}</p>
    @endif

    {{-- 4. Lo secundario, atenuado. La acción solo en las nuevas. --}}
    <footer class="mt-3 flex flex-wrap items-end justify-between gap-3">
        <p class="text-sm text-soft">De {{ $request->guardian?->name }} · {{ $request->created_at->diffForHumans() }}</p>
        @if ($actionable && $isNew)
            {{-- Ícono + texto: en el celular no hay tooltip, y esta es la acción principal. --}}
            <form method="POST" action="{{ route('educator.requests.handle', $request) }}">
                @csrf
                <button type="submit"
                        class="inline-flex min-h-[44px] items-center gap-2 rounded-xl border-2 border-navy bg-amber px-4 text-sm font-bold text-navy hover:brightness-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-action focus-visible:ring-offset-2">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                    {{ $isPermission ? 'Confirmar' : 'Marcar como vista' }}
                    <span class="sr-only">la solicitud de {{ $child?->first_name }}</span>
                </button>
            </form>
        @endif
    </footer>
</article>
