<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('guardian.messages.index') }}" class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline">← Mensajes</a>
        <h1 class="text-[26px] font-semibold">{{ $child->first_name }}</h1>
        <p class="text-soft">
            {{ $child->classroom?->director ? 'Con '.$child->classroom->director->name.', director de grupo' : 'Con el docente del grupo' }}
        </p>
    </x-slot>

    <div class="mx-auto max-w-3xl space-y-3 px-4 py-4 sm:px-6 lg:px-0">
        @if (session('status'))
            <div class="rounded-xl border-2 border-ok bg-ok-bg px-3 py-2 font-bold text-ok" role="status">✓ {{ session('status') }}</div>
        @endif

        @include('messages._thread', ['messages' => $messages, 'mine' => auth()->id()])

        <form method="POST" action="{{ route('guardian.messages.store', $child) }}" class="card">
            @csrf
            <label for="body" class="font-bold">Tu mensaje</label>
            <textarea id="body" name="body" rows="3" maxlength="1000" required
                      class="mt-1 block w-full rounded-xl border-2 border-line text-base focus:border-action focus:ring-action">{{ old('body') }}</textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-2" />
            @unless ($isOpen)
                <p class="mt-2 text-sm text-soft">El docente recibe mensajes de 6:00 a. m. a 7:00 p. m. Si escribes ahora, le llegará a las 6:00 a. m.</p>
            @endunless
            <button class="btn mt-3">Enviar</button>
        </form>
    </div>
</x-app-layout>
