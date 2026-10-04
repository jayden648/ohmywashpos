<x-app-layout title="Inventori" subtitle="Pantau stok bahan dan perlengkapan laundry.">
    <x-slot:actions>
        <span class="omw-badge bg-amber-100 px-3 py-2 text-amber-800 ring-amber-200">
            {{ $lowStockCount }} barang menipis
        </span>
    </x-slot:actions>

    <details class="omw-card mb-4 p-4">
        <summary class="cursor-pointer text-sm font-bold text-ink">+ Tambah Barang</summary>

        <form method="POST" action="{{ route('inventory.store') }}" class="mt-4 grid gap-3 sm:grid-cols-4">
            @csrf

            <div>
                <label for="inv-name" class="omw-label">Nama Barang</label>
                <input id="inv-name" name="name" required class="omw-input" placeholder="Cleaning Chemical">
            </div>

            <div>
                <label for="inv-unit" class="omw-label">Satuan</label>
                <input id="inv-unit" name="unit" required class="omw-input" placeholder="pcs">
            </div>

            <div>
                <label for="inv-qty" class="omw-label">Jumlah</label>
                <input id="inv-qty" name="quantity" type="number" min="0" required class="omw-input">
            </div>

            <div>
                <label for="inv-min" class="omw-label">Stok Minimum</label>
                <input id="inv-min" name="minimum_stock" type="number" min="0" required class="omw-input">
            </div>

            <div class="sm:col-span-4">
                <button type="submit" class="omw-btn-primary">Simpan Barang</button>
            </div>
        </form>
    </details>

    <form method="GET" class="omw-card mb-4 flex gap-2 p-4">
        <label for="search" class="sr-only">Cari barang</label>
        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="omw-input flex-1"
               placeholder="Cari nama barang…">
        <button type="submit" class="omw-btn-dark">Cari</button>
    </form>

    @include('inventory.partials.table')
</x-app-layout>