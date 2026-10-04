<x-app-layout title="Dashboard" subtitle="Ringkasan operasional OhMyWash.">
    {{-- Headline figures, all aggregated from MySQL. --}}
    <div class="mb-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ([
            ['Pendapatan Hari Ini', $revenueToday, true],
            ['Pesanan Hari Ini', $todayOrders, false],
            ['Sedang Diproses', $inProgress, false],
            ['Siap Diambil', $readyForPickup, false],
        ] as [$label, $value, $isMoney])
            <div class="omw-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-extrabold {{ $isMoney ? 'text-brand-dark' : 'text-ink' }}">
                    @if ($isMoney) @rupiah($value) @else {{ $value }} @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
        @foreach ([
            ['Pesanan Selesai', $completed],
            ['Belum Dibayar', $unpaid],
            ['Barang Menipis', $lowStock->count()],
        ] as [$label, $value])
            <div class="omw-card flex items-center justify-between p-4">
                <span class="text-sm text-gray-600">{{ $label }}</span>
                <span class="text-lg font-extrabold text-ink">{{ $value }}</span>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-5">
        {{-- Status distribution across the whole workflow. --}}
        <section class="omw-card p-4 lg:col-span-3">
            <h2 class="mb-3 text-base font-bold text-ink">Status Pesanan</h2>

            <ul class="space-y-2">
                @foreach ($statusDistribution as $row)
                    <li class="flex items-center gap-3">
                        <span class="w-28 shrink-0 truncate text-xs text-gray-600">{{ $row['status']->shortLabel() }}</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-brand" style="width: {{ max($row['percentage'], 2) }}%"></div>
                        </div>
                        <span class="w-8 text-right text-xs font-bold text-ink">{{ $row['count'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="space-y-4 lg:col-span-2">
            @include('dashboard.partials.popular-services')
            @include('dashboard.partials.low-stock')
        </div>
    </div>

    @include('dashboard.partials.recent-orders')
</x-app-layout>