@props(['items'])

{{-- Menú de coordinación: columna lateral en computador, fila deslizable en celular. --}}
<nav aria-label="Menú de coordinación"
     class="bg-deep p-2 lg:sticky lg:top-6 lg:w-56 lg:shrink-0 lg:self-start lg:rounded-2xl">
    <ul class="flex gap-1 overflow-x-auto lg:flex-col lg:overflow-visible">
        @foreach ($items as $item)
            <li class="shrink-0">
                <a href="{{ $item['url'] }}"
                   @class([
                       'flex min-h-[48px] items-center whitespace-nowrap rounded-xl px-4 font-bold focus-visible:outline-amber',
                       'bg-amber text-navy' => $item['current'],
                       'text-white hover:bg-navy' => ! $item['current'],
                   ])
                   @if ($item['current']) aria-current="page" @endif>{{ $item['label'] }}</a>
            </li>
        @endforeach
    </ul>
</nav>