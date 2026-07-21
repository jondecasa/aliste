<x-guest-layout>
    <div class="relative sm:flex sm:justify-center items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        @if (Route::has('login'))
            <livewire:welcome.navigation />
        @endif

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex justify-center">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </div>

            <div class="mt-16">
                <h1 class="text-2xl font-semibold text-center text-gray-900 dark:text-white">
                    {{ config('app.name') }}
                </h1>
            </div>
        </div>
    </div>
</x-guest-layout>
