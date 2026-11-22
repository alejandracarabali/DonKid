<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Renovación de matrícula</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto space-y-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="rounded-md bg-green-50 p-3 text-green-800" role="status">{{ session('status') }}</p>
            @endif

            @if ($year)
                <section class="card">
                    <p class="font-semibold">Renovación {{ $year }} abierta · {{ $renewed }} de {{ $children->count() }} renovados</p>
                    <p class="text-sm text-soft">Las familias ven el botón "Renovar matrícula" en su inicio. 5.° no renueva: termina primaria.</p>
                    <form method="POST" action="{{ route('admin.renewals.close') }}" class="mt-3">
                        @csrf
                        <button class="btn btn-sm btn-secondary">Cerrar renovación</button>
                    </form>
                </section>

                @foreach ($children as $child)
                    @php($renewal = $child->renewals->first())
                    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                        <div>
                            <p class="font-medium">{{ $child->first_name }} {{ $child->last_name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $child->classroom?->name ?? 'Sin grupo' }}
                                @if ($renewal) → {{ $renewal->next_level->label() }} @endif
                            </p>
                        </div>
                        @if ($renewal)
                            <span class="tag bg-ok-bg text-ok border border-ok">✓ Renovó</span>
                        @else
                            <span class="tag">Pendiente</span>
                        @endif
                    </div>
                @endforeach
            @else
                <section class="card">
                    <p>La renovación está cerrada. Ábrela para que las familias confirmen la matrícula del próximo año.</p>
                    <form method="POST" action="{{ route('admin.renewals.open') }}" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <x-input-label for="year" value="Año" />
                            <select id="year" name="year" class="block mt-1 min-h-[48px] rounded-xl border-2 border-line bg-white px-3">
                                <option value="{{ now()->year + 1 }}">{{ now()->year + 1 }}</option>
                                <option value="{{ now()->year }}">{{ now()->year }}</option>
                            </select>
                        </div>
                        <button class="btn btn-sm">Abrir renovación</button>
                    </form>
                    <x-input-error :messages="$errors->get('year')" class="mt-2" />
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
