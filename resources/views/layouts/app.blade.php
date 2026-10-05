<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'OhMyWash POS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50">
    {{-- Desktop is a flex row: sidebar beside the content.
         Mobile keeps the fixed drawer and stacks the content normally. --}}
    <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">

        {{-- Mobile top bar --}}
        <div class="sticky top-0 z-40 flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 lg:hidden">
            <button type="button" @click="sidebarOpen = true"
                    class="omw-btn-secondary !min-h-[40px] !px-3" aria-label="Buka menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <x-application-logo />
        </div>

        {{-- Backdrop for the mobile drawer --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>

        {{-- Sidebar: fixed drawer on mobile, sticky flex column on desktop.
                 lg:static previously left it as a static block, which pushed
                 the content below it instead of sitting beside it.
                 lg:inset-auto drops the drawer's mobile offsets on desktop. --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full transition-transform duration-200
                      lg:sticky lg:top-0 lg:inset-auto lg:z-auto lg:h-screen lg:w-64 lg:shrink-0
                      lg:self-start lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               aria-label="Sidebar">
            @include('layouts.navigation')
        </aside>

        {{-- Main content: takes the remaining width, never under the sidebar --}}
        <div class="lg:min-w-0 lg:flex-1">
            <header class="hidden border-b border-gray-200 bg-white px-6 py-5 lg:block">
                <div class="mx-auto flex max-w-[1400px] items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight text-ink">
                            {{ $title ?? config('app.name', 'OhMyWash POS') }}
                        </h1>
                        @isset($subtitle)
                            <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
                        @endisset
                    </div>

                    @isset($actions)
                        <div class="flex items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @isset($subtitle)
                    <div class="mb-4 lg:hidden">
                        <h1 class="text-xl font-extrabold tracking-tight text-ink">
                            {{ $title ?? config('app.name', 'OhMyWash POS') }}
                        </h1>
                        <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
                    </div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('status') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition.duration.300ms
             class="omw-no-print fixed bottom-4 right-4 z-50 w-[min(92vw,26rem)]">
            <div class="flex items-start gap-3 rounded-2xl border-l-4 p-4 shadow-lg
                        {{ session('error') ? 'border-red-500 bg-red-50 text-red-900' : 'border-brand bg-ink text-white' }}">
                <p class="flex-1 text-sm font-medium">{{ session('status') ?? session('error') }}</p>
                <button type="button" @click="show = false" class="opacity-70 hover:opacity-100"
                        aria-label="Tutup">&times;</button>
            </div>
        </div>
    @endif
</body>
</html>