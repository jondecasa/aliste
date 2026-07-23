<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Aliste.es') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo-aliste.png') }}">

        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#a24019">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-crema text-tinta">
        <div class="relative min-h-screen lg:flex">
            <div class="absolute inset-0 bg-foto-placeholder bg-cover bg-center lg:static lg:order-2 lg:flex-1">
                <img
                    src="{{ asset('images/background-aliste.gif') }}"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-tinta via-tinta/40 to-transparent lg:hidden"></div>
            </div>

            <div class="relative flex min-h-screen flex-col justify-end p-6 pb-10 lg:min-h-0 lg:w-[480px] lg:flex-shrink-0 lg:justify-center lg:px-14 lg:py-10 xl:w-[520px]">
                <a href="{{ route('inicio') }}" wire:navigate class="mb-6 block font-serif text-2xl italic text-white lg:mb-9 lg:text-tinta">
                    Aliste.es
                </a>

                <div class="rounded-3xl bg-white p-7 shadow-2xl lg:rounded-none lg:bg-transparent lg:p-0 lg:shadow-none">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
