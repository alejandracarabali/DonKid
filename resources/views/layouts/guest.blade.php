<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DonKid') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fredoka:500,600,700|nunito:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-deep font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col items-center px-4 py-8 sm:justify-center">

            {{-- Marca: mascota + nombre, como en el prototipo --}}
            <a href="/" class="flex flex-col items-center text-center text-white">
                <x-mascot size="120" sparkles />
                <span class="mt-2 font-display text-[46px] font-bold leading-none">
                    Don<span class="text-amber">Kid</span>
                </span>
            </a>
            <p class="mb-6 mt-2 text-[#D5DBF5]">Tu colegio, más cerca de casa.</p>

            <div class="w-full max-w-md rounded-[20px] border-[3px] border-amber bg-white p-5">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>