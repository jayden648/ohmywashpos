<x-app-layout title="Laporan" subtitle="Ringkasan penjualan OhMyWash.">
    {{-- Period selector; all aggregates are recomputed for the window. --}}
    <form method="GET" class="omw-card mb-4 flex flex-wrap gap-2 p-4">
        @foreach ([7 => '7 Hari', 30 => '30 Hari', 90 => '90 Hari'] as $value => $label)
            <a href="{{ route('reports.index', ['days' => $value]) }}"
               class="omw-btn {{ $days === $value ? 'border-brand bg-brand text-ink' : 'border-gray-300 bg-white text-gray-600' }}">
                {{ $label }}
            </a>
        @endforeach
    </form>

    <p class="mb-3 text-xs text-gray-500">
        Periode {{ $since->format('d M Y') }} — {{ now()->format('d M Y') }}
    </p>

    <div class="mb-4 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ([
            ['Total Pendapatan', $revenue, true],
            ['Jumlah Pesanan', $orders, false],
            ['Pesanan Dibatalkan', $cancelled, false],
            ['Rata-rata / Pesanan', $averageOrder, true],
        ] as [$label, $value, $isMoney])
            <div class="omw-card p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-extrabold text-ink">
                    @if ($isMoney) @rupiah($value) @else {{ $value }} @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Revenue split by payment method. --}}
        <section class="omw-card p-4">
            <h2 class="mb-3 text-base font-bold text-ink">Pendapatan per Metode</h2>

            <ul class="space-y-2">
                @forelse ($byMethod as $row)
                    <li class="flex items-center justify-between border-b border-dashed border-gray-100 pb-2 text-sm last:border-0">
                        <span class="text-gray-700">{{ $row['label'] }}
                            <span class="text-xs text-gray-400">({{ $row['count'] }}x)</span>
                        </span>
                        <span class="font-bold text-ink">@rupiah($row['total'])</span>
                    </li>
                @empty
                    <li class="text-sm text-gray-500">Belum ada pembayaran pada periode ini.</li>
                @endforelse
            </ul>
        </section>

        {{-- Best sellers in the window. --}}
        <section class="omw-card p-4">
            <h2 class="mb-3 text-base font-bold text-ink">Layanan Terlaris</h2>

            <ul class="space-y-2">
                @forelse ($topServices as $service)
                    <li class="flex items-center justify-between border-b border-dashed border-gray-100 pb-2 text-sm last:border-0">
                        <span class="truncate text-gray-700">{{ $service->service_name }}</span>
                        <span class="text-right">
                            <span class="font-bold text-ink">{{ (int) $service->total_quantity }}</span>
                            <span class="block text-xs text-gray-400">@rupiah($service->revenue)</span>
                        </span>
                    </li>
                @empty
                    <li class="text-sm text-gray-500">Belum ada penjualan pada periode ini.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-app-layout>