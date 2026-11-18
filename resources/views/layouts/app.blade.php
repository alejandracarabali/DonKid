<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DonKid') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fredoka:500,600,700|nunito:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased">
        @php
            // El menú de cada rol sale de App\Support\Navigation (un solo lugar).
            $menu = \App\Support\Navigation::for(auth()->user());
            $isAdmin = auth()->user()->role === \App\Enums\Role::Admin;
        @endphp

        @include('layouts.navigation')

        <div class="mx-auto max-w-7xl lg:flex lg:gap-6 lg:px-6 lg:py-6">
            @if ($isAdmin)
                <x-side-nav :items="$menu" />
            @endif

            {{-- pb-28: deja espacio para que la barra inferior no tape el contenido. --}}
            <div @class(['min-w-0 flex-1', 'pb-28' => ! $isAdmin])>
                @isset($header)
                    <div class="px-4 pt-6 sm:px-6 lg:px-0 lg:pt-0">
                        {{ $header }}
                    </div>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        @unless ($isAdmin)
            <x-bottom-nav :items="$menu" />
        @endunless
    </body>
</html>