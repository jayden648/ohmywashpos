<x-app-layout title="Pesanan" subtitle="Kelola pesanan dan majukan status laundry.">
    {{-- Server-side search and filtering; the order book is never loaded into JS. --}}
    <form method="GET" class="omw-card mb-4 flex flex-col gap-3 p-4 sm:flex-row">
        <div class="flex-1">
            <label for="search" class="sr-only">Cari pesanan</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                   class="omw-input" placeholder="Cari nomor pesanan atau nama pelanggan…">
        </div>

        <div class="sm:w-56">
            <label for="status" class="sr-only">Filter status</label>
            <select id="status" name="status" class="omw-select">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="omw-btn-dark">Filter</button>
    </form>

    <div class="omw-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="omw-th">Nomor Pesanan</th>
                        <th class="omw-th">Pelanggan</th>
                        <th class="omw-th">Layanan</th>
                        @role('admin', 'cashier')
                            <th class="omw-th">Total</th>
                        @endrole
                        <th class="omw-th">Status</th>
                        <th class="omw-th">Tanggal</th>
                        <th class="omw-th"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-brand-soft/40">
                            <td class="omw-td font-bold text-ink">{{ $order->order_number }}</td>

                            <td class="omw-td">
                                <span class="font-medium text-ink">{{ $order->customer->name }}</span>
                                <span class="block text-xs text-gray-500">{{ $order->customer->phone }}</span>
                            </td>

                            <td class="omw-td">
                                <span class="text-xs text-gray-600">
                                    {{ $order->items->pluck('service_name')->join(', ') ?: '—' }}
                                </span>
                            </td>

                            @role('admin', 'cashier')
                                <td class="omw-td font-semibold text-ink">@rupiah($order->total)</td>
                            @endrole

                            <td class="omw-td"><x-status-badge :status="$order->status" /></td>

                            <td class="omw-td text-xs text-gray-500">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>

                            <td class="omw-td text-right">
                                <a href="{{ route('orders.show', $order) }}" class="omw-btn-secondary !min-h-[36px] !px-3">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">
                                Belum ada pesanan yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>