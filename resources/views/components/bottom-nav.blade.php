@props(['items'])

{{-- Barra inferior (docente y familia). Fija abajo, al alcance del pulgar. --}}
<nav aria-label="Navegación principal"
     class="fixed inset-x-0 bottom-0 z-30 border-t-2 border-line bg-white px-2 pt-1.5 pb-[calc(0.375rem+env(safe-area-inset-bottom))]">
    <ul class="mx-auto flex max-w-md gap-1">
        @foreach ($items as $item)
            <li class="flex-1">
                <a href="{{ $item['url'] }}"
                   @class([
                       'relative flex min-h-[56px] items-center justify-center rounded-xl px-1 text-center text-sm font-bold',
                       'bg-amber text-navy' => $item['current'],
                       'text-soft hover:bg-canvas' => ! $item['current'],
                   ])
                   @if ($item['current']) aria-current="page" @endif>{{ $item['label'] }}@if ($item['badge'] > 0)<span class="absolute right-2 top-1 grid h-[22px] min-w-[22px] place-items-center rounded-full bg-bad px-1 text-xs text-white" aria-label="{{ $item['badge'] }} sin leer">{{ $item['badge'] }}</span>@endif</a>
            </li>
        @endforeach
    </ul>
</nav>