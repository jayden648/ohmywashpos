<x-app-layout title="Layanan" subtitle="Kelola kategori dan harga layanan laundry.">
    <x-slot:actions>
        <a href="{{ route('service-categories.index') }}" class="omw-btn-secondary">Kategori</a>
    </x-slot:actions>

    {{-- Filters run server-side. --}}
    <form method="GET" class="omw-card mb-4 flex flex-col gap-3 p-4 sm:flex-row">
        <div class="flex-1">
            <label for="search" class="sr-only">Cari layanan</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="omw-input"
                   placeholder="Cari nama layanan…">
        </div>

        <div class="sm:w-56">
            <label for="category" class="sr-only">Kategori</label>
            <select id="category" name="category" class="omw-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="omw-btn-dark">Filter</button>
    </form>

    @role('admin')
        <details class="omw-card mb-4 p-4">
            <summary class="cursor-pointer text-sm font-bold text-ink">+ Tambah Layanan Baru</summary>
            @include('services.partials.create-form')
        </details>
    @endrole

    @include('services.partials.table')
</x-app-layout>