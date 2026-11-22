<section class="card border-amber" aria-labelledby="solicitudes-titulo">
    <h2 id="solicitudes-titulo" class="text-[17px] font-medium">Solicitudes de familias</h2>

    @foreach ($requests as $request)
        <div class="mt-3 border-t border-line pt-3">
            <p>
                <strong>{{ $request->child?->first_name }}</strong>
                <span class="tag bg-amber text-navy">{{ $request->type->label() }}</span>
                @if ($request->date)
                    <span class="text-sm text-soft">{{ $request->date->isToday() ? 'Hoy' : $request->date->format('d/m/Y') }}{{ $request->time ? ' · '.$request->time : '' }}</span>
                @endif
            </p>
            <p class="mt-1">{{ $request->summary() }}</p>
            @if ($request->isPickupByOther())
                <p class="text-sm text-soft">Tel. {{ $request->pickup_phone }} · <strong>Código {{ $request->pickup_code }}</strong> — pídeselo junto con el documento.</p>
            @endif
            @if ($request->note)
                <p class="text-sm text-soft">{{ $request->note }}</p>
            @endif
            <form method="POST" action="{{ route('educator.requests.handle', $request) }}" class="mt-2">
                @csrf
                <button class="btn btn-sm">{{ $request->type === \App\Enums\FamilyRequestType::Permission ? 'Confirmar' : 'Visto' }}</button>
            </form>
        </div>
    @endforeach
</section>
