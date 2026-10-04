{{-- Items at or below their minimum stock level. --}}
<section class="omw-card p-4">
    <h2 class="mb-3 text-base font-bold text-ink">Stok Menipis</h2>

    <ul class="space-y-2">
        @forelse ($lowStock as $item)
            <li class="flex items-center justify-between text-sm">
                <span class="truncate text-gray-700">{{ $item->name }}</span>
                <span class="omw-badge bg-amber-100 text-amber-800 ring-amber-200">
                    {{ $item->quantity }} {{ $item->unit }}
                </span>
            </li>
        @empty
            <li class="text-sm text-gray-500">Semua stok aman.</li>
        @endforelse
    </ul>
</section>