<form method="POST" action="{{ route('services.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
    @csrf

    <div>
        <label for="new-name" class="omw-label">Nama Layanan</label>
        <input id="new-name" name="name" required class="omw-input @error('name') border-red-400 @enderror">
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="new-category" class="omw-label">Kategori</label>
        <select id="new-category" name="service_category_id" required class="omw-select">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        @error('service_category_id')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="new-price" class="omw-label">Harga (Rp)</label>
        <input id="new-price" name="price" type="number" min="0" step="500" required
               class="omw-input @error('price') border-red-400 @enderror">
        @error('price')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="new-duration" class="omw-label">Estimasi (hari)</label>
        <input id="new-duration" name="estimated_duration" type="number" min="1" max="365" value="2" required
               class="omw-input @error('estimated_duration') border-red-400 @enderror">
        @error('estimated_duration')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <button type="submit" class="omw-btn-primary">Simpan Layanan</button>
    </div>
</form>