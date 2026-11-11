@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok']) }} role="status">
        ✓ {{ $status }}
    </div>
@endif