<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'OhMyWash POS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 flex justify-center">
                <x-application-logo :dark="true" :with-tagline="true" />
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-card sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>