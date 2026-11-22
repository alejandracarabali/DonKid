<x-guest-layout>
    <div class="text-center" role="status">
        <h1 class="text-2xl font-semibold">¡Listo, recibimos tu solicitud!</h1>
        <p class="mt-2">{{ $school->name }} la revisará y te escribirá al correo que dejaste.</p>

        <p class="mt-4 text-soft">Tu número de solicitud es</p>
        <p class="mt-1 rounded-[14px] border-[3px] border-navy p-4 font-display text-3xl font-bold tracking-wider">{{ $reference }}</p>
        <p class="mt-2 text-sm text-soft">Guárdalo por si necesitas preguntar por tu solicitud.</p>
    </div>
</x-guest-layout>
