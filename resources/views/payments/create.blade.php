{{-- The till payment screen. Change is calculated live for the preview but
     recomputed server-side when the payment is stored. --}}
<x-app-layout title="Pembayaran" :subtitle="'Pesanan '.$order->order_number">
    <x-slot:actions>
        <a href="{{ route('orders.show', $order) }}" class="omw-btn-secondary">Kembali ke Pesanan</a>
    </x-slot:actions>

    <div class="mx-auto max-w-2xl" x-data="{
            method: 'cash',
            total: {{ (float) $order->total }},
            due: {{ (float) $due }},
            get received() { return this.method === 'cash' ? Number(this.tendered || 0) : this.due; },
            get change() { return Math.max(0, this.received - this.total); },
         }">
        {{-- Summary --}}
        <section class="omw-card mb-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Total Tagihan</p>
                    <p class="text-3xl font-extrabold text-ink">@rupiah($order->total)</p>
                </div>
                @if ($due < (float) $order->total)
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Sisa Tagihan</p>
                        <p class="text-xl font-bold text-brand-dark">@rupiah($due)</p>
                    </div>
                @endif
            </div>

            <p class="mt-2 text-sm text-gray-500">
                Pelanggan: <span class="font-medium text-ink">{{ $order->customer->name }}</span>
            </p>
        </section>

        <form method="POST" action="{{ route('payments.store', $order) }}" class="omw-card space-y-4 p-4">
            @csrf

            <div>
                <label for="method" class="omw-label">Metode Pembayaran</label>
                <select id="method" name="method" x-model="method" class="omw-select" required>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->value }}">{{ $method->label() }}</option>
                    @endforeach
                </select>
                @error('method')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cash only: the tendered amount and resulting change. --}}
            <div x-show="method === 'cash'" x-cloak>
                <label for="amount_received" class="omw-label">Uang Diterima</label>
                <input id="amount_received" name="amount_received" type="number" min="0" step="100"
                       x-model="tendered" class="omw-input" placeholder="0">
                @error('amount_received')
                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-3 flex items-center justify-between rounded-xl bg-brand-soft px-4 py-3">
                    <span class="text-sm font-semibold text-ink">Kembalian</span>
                    <span class="text-lg font-extrabold text-ink" x-text="'Rp' + change.toLocaleString('id-ID')">Rp0</span>
                </div>
            </div>

            {{-- Reference for non-cash methods. --}}
            <div x-show="method !== 'cash'" x-cloak>
                <label for="reference" class="omw-label">Referensi Transaksi <span class="font-normal normal-case text-gray-400">(opsional)</span></label>
                <input id="reference" name="reference" class="omw-input" placeholder="Nomor QRIS / VA / nota kartu">
                @error('reference')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="omw-btn-lg">Konfirmasi Pembayaran</button>
        </form>
    </div>
</x-app-layout>