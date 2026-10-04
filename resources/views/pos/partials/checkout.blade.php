{{-- Customer, promo and fee. Each control posts on change so the server
     keeps its session in step with what the cashier sees. --}}
<section class="omw-card p-4">
    <form method="POST" action="{{ route('pos.context') }}" class="space-y-3">
        @csrf

        <div>
            <label for="pos-customer" class="omw-label">Pelanggan <span class="text-red-500">*</span></label>
            <select id="pos-customer" name="customer_id" class="omw-select" required
                    onchange="this.form.submit()">
                <option value="">— Pilih pelanggan —</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected($customerId === $customer->id)>
                        {{ $customer->name }} — {{ $customer->phone }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <span class="omw-label">Atau buat pelanggan baru</span>
            <a href="{{ route('customers.create') }}" class="omw-btn-secondary w-full">+ Pelanggan Baru</a>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="pos-promo" class="omw-label">Kode Promo</label>
                <input id="pos-promo" name="promotion_code" list="promo-codes"
                       value="{{ $promotionCode }}" placeholder="WELCOME10"
                       class="omw-input uppercase @error('promotion_code') border-red-400 @enderror">
                <datalist id="promo-codes">
                    @foreach ($promotions as $promotion)
                        <option value="{{ $promotion->code }}">
                            {{ $promotion->name }} — min. @rupiah($promotion->minimum_total)
                        </option>
                    @endforeach
                </datalist>
                @error('promotion_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pos-fee" class="omw-label">Biaya Tambahan</label>
                <input id="pos-fee" name="additional_fee" type="number" min="0" step="500"
                       value="{{ (int) $additionalFee ?: '' }}" placeholder="0"
                       class="omw-input @error('additional_fee') border-red-400 @enderror"
                       onchange="this.form.submit()">
                @error('additional_fee')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" class="omw-btn-secondary w-full">Perbarui Promo &amp; Biaya</button>
    </form>
</section>