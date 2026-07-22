<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->check() && auth()->user()->prefiereTemaOscuro() ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Aliste.es') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo-aliste.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-crema text-tinta">
        <div x-data="{ menuAbierto: false }" class="min-h-screen flex flex-col">
            <header class="border-b border-tinta-borde">
                <div class="max-w-7xl mx-auto px-5 sm:px-8 h-16 sm:h-20 flex items-center justify-between">
                    <a href="{{ route('inicio') }}" wire:navigate class="flex items-center gap-2 bg-white rounded-xl px-2 py-1">
                        <x-application-logo class="h-12 sm:h-14 w-auto" />
                    </a>

                    <nav class="hidden sm:flex items-center gap-8">
                        <a href="{{ route('pueblos') }}" wire:navigate
                            class="text-[15px] {{ request()->routeIs('pueblos') ? 'font-bold text-terracota border-b-2 border-terracota pb-1' : 'text-tinta/80 hover:text-tinta' }}">
                            Pueblos
                        </a>
                        <a href="{{ route('servicios') }}" wire:navigate
                            class="text-[15px] {{ request()->routeIs('servicios') ? 'font-bold text-terracota border-b-2 border-terracota pb-1' : 'text-tinta/80 hover:text-tinta' }}">
                            Servicios
                        </a>
                        <a href="{{ route('noticias') }}" wire:navigate
                            class="text-[15px] {{ request()->routeIs('noticias') ? 'font-bold text-terracota border-b-2 border-terracota pb-1' : 'text-tinta/80 hover:text-tinta' }}">
                            Noticias
                        </a>
                        <a href="{{ route('contacto') }}" wire:navigate
                            class="text-[15px] {{ request()->routeIs('contacto') ? 'font-bold text-terracota border-b-2 border-terracota pb-1' : 'text-tinta/80 hover:text-tinta' }}">
                            Contacto
                        </a>

                        @auth
                            <a href="{{ route('profile') }}" wire:navigate
                                class="border border-terracota text-terracota px-4 py-2 rounded-full text-sm font-semibold hover:bg-terracota hover:text-white transition">
                                Mi cuenta
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate
                                class="border border-terracota text-terracota px-4 py-2 rounded-full text-sm font-semibold hover:bg-terracota hover:text-white transition">
                                Entrar
                            </a>
                        @endauth
                    </nav>

                    <button @click="menuAbierto = !menuAbierto" class="sm:hidden flex flex-col gap-1.5" aria-label="Abrir menú">
                        <span class="w-6 h-0.5 bg-tinta"></span>
                        <span class="w-6 h-0.5 bg-tinta"></span>
                        <span class="w-6 h-0.5 bg-tinta"></span>
                    </button>
                </div>

                <nav x-show="menuAbierto" x-cloak class="sm:hidden flex flex-col px-5 pb-4 gap-3 border-t border-tinta-borde pt-3">
                    <a href="{{ route('pueblos') }}" wire:navigate class="text-[15px] {{ request()->routeIs('pueblos') ? 'font-bold text-terracota' : 'text-tinta/80' }}">Pueblos</a>
                    <a href="{{ route('servicios') }}" wire:navigate class="text-[15px] {{ request()->routeIs('servicios') ? 'font-bold text-terracota' : 'text-tinta/80' }}">Servicios</a>
                    <a href="{{ route('noticias') }}" wire:navigate class="text-[15px] {{ request()->routeIs('noticias') ? 'font-bold text-terracota' : 'text-tinta/80' }}">Noticias</a>
                    <a href="{{ route('contacto') }}" wire:navigate class="text-[15px] {{ request()->routeIs('contacto') ? 'font-bold text-terracota' : 'text-tinta/80' }}">Contacto</a>
                    @auth
                        <a href="{{ route('profile') }}" wire:navigate class="text-[15px] text-terracota font-semibold">Mi cuenta</a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="text-[15px] text-terracota font-semibold">Entrar</a>
                    @endauth
                </nav>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-tinta text-tinta-borde">
                <div class="max-w-7xl mx-auto px-5 sm:px-12 py-8 sm:py-9 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 sm:gap-0 text-sm">
                    <div class="bg-white rounded-lg px-3 py-1.5 inline-flex w-fit">
                        <x-application-logo class="h-8 sm:h-9 w-auto" />
                    </div>
                    <div class="flex flex-wrap gap-4 sm:gap-12">
                        <a href="{{ route('pueblos') }}" wire:navigate>Pueblos</a>
                        <a href="{{ route('servicios') }}" wire:navigate>Servicios</a>
                        <a href="{{ route('noticias') }}" wire:navigate>Noticias</a>
                        <a href="{{ route('contacto') }}" wire:navigate>Contacto</a>
                        <a href="{{ route('cookies') }}" wire:navigate>Política de cookies</a>
                    </div>
                </div>
            </footer>
        </div>

        <x-cookie-consent />
    </body>
</html>
