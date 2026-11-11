{{-- Botón principal DonKid: ancho completo en celular, ancho automático desde tablet. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn sm:w-auto']) }}>
    {{ $slot }}
</button>