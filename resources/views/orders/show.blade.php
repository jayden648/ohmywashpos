@php
    $waLink = 'https://wa.me/'.$order->customer->whatsapp_link.'?text='.rawurlencode(
        "Halo {$order->customer->name},\nPesanan {$order->order_number} saat ini {$order->status->label()}.\nTotal: Rp".number_format((float) $order->total, 0, ',', '.')."\n— OhMyWash",
    );
@endphp

<x-app-layout :title="$order->order_number" :subtitle="'Pelanggan: '.$order->customer->name">
    <x-slot:actions>
        <a href="{{ route('orders.index') }}" class="omw-btn-secondary">Kembali</a>
        <a href="{{ route('receipts.show', $order) }}" class="omw-btn-secondary" target="_blank" rel="noopener">
            Struk
        </a>
        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="omw-btn-primary">Kirim WhatsApp</a>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            {{-- Progress and the single permitted forward transition. --}}
            <section class="omw-card p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-bold text-ink">Status Pesanan</h2>
                    <x-status-badge :status="$order->status" />
                </div>

                <div class="mb-1 flex items-center justify-between text-xs text-gray-500">
                    <span>Dibuat</span>
                    <span>{{ $order->status->progress() }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-brand transition-all"
                         style="width: {{ max($order->status->progress(), 2) }}%"></div>
                </div>

                @if ($nextStatus)
                    <form method="POST" action="{{ route('orders.advance', $order) }}" class="mt-4 space-y-2">
                        @csrf
                        <label for="advance-note" class="omw-label">Catatan (opsional)</label>
                        <input id="advance-note" name="note" class="omw-input" placeholder="Catatan untuk tim laundry">
                        <button type="submit" class="omw-btn-primary w-full">
                            Lanjut ke: {{ $nextStatus->label() }}
                        </button>
                    </form>
                @elseif ($order->status->isCancelled())
                    <p class="mt-4 rounded-xl bg-red-50 p-3 text-center text-sm font-semibold text-red-700">
                        Pesanan ini telah dibatalkan.
                    </p>
                @else
                    <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-center text-sm font-semibold text-emerald-700">
                        Pesanan selesai dan telah diambil pelanggan.
                    </p>
                @endif
            </section>

            @if ($requiresQc)
                @include('orders.partials.quality-control')
            @endif

            @include('orders.partials.items')
            @include('orders.partials.shoe')
        </div>

        @include('orders.partials.sidebar')
    </div>
</x-app-layout>