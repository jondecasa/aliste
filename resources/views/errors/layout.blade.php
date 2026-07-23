<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('titulo', 'Error') · {{ config('app.name', 'Aliste.es') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo-aliste.png') }}">
        <meta name="theme-color" content="#a24019">

        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans antialiased bg-crema text-tinta">
        <div class="min-h-screen flex flex-col">
            <header class="border-b border-tinta-borde">
                <div class="max-w-7xl mx-auto px-5 sm:px-8 h-16 sm:h-20 flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2 bg-white rounded-xl px-2 py-1">
                        <img src="{{ asset('images/logo-aliste.png') }}" alt="Aliste.es" class="h-10 sm:h-12 w-auto">
                    </a>
                </div>
            </header>

            <main class="flex-1 flex items-center justify-center px-5 py-16 sm:py-24">
                <div class="max-w-lg text-center">
                    <div class="font-serif text-7xl sm:text-8xl text-terracota mb-4">@yield('codigo')</div>
                    <h1 class="font-serif text-2xl sm:text-3xl text-tinta mb-3">@yield('titulo')</h1>
                    <p class="text-[15px] text-tinta-muted leading-relaxed mb-8">@yield('mensaje')</p>

                    <a href="{{ url('/') }}"
                        class="inline-flex items-center justify-center bg-terracota text-white px-7 py-3 rounded-full font-bold text-sm hover:bg-terracota-dark transition">
                        Volver al inicio
                    </a>
                </div>
            </main>

            <footer class="bg-tinta text-tinta-borde">
                <div class="max-w-7xl mx-auto px-5 sm:px-12 py-6 flex justify-center">
                    <div class="bg-white rounded-lg px-3 py-1.5 inline-flex w-fit">
                        <img src="{{ asset('images/logo-aliste.png') }}" alt="Aliste.es" class="h-7 sm:h-8 w-auto">
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
