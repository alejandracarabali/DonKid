{{--
    Botón de solo ícono con tooltip (aparece con el cursor y con el teclado).
    Ruta: resources/views/components/icon-button.blade.php

    Uso:
      <x-icon-button icon="edit" label="Editar" context="{{ $child->name }}" :href="route('admin.children.edit', $child)" />
      <x-icon-button type="submit" icon="save" variant="soft" label="Guardar cambios" />

    Variantes: default | soft | danger (rojo) | primary (ámbar)
    Íconos: edit, list, trash, save, power, eye, user-minus, archive, check, clock
    `context` se agrega al nombre accesible: "Editar Alexandra Jurado".
--}}
@props(['label', 'icon', 'variant' => 'default', 'href' => null, 'type' => 'button', 'context' => null])

@php
    $classes = 'group relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-action focus-visible:ring-offset-2 '.[
        'default' => 'border-navy text-navy hover:bg-canvas',
        'soft'    => 'border-line bg-canvas text-navy hover:border-navy',
        'danger'  => 'border-bad text-bad hover:bg-bad-bg',
        'primary' => 'border-navy bg-amber text-navy hover:brightness-95',
    ][$variant];

    $paths = [
        'edit'       => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/>',
        'list'       => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
        'trash'      => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/>',
        'save'       => '<path d="M5 12l5 5L20 7"/>',
        'check'      => '<path d="M5 12l5 5L20 7"/>',
        'power'      => '<path d="M12 3v9"/><path d="M6.4 7a8 8 0 1011.2 0"/>',
        'eye'        => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'user-minus' => '<circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0114 0"/><path d="M17 11h6"/>',
        'archive'    => '<path d="M3 5h18v4H3z"/><path d="M5 9v10h14V9"/><path d="M10 13h4"/>',
        'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ];
    $name = $label.($context ? ' '.$context : '');
@endphp

@if ($href)
    <a href="{{ $href }}" aria-label="{{ $name }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
    <button type="{{ $type }}" aria-label="{{ $name }}" {{ $attributes->merge(['class' => $classes]) }}>
@endif
    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$icon] !!}</svg>
    <span aria-hidden="true" class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-deep px-2.5 py-1 text-xs font-bold text-white opacity-0 shadow transition motion-reduce:transition-none group-hover:opacity-100 group-focus-visible:opacity-100">{{ $label }}</span>
@if ($href)
    </a>
@else
    </button>
@endif
