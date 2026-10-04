{{-- Shoe details, stored with the order so laundry staff know what they are handling. --}}
<section class="omw-card p-4">
    <h2 class="mb-3 text-base font-bold text-ink">Data Sepatu</h2>

    <form method="POST" action="{{ route('pos.context') }}">
        @csrf

        <input type="hidden" name="customer_id" value="{{ $customerId }}">
        <input type="hidden" name="promotion_code" value="{{ $promotionCode }}">
        <input type="hidden" name="additional_fee" value="{{ $additionalFee }}">

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                'brand' => 'Brand',
                'model' => 'Model',
                'color' => 'Warna',
                'size' => 'Ukuran',
                'material' => 'Material',
                'condition_before' => 'Kondisi sebelum dicuci',
            ] as $field => $label)
                <div>
                    <label for="shoe-{{ $field }}" class="omw-label">{{ $label }}</label>
                    <input id="shoe-{{ $field }}" name="shoe[{{ $field }}]"
                           value="{{ old('shoe.'.$field, $shoe[$field] ?? '') }}"
                           class="omw-input @error('shoe.'.$field) border-red-400 @enderror">
                    @error('shoe.'.$field)
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="sm:col-span-2 lg:col-span-3">
                <label for="shoe-customer_notes" class="omw-label">Catatan pelanggan</label>
                <textarea id="shoe-customer_notes" name="shoe[customer_notes]" rows="2"
                          class="omw-input @error('shoe.customer_notes') border-red-400 @enderror">{{ old('shoe.customer_notes', $shoe['customer_notes'] ?? '') }}</textarea>
                @error('shoe.customer_notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="omw-btn-secondary">Simpan data sepatu</button>
        </div>
    </form>
</section>