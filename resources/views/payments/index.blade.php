<x-app-layout title="Pembayaran" subtitle="Catatan seluruh transaksi kasir.">
    <form method="GET" class="omw-card mb-4 flex flex-col gap-3 p-4 sm:flex-row">
        <div class="flex-1">
            <label for="search" class="sr-only">Cari pembayaran</label>
            <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" class="omw-input"
                   placeholder="Cari nomor pesanan…">
        </div>

        <div class="sm:w-48">
            <label for="method" class="sr-only">Metode</label>
            <select id="method" name="method" class="omw-select">
                <option value="">Semua metode</option>
                @foreach ($methods as $method)
                    <option value="{{ $method->value }}" @selected(($filters['method'] ?? '') === $method->value)>
                        {{ $method->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:w-44">
            <label for="status" class="sr-only">Status</label>
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
                        <th class="omw-th">No. Pesanan</th>
                        <th class="omw-th">Metode</th>
                        <th class="omw-th text-right">Jumlah</th>
                        <th class="omw-th text-center">Status</th>
                        <th class="omw-th">Waktu</th>
                        <th class="omw-th">Kasir</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($payments as $payment)
                        <tr class="transition hover:bg-brand-soft/40">
                            <td class="omw-td">
                                <a href="{{ route('orders.show', $payment->order) }}"
                                   class="font-bold text-ink hover:underline">
                                    {{ $payment->order->order_number }}
                                </a>
                            </td>
                            <td class="omw-td">{{ $payment->method->label() }}</td>
                            <td class="omw-td text-right font-semibold">@rupiah($payment->amount)</td>
                            <td class="omw-td text-center"><x-payment-badge :status="$payment->status" /></td>
                            <td class="omw-td text-xs text-gray-500">{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                            <td class="omw-td text-xs text-gray-500">{{ $payment->receiver?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                                Belum ada pembayaran yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $payments->links() }}</div>
        @endif
    </div>
</x-app-layout>