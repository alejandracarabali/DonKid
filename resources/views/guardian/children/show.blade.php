<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Hoy con {{ $child->first_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <p class="text-gray-600">Aún no hay novedades de hoy. Te avisamos cuando haya.</p>
        </div>
    </div>

    {{-- Autorización de uso de imagen: la familia la da o la retira cuando quiera. --}}
    <section class="card mx-auto my-4 max-w-3xl" aria-labelledby="fotos-titulo">
        <h3 id="fotos-titulo" class="text-lg font-semibold">Fotos de las actividades</h3>

        @if (session('status'))
            <p class="mt-2 rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">✓ {{ session('status') }}</p>
        @endif

        @if ($child->hasImageConsent())
            <p class="mt-2">
                <strong>Autorizaste</strong> que el colegio comparta contigo fotos de las actividades donde aparece {{ $child->first_name }}
                (desde el {{ $child->image_consent_at->format('d/m/Y') }}).
            </p>
            <p class="mt-1 text-sm text-soft">Las fotos solo las ven las familias del grupo que también autorizaron. Puedes retirar la autorización cuando quieras.</p>
            <form method="POST" action="{{ route('guardian.children.image-consent', $child) }}" class="mt-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="consent" value="0">
                <button class="btn btn-secondary btn-sm">Retirar autorización</button>
            </form>
        @else
            <p class="mt-2">
                Sin tu autorización, el colegio <strong>no toma ni comparte</strong> fotos de {{ $child->first_name }}, y no verás las fotos de las actividades.
            </p>
            <form method="POST" action="{{ route('guardian.children.image-consent', $child) }}" class="mt-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="consent" value="1">
                <button class="btn btn-sm">Autorizar fotos</button>
            </form>
        @endif
    </section>    

    @if ($child->hasDailyLog())
        <a href="{{ route('guardian.reports.index', $child) }}"
            class="inline-block bg-indigo-600 text-white px-4 py-3 rounded">
            Ver la bitácora
        </a>
    @endif
</x-app-layout>