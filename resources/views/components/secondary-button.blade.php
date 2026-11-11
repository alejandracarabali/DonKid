<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary sm:w-auto']) }}>
    {{ $slot }}
</button>