<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl text-ink">Guía de estilos</h2>
    </x-slot>

    @php
        // Clases escritas completas a propósito: Tailwind solo compila las clases
        // que encuentra escritas tal cual en los archivos (no "bg-{{ $color }}").
        $colores = [
            'bg-navy' => 'navy', 'bg-deep' => 'deep', 'bg-action' => 'action',
            'bg-amber' => 'amber', 'bg-amber-dark' => 'amber-dark', 'bg-canvas' => 'canvas',
            'bg-soft' => 'soft', 'bg-line' => 'line', 'bg-ok' => 'ok', 'bg-bad' => 'bad',
        ];
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section class="card">
                <h3 class="mb-3 text-lg">Mascota</h3>
                <div class="flex items-end gap-6">
                    <x-mascot />
                    <x-mascot size="64" sparkles />
                    <x-mascot size="40" label="DonKid" />
                </div>
            </section>

            <section class="card">
                <h3 class="mb-3 text-lg">Tipografía</h3>
                <h1 class="text-[26px] font-semibold">Título Fredoka 26</h1>
                <h2 class="text-[21px] font-semibold">Subtítulo Fredoka 21</h2>
                <p class="mt-2 text-soft">Texto en Nunito: así se leerá la bitácora de cada día.</p>
            </section>

            <section class="card">
                <h3 class="mb-3 text-lg">Colores</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @foreach ($colores as $clase => $nombre)
                        <div class="text-center text-sm font-bold">
                            <div class="{{ $clase }} mb-1 h-12 rounded-xl border-2 border-line"></div>
                            {{ $nombre }}
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card space-y-3">
                <h3 class="text-lg">Botones</h3>
                <button class="btn">Botón principal: Enviar a la familia</button>
                <button class="btn btn-secondary">Botón secundario</button>
                <div class="flex flex-wrap gap-3">
                    <button class="btn btn-sm">Pequeño</button>
                    <button class="btn btn-sm btn-secondary">Ya lo leí</button>
                </div>
                <button class="btn" disabled>Deshabilitado</button>
            </section>

            <section class="card">
                <h3 class="mb-3 text-lg">Estados</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="tag tag-new">★ Nuevo</span>
                    <span class="tag tag-ok">✓ Enviado</span>
                    <span class="tag tag-pending">○ Pendiente</span>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>