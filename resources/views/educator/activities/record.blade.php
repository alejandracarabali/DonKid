<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('educator.activities.index', ['semana' => $activity->starts_at->toDateString()]) }}"
           class="inline-flex min-h-[44px] items-center font-bold text-action">← Actividades</a>
        <h1 class="text-[26px] font-semibold">{{ $activity->title }}</h1>
        <p class="text-soft">{{ $activity->classroom->name }} · {{ $activity->starts_at->translatedFormat('l j \d\e F, H:i') }}</p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-4 px-4 py-4 sm:px-6 lg:px-0">
        @if (session('status'))
            <div class="rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">✓ {{ session('status') }}</div>
        @endif

        @if ($activity->isDone())
            <p><span class="tag bg-ok-bg text-ok border border-ok">✓ Hecha el {{ $activity->done_at->format('d/m/Y H:i') }}</span></p>
        @endif

        {{-- Antes de tomar fotos: a quién NO fotografiar (RF-67). --}}
        @if ($withoutConsent->isNotEmpty())
            <section class="rounded-xl border-2 border-bad bg-bad-bg p-3" aria-labelledby="sin-autorizacion">
                <h2 id="sin-autorizacion" class="font-bold text-bad">No deben aparecer en las fotos</h2>
                <p class="text-sm">Su familia no autorizó el uso de imagen:</p>
                <p class="mt-1 font-bold">{{ $withoutConsent->map(fn ($c) => $c->first_name.' '.$c->last_name)->join(', ') }}</p>
            </section>
        @endif

        <form method="POST" action="{{ route('educator.activities.record.update', $activity) }}" enctype="multipart/form-data" class="card space-y-4">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="done_note" value="¿Cómo les fue? (opcional)" />
                <textarea id="done_note" name="done_note" rows="3" maxlength="1000"
                          class="mt-1 block w-full rounded-xl border-line"
                          placeholder="Ej.: pintaron con témperas y armaron un mural entre todos.">{{ old('done_note', $activity->done_note) }}</textarea>
                <x-input-error :messages="$errors->get('done_note')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="photos" value="Fotos (opcional)" />
                <input id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm">
                <p class="mt-1 text-sm text-soft">
                    Hasta {{ \App\Http\Controllers\Educator\ActivityRecordController::MAX_PHOTOS }} fotos por actividad, de 5 MB cada una.
                    Solo las ven las familias del grupo que autorizaron.
                </p>
                <x-input-error :messages="array_merge($errors->get('photos'), $errors->get('photos.*'))" class="mt-2" />
            </div>

            <button class="btn w-full">{{ $activity->isDone() ? 'Guardar cambios' : 'Marcar como hecha' }}</button>
        </form>

        @if ($activity->photos->isNotEmpty())
            <section class="card" aria-labelledby="fotos-subidas">
                <h2 id="fotos-subidas" class="text-lg font-semibold">Fotos ({{ $activity->photos->count() }})</h2>
                <ul class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($activity->photos as $photo)
                        <li class="relative">
                            <img src="{{ route('activities.photos.show', [$activity, $photo]) }}" alt="Foto {{ $loop->iteration }} de {{ $activity->title }}"
                                 class="aspect-square w-full rounded-xl border-2 border-line object-cover" loading="lazy">
                            <form method="POST" action="{{ route('educator.activities.photos.destroy', [$activity, $photo]) }}" class="absolute right-2 top-2">
                                @csrf
                                @method('DELETE')
                                <x-icon-button type="submit" icon="trash" variant="danger" label="Quitar foto" :context="(string) $loop->iteration" class="bg-white" />
                            </form>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-app-layout>