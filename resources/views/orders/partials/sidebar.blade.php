{{-- Customer, payment and the status timeline. --}}
<div class="space-y-4">
    @if ($canSeePrices)
        <section class="omw-card p-4">
            <h2 class="mb-3 text-base font-bold text-ink">Pelanggan</h2>

            <p class="font-semibold text-ink">{{ $order->customer->name }}</p>
            <p class="mt-0.5 text-xs text-gray-500">{{ $order->customer->customer_code }}</p>
            <p class="mt-1 text-sm text-ink">{{ $order->customer->phone }}</p>

            @if ($order->notes)
                <p class="mt-3 rounded-xl bg-gray-50 p-2 text-xs text-gray-600">{{ $order->notes }}</p>
            @endif
        </section>

        <section class="omw-card p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-ink">Pembayaran</h2>
                <x-payment-badge :status="$order->paymentStatus()" />
            </div>

            @if ($canCancel || ! $order->isPaid())
                @role('admin', 'cashier')
                    <a href="{{ route('payments.create', $order) }}" class="omw-btn-primary mb-3 w-full">
                        @if ($order->isPaid()) Bayar Sisa @else Bayar Sekarang @endif
                    </a>
                @endrole
            @endif

            <ul class="space-y-2 text-sm">
                @forelse ($order->payments as $payment)
                    <li class="rounded-xl bg-gray-50 p-2.5">
                        <div class="flex justify-between">
                            <span class="font-medium text-ink">{{ $payment->method->label() }}</span>
                            <span class="font-bold text-ink">@rupiah($payment->amount)</span>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ $payment->paid_at?->format('d M Y, H:i') }}
                            @if ($payment->change_due > 0)
                                · Kembalian @rupiah($payment->change_due)
                            @endif
                            @if ($payment->receiver)
                                · {{ $payment->receiver->name }}
                            @endif
                        </p>
                    </li>
                @empty
                    <li class="text-sm text-gray-500">Belum ada pembayaran.</li>
                @endforelse
            </ul>
        </section>
    @endif

    {{-- Timeline: every status change, who made it and when. --}}
    <section class="omw-card p-4">
        <h2 class="mb-3 text-base font-bold text-ink">Timeline</h2>

        <ol class="space-y-4">
            @foreach ($order->statusHistories->sortBy('id') as $index => $history)
                <li class="relative pl-6">
                    <span class="omw-timeline-dot {{ $index === $order->statusHistories->count() - 1 ? 'omw-timeline-dot-current' : 'omw-timeline-dot-done' }}"></span>
                    @if (! $loop->last)
                        <span class="absolute left-[9px] top-6 h-[calc(100%+0.25rem)] w-0.5 bg-gray-200"></span>
                    @endif

                    <p class="text-sm font-semibold text-ink">{{ $history->status->label() }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $history->created_at->format('d M Y, H:i') }}
                        @if ($history->changedBy)
                            · {{ $history->changedBy->name }}
                        @endif
                    </p>
                    @if ($history->note)
                        <p class="mt-1 text-xs italic text-gray-600">{{ $history->note }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </section>

    @if ($canCancel)
        <section class="omw-card border-red-200 p-4">
            <h2 class="mb-2 text-base font-bold text-red-700">Batalkan Pesanan</h2>
            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                  onsubmit="return confirm('Batalkan pesanan {{ $order->order_number }}?')">
                @csrf
                <input type="hidden" name="note" value="Pesanan dibatalkan oleh kasir.">
                <button type="submit" class="omw-btn-danger w-full">Batalkan Pesanan</button>
            </form>
        </section>
    @endif
</div>