{{-- Line items and totals. Staff never see prices. --}}
<section class="omw-card overflow-hidden">
    <h2 class="border-b border-gray-200 px-4 py-3 text-base font-bold text-ink">Layanan</h2>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="omw-th">Layanan</th>
                <th class="omw-th text-center">Qty</th>
                @if ($canSeePrices)
                    <th class="omw-th text-right">Harga</th>
                    <th class="omw-th text-right">Subtotal</th>
                @endif
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
            @foreach ($order->items as $item)
                <tr>
                    <td class="omw-td font-medium text-ink">{{ $item->service_name }}</td>
                    <td class="omw-td text-center">{{ $item->quantity }}</td>
                    @if ($canSeePrices)
                        <td class="omw-td text-right">@rupiah($item->unit_price)</td>
                        <td class="omw-td text-right font-semibold">@rupiah($item->subtotal)</td>
                    @endif
                </tr>
            @endforeach
        </tbody>

        @if ($canSeePrices)
            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                <tr>
                    <td colspan="3" class="omw-td text-right text-gray-600">Subtotal</td>
                    <td class="omw-td text-right font-semibold">@rupiah($order->subtotal)</td>
                </tr>
                <tr>
                    <td colspan="3" class="omw-td text-right text-gray-600">Diskon</td>
                    <td class="omw-td text-right font-semibold text-emerald-700">−@rupiah($order->discount)</td>
                </tr>
                <tr>
                    <td colspan="3" class="omw-td text-right text-gray-600">Biaya Tambahan</td>
                    <td class="omw-td text-right font-semibold">@rupiah($order->additional_fee)</td>
                </tr>
                <tr>
                    <td colspan="3" class="omw-td text-right text-base font-bold">Total</td>
                    <td class="omw-td text-right text-base font-extrabold">@rupiah($order->total)</td>
                </tr>
            </tfoot>
        @endif
    </table>
</section>