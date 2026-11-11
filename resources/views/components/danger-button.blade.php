<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger sm:w-auto']) }}>
    {{ $slot }}
</button>