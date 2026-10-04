{{-- The cart. Totals below are a server preview; the order is always
     recalculated again on submit, so a tampered form cannot change a price. --}}
<div class="lg:sticky lg:top-6 space-y-4">
    <section class="omw-card p-4">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-base font-bold text-ink">Keranjang</h2>

            @if (count($cart) > 0)
                <form method="POST" action="{{ route('pos.cart') }}">
                    @csrf
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                        Kosongkan
                    </button>
                </form>
            @endif
        </div>

        @forelse ($totals['lines'] as $line)
            <div class="flex items-center justify-between gap-3 border-b border-dashed border-gray-200 py-2.5 last:border-0">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-ink">{{ $line['service']->name }}</p>
                    <p class="text-xs text-gray-500">
                        @rupiah($line['unit_price']) × {{ $line['quantity'] }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-1">
                    <form method="POST" action="{{ route('pos.cart') }}">
                        @csrf
                        <input type="hidden" name="action" value="decrement">
                        <input type="hidden" name="service_id" value="{{ $line['service']->id }}">
                        <button class="h-8 w-8 rounded-lg border border-gray-300 bg-white font-bold hover:bg-gray-50"
                                aria-label="Kurangi">−</button>
                    </form>

                    <span class="w-6 text-center text-sm font-bold">{{ $line['quantity'] }}</span>

                    <form method="POST" action="{{ route('pos.cart') }}">
                        @csrf
                        <input type="hidden" name="action" value="increment">
                        <input type="hidden" name="service_id" value="{{ $line['service']->id }}">
                        <button class="h-8 w-8 rounded-lg border border-gray-300 bg-white font-bold hover:bg-gray-50"
                                aria-label="Tambah">+</button>
                    </form>

                    <form method="POST" action="{{ route('pos.cart') }}" class="ml-1">
                        @csrf
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="service_id" value="{{ $line['service']->id }}">
                        <button class="px-1 text-red-500 hover:text-red-700" aria-label="Hapus">×</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-gray-500">
                Keranjang masih kosong. Pilih layanan di sebelah kiri.
            </p>
        @endforelse
    </section>

    @include('pos.partials.checkout')
    @include('pos.partials.totals')
</div>