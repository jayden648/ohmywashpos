<x-app-layout :title="$customer->name" :subtitle="'Pelanggan '.$customer->customer_code">
    <x-slot:actions>
        <a href="{{ route('pos.index') }}" class="omw-btn-primary">Buat Pesanan</a>
        <a href="{{ route('customers.edit', $customer) }}" class="omw-btn-secondary">Edit</a>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="omw-card overflow-hidden">
                <h2 class="border-b border-gray-200 px-4 py-3 text-base font-bold text-ink">Riwayat Pesanan</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="omw-th">No. Pesanan</th>
                                <th class="omw-th">Status</th>
                                <th class="omw-th text-right">Total</th>
                                <th class="omw-th"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($orders as $order)
                                <tr class="transition hover:bg-brand-soft/40">
                                    <td class="omw-td font-bold text-ink">{{ $order->order_number }}</td>
                                    <td class="omw-td"><x-status-badge :status="$order->status" /></td>
                                    <td class="omw-td text-right font-semibold">@rupiah($order->total)</td>
                                    <td class="omw-td text-right">
                                        <a href="{{ route('orders.show', $order) }}"
                                           class="omw-btn-secondary !min-h-[34px] !px-3">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                        Belum ada pesanan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="border-t border-gray-200 px-4 py-3">{{ $orders->links() }}</div>
                @endif
            </section>
        </div>

        {{-- Lifetime value, computed from settled orders only. --}}
        <div class="space-y-4">
            <section class="omw-card p-4">
                <h2 class="mb-3 text-base font-bold text-ink">Ringkasan</h2>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">WhatsApp</dt>
                        <dd class="font-medium text-ink">{{ $customer->phone }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Jumlah Pesanan</dt>
                        <dd class="font-bold text-ink">{{ $customer->payable_orders_count }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2">
                        <dt class="text-gray-600">Total Belanja</dt>
                        <dd class="font-extrabold text-brand-dark">@rupiah($customer->total_spending ?? 0)</dd>
                    </div>
                </dl>

                @if ($customer->address)
                    <p class="mt-3 text-xs text-gray-500">{{ $customer->address }}</p>
                @endif

                @if ($customer->notes)
                    <p class="mt-2 rounded-xl bg-brand-soft p-2 text-xs text-ink">{{ $customer->notes }}</p>
                @endif

                @unless ($customer->is_active)
                    <p class="omw-badge mt-3 bg-gray-200 text-gray-600 ring-gray-300">Nonaktif</p>
                @endunless
            </section>
        </div>
    </div>
</x-app-layout>