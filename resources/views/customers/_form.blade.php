{{-- Shared customer form used by both create and edit. --}}
@php $editing = isset($customer); @endphp

<div class="grid gap-3 sm:grid-cols-2">
    <div>
        <label for="name" class="omw-label">Nama <span class="text-red-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $customer->name ?? '') }}" required
               class="omw-input @error('name') border-red-400 @enderror" placeholder="Nama lengkap">
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="omw-label">WhatsApp <span class="text-red-500">*</span></label>
        <input id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" required
               inputmode="tel" class="omw-input @error('phone') border-red-400 @enderror"
               placeholder="081234567890">
        @error('phone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="email" class="omw-label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $customer->email ?? '') }}"
               class="omw-input @error('email') border-red-400 @enderror">
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="omw-label">Alamat</label>
        <textarea id="address" name="address" rows="2" class="omw-input">{{ old('address', $customer->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="omw-label">Catatan</label>
        <textarea id="notes" name="notes" rows="2" class="omw-input">{{ old('notes', $customer->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-4 flex justify-end gap-2">
    <a href="{{ $editing ? route('customers.show', $customer) : route('customers.index') }}"
       class="omw-btn-secondary">Batal</a>
    <button type="submit" class="omw-btn-primary">{{ $editing ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}</button>
</div>