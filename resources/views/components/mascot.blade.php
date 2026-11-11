@props(['size' => 110, 'label' => null, 'sparkles' => false])

{{-- Mascota de DonKid. Uso: <x-mascot />, <x-mascot size="48" />, <x-mascot sparkles label="DonKid" /> --}}
<svg {{ $attributes->merge(['width' => $size, 'height' => $size]) }}
     viewBox="0 0 60 60"
     @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" focusable="false" @endif>
    <circle cx="30" cy="31" r="22" fill="#fff" stroke="#0B1F5C" stroke-width="3"/>
    <rect x="12" y="18" width="36" height="20" rx="10" fill="#0B1F5C" stroke="#FFB020" stroke-width="2"/>
    <path d="M19 29q4-6 8 0M33 29q4-6 8 0" fill="none" stroke="#FFC93C" stroke-width="3" stroke-linecap="round"/>
    <ellipse cx="30" cy="42" rx="7" ry="5" fill="#fff" stroke="#0B1F5C" stroke-width="2"/>
    <path d="M26 42q4 4 8 0" fill="#F4707F" stroke="#0B1F5C" stroke-width="1.5"/>
    <circle cx="8" cy="31" r="5" fill="#fff" stroke="#0B1F5C" stroke-width="2.5"/>
    <circle cx="52" cy="31" r="5" fill="#fff" stroke="#0B1F5C" stroke-width="2.5"/>
    @if ($sparkles)
        <path d="M52 8l3-4M56 14l5-1M50 3l1-4" stroke="#FFB020" stroke-width="3" stroke-linecap="round"/>
    @endif
</svg>