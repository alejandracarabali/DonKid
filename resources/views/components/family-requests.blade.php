<section aria-labelledby="solicitudes-titulo" class="space-y-3">
    <h2 id="solicitudes-titulo" class="text-[17px] font-medium">Solicitudes de familias</h2>

    @foreach ($requests as $request)
        <x-request-card :request="$request" actionable />
    @endforeach
</section>
