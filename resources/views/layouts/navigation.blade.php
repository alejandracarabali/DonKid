{{-- Encabezado común: marca, nombre y salida. El menú de cada rol va aparte. --}}
<header class="bg-deep text-white">
    <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2 sm:px-6">
        <a href="{{ route('dashboard') }}" class="flex min-h-[44px] items-center gap-2">
            <x-mascot size="36" />
            <span class="font-display text-2xl font-semibold">Don<span class="text-amber">Kid</span></span>
        </a>

        <a href="{{ route('profile.edit') }}"
           class="ms-auto flex min-h-[44px] items-center text-sm text-[#D5DBF5] hover:text-white"
           title="Mi perfil">
            {{ auth()->user()->name }}
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="min-h-[44px] rounded-xl border-2 border-white px-3 text-sm font-bold hover:bg-navy">
                Salir
            </button>
        </form>
    </div>
</header>