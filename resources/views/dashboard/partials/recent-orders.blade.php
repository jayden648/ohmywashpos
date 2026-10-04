{{-- The eight most recent orders across every status. --}}
<section class="omw-card mt-4 overflow-hidden">
    <h2 class="border-b border-gray-200 px-4 py-3 text-base font-bold text-ink">Pesanan Terbaru</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="omw-th">Nomor Pesanan</th>
                    <th class="omw-th">Pelanggan</th>
                    <th class="omw-th">Status</th>
                    <th class="omw-th text-right">Total</th>
                    <th class="omw-th"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($recentOrders as $order)
                    <tr class="transition hover:bg-brand-soft/40">
                        <td class="omw-td font-bold text-ink">{{ $order->order_number }}</td>
                        <td class="omw-td">{{ $order->customer->name }}</td>
                        <td class="omw-td"><x-status-badge :status="$order->status" /></td>
                        <td class="omw-td text-right font-semibold">@rupiah($order->total)</td>
                        <td class="omw-td text-right">
                            <a href="{{ route('orders.show', $order) }}" class="omw-btn-secondary !min-h-[34px] !px-3">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">
                            Belum ada pesanan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>