<x-app-layout title="Lacak Pesanan" subtitle="Cek status laundry Anda dengan nomor pesanan atau nomor WhatsApp.">
    <div class="mx-auto max-w-2xl">
        <form method="GET" class="omw-card mb-4 p-4">
            <label for="q" class="omw-label">Nomor Pesanan atau WhatsApp</label>

            <div class="flex gap-2">
                <input id="q" name="q" value="{{ $term }}" class="omw-input flex-1"
                       placeholder="OMW-20261005-0001 atau 081234567890" autofocus>
                <button type="submit" class="omw-btn-primary">Lacak</button>
            </div>
        </form>

        @if ($term && ! $order)
            <div class="omw-card p-8 text-center">
                <p class="text-sm text-gray-600">
                    Pesanan tidak ditemukan. Periksa kembali nomor pesanan atau nomor WhatsApp Anda.
                </p>
            </div>
        @endif

        @if ($order)
            @php $currentStep = $order->status->stepIndex() ?? 0; @endphp

            <section class="omw-card p-4">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-extrabold text-ink">{{ $order->customer->name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->order_number }}</p>
                    </div>
                    <x-status-badge :status="$order->status" />
                </div>

                <div class="mb-1 flex justify-between text-xs text-gray-500">
                    <span>Progres</span>
                    <span>{{ $order->status->progress() }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-brand" style="width: {{ max($order->status->progress(), 2) }}%"></div>
                </div>
            </section>

            {{-- The workflow, with completed steps marked. --}}
            <section class="omw-card mt-4 p-4">
                <h2 class="mb-3 text-base font-bold text-ink">Riwayat Status</h2>

                <ol class="space-y-4">
                    @foreach ($statuses as $index => $status)
                        <li class="relative pl-6">
                            <span class="omw-timeline-dot
                                @if ($index < $currentStep || $order->status === $status) omw-timeline-dot-done
                                @elseif ($index === $currentStep) omw-timeline-dot-current @endif"></span>

                            @if (! $loop->last)
                                <span class="absolute left-[9px] top-6 h-[calc(100%+0.25rem)] w-0.5 bg-gray-200"></span>
                            @endif

                            <p class="text-sm {{ $index <= $currentStep ? 'font-bold text-ink' : 'text-gray-500' }}">
                                {{ $status->label() }}
                            </p>

                            @php
                                $entry = $order->statusHistories->firstWhere('status', $status);
                            @endphp

                            @if ($entry)
                                <p class="text-xs text-gray-500">
                                    {{ $entry->created_at->format('d M Y, H:i') }}
                                </p>
                            @endif
                        </li>
                    @endforeach

                    @if ($order->status->isCancelled())
                        <li class="relative pl-6">
                            <span class="omw-timeline-dot border-red-400 bg-red-100"></span>
                            <p class="text-sm font-bold text-red-700">Dibatalkan</p>
                        </li>
                    @endif
                </ol>
            </section>

            <section class="omw-card mt-4 p-4">
                <h2 class="mb-2 text-base font-bold text-ink">Layanan</h2>
                <ul class="space-y-1 text-sm text-gray-700">
                    @foreach ($order->items as $item)
                        <li>{{ $item->service_name }} ×{{ $item->quantity }}</li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-app-layout>