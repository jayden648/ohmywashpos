<section class="omw-card p-4">
    <h2 class="mb-3 text-base font-bold text-ink">Layanan Terlaris</h2>

    <ul class="space-y-2">
        @forelse ($popularServices as $service)
            <li class="flex items-center justify-between border-b border-dashed border-gray-100 pb-2 text-sm last:border-0">
                <span class="truncate text-gray-700">{{ $service->service_name }}</span>
                <span class="font-bold text-ink">{{ (int) $service->total_quantity }}</span>
            </li>
        @empty
            <li class="text-sm text-gray-500">Belum ada penjualan.</li>
        @endforelse
    </ul>
</section>