<x-app-layout title="Kategori Layanan" subtitle="Kelompokkan layanan yang ditawarkan OhMyWash.">
    <x-slot:actions>
        <a href="{{ route('services.index') }}" class="omw-btn-secondary">Kembali ke Layanan</a>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <form method="POST" action="{{ route('service-categories.store') }}" class="omw-card h-fit p-4">
            @csrf
            <h2 class="mb-3 text-base font-bold text-ink">Tambah Kategori</h2>

            <label for="cat-name" class="omw-label">Nama</label>
            <input id="cat-name" name="name" required class="omw-input @error('name') border-red-400 @enderror"
                   placeholder="Contoh: Cleaning">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <label for="cat-desc" class="omw-label mt-3">Deskripsi</label>
            <textarea id="cat-desc" name="description" rows="2" class="omw-input"></textarea>

            <button type="submit" class="omw-btn-primary mt-4 w-full">Simpan Kategori</button>
        </form>

        <div class="space-y-3 lg:col-span-2">
            @forelse ($categories as $category)
                <section class="omw-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-ink">{{ $category->name }}</h3>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $category->services_count }} layanan</p>

                            @if ($category->description)
                                <p class="mt-2 text-sm text-gray-600">{{ $category->description }}</p>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <span class="omw-badge {{ $category->is_active ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>

                            @if ($category->services_count === 0)
                                <form method="POST" action="{{ route('service-categories.destroy', $category) }}"
                                      onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>
            @empty
                <p class="omw-card p-8 text-center text-sm text-gray-500">Belum ada kategori.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>