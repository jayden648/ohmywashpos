{{-- Totals and the checkout action. The figures are a server preview only:
     the order is recalculated from MySQL again when it is stored. --}}
<section class="omw-card p-4">
    <dl class="space-y-2 text-sm">
        <div class="flex justify-between">
            <dt class="text-gray-600">Subtotal</dt>
            <dd class="font-semibold text-ink">@rupiah($totals['subtotal'])</dd>
        </div>

        <div class="flex justify-between">
            <dt class="text-gray-600">
                Diskon
                @if ($totals['promotion'])
                    <span class="omw-badge bg-brand-soft text-brand-dark ring-brand/40">{{ $totals['promotion']->code }}</span>
                @endif
            </dt>
            <dd class="font-semibold text-emerald-700">−@rupiah($totals['discount'])</dd>
        </div>

        <div class="flex justify-between">
            <dt class="text-gray-600">Biaya Tambahan</dt>
            <dd class="font-semibold text-ink">@rupiah($totals['additional_fee'])</dd>
        </div>

        <div class="flex justify-between">
            <dt class="text-gray-600">PPN</dt>
            <dd class="font-semibold text-ink">@rupiah($totals['tax'])</dd>
        </div>

        <div class="flex items-center justify-between border-t-2 border-ink pt-3">
            <dt class="text-base font-bold text-ink">Total</dt>
            <dd class="text-xl font-extrabold text-ink">@rupiah($totals['total'])</dd>
        </div>
    </dl>

    <form method="POST" action="{{ route('pos.store') }}" class="mt-4 space-y-3">
        @csrf

        {{-- Only identifiers are posted; unit prices are read from MySQL. --}}
        @foreach ($totals['lines'] as $line)
            <input type="hidden" name="items[{{ $loop->index }}][service_id]" value="{{ $line['service']->id }}">
            <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $line['quantity'] }}">
        @endforeach

        @foreach (['brand', 'model', 'color', 'size', 'material', 'condition_before', 'customer_notes'] as $field)
            <input type="hidden" name="shoe[{{ $field }}]" value="{{ $shoe[$field] ?? '' }}">
        @endforeach

        <div>
            <label for="pos-notes" class="omw-label">Catatan Pesanan</label>
            <input id="pos-notes" name="notes" value="{{ old('notes') }}"
                   class="omw-input @error('notes') border-red-400 @enderror" placeholder="Opsional">
            @error('notes')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="omw-btn-lg" @disabled(count($cart) === 0 || ! $customerId)>
            Bayar Sekarang
        </button>

        @if (count($cart) === 0 || ! $customerId)
            <p class="text-center text-xs text-gray-500">
                Pilih pelanggan dan minimal satu layanan untuk melanjutkan.
            </p>
        @endif
    </form>

    @error('items')
        <p class="mt-2 text-center text-xs text-red-600">{{ $message }}</p>
    @enderror

    @error('items.*.service_id')
        <p class="mt-2 text-center text-xs text-red-600">{{ $message }}</p>
    @enderror

    @error('items.*.quantity')
        <p class="mt-2 text-center text-xs text-red-600">{{ $message }}</p>
    @enderror
</section>