<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OhMyWash POS — Sistem Laundry Sepatu</title>

    <link rel="icon" type="image/png" href="{{ asset('images/ohmywash-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink">
    <div class="mx-auto flex min-h-screen max-w-4xl flex-col justify-center px-5 py-12">
        <div class="text-center">
            <div class="mb-4 flex justify-center">
                <x-application-logo :dark="true" size="w-64" />
            </div>

            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                Sistem Kasir Laundry Sepatu
            </h1>

            <p class="mx-auto mt-4 max-w-xl text-brand">
                Bukan hanya bersih, tapi terlahir kembali dengan elegansi.
            </p>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['Kasir (POS)', 'Transaksi cepat dengan promo, pembayaran, dan struk instan.'],
                ['Alur Laundry', 'Pesanan berpindah status dari penerimaan hingga siap diambil.'],
                ['Quality Control', 'Checklist wajib sebelum pesanan boleh diserahkan ke pelanggan.'],
            ] as [$title, $description])
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <h2 class="font-bold text-white">{{ $title }}</h2>
                    <p class="mt-2 text-sm text-gray-400">{{ $description }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('login') }}" class="omw-btn-primary !min-h-[52px] !px-10 text-base">
                Masuk ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>