<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->order_number }} — OhMyWash</title>

    <link rel="icon" type="image/png" href="{{ asset('images/ohmywash-logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 py-6">
    {{-- Screen-only toolbar; hidden when the receipt is printed. --}}
    <div class="omw-no-print mx-auto mb-4 flex max-w-sm flex-wrap gap-2 px-4">
        <button type="button" onclick="window.print()" class="omw-btn-primary flex-1">Cetak</button>

        <a href="{{ $receiptLink }}" target="_blank" rel="noopener" class="omw-btn-dark flex-1">
            Kirim WhatsApp
        </a>

        <button type="button" onclick="window.close()" class="omw-btn-secondary flex-1">Tutup</button>
    </div>

    <div class="omw-print-area mx-auto max-w-sm rounded-2xl bg-white p-6 shadow-card">
        <header class="mb-4 border-b-2 border-ink pb-3 text-center">
            {{-- Official OhMyWash logo, served as a static asset (not via Vite).
                 w-48 with h-auto keeps the 1999x787 artwork proportional;
                 object-contain guards against any distortion. --}}
            <img
                src="{{ asset('images/ohmywash-logo.png') }}"
                alt="OhMyWash"
                width="1999"
                height="787"
                class="mx-auto block h-auto w-48 object-contain"
            >
            <p class="mt-2 text-[11px] text-gray-500">Bukan hanya bersih, tapi terlahir kembali dengan elegansi</p>
        </header>

        <dl class="space-y-1.5 text-xs">
            <div class="flex justify-between">
                <dt class="text-gray-500">No. Pesanan</dt>
                <dd class="font-bold text-ink">{{ $order->order_number }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Tanggal</dt>
                <dd class="text-ink">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Pelanggan</dt>
                <dd class="text-ink">{{ $order->customer->name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">WhatsApp</dt>
                <dd class="text-ink">{{ $order->customer->phone }}</dd>
            </div>
        </dl>

        <table class="mt-4 w-full border-t border-dashed border-gray-300 pt-2 text-xs">
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-b border-dashed border-gray-200">
                        <td class="py-1.5 pr-2 text-ink">
                            {{ $item->service_name }}
                            <span class="text-gray-500">×{{ $item->quantity }}</span>
                        </td>
                        <td class="py-1.5 text-right font-semibold text-ink">@rupiah($item->subtotal)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="mt-3 space-y-1.5 border-t border-ink pt-2 text-xs">
            <div class="flex justify-between">
                <dt class="text-gray-500">Subtotal</dt>
                <dd class="text-ink">@rupiah($order->subtotal)</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Diskon</dt>
                <dd class="text-ink">−@rupiah($order->discount)</dd>
            </div>
            @if ((float) $order->additional_fee > 0)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Biaya Tambahan</dt>
                    <dd class="text-ink">@rupiah($order->additional_fee)</dd>
                </div>
            @endif
            @if ((float) $order->tax > 0)
                <div class="flex justify-between">
                    <dt class="text-gray-500">PPN</dt>
                    <dd class="text-ink">@rupiah($order->tax)</dd>
                </div>
            @endif

            <div class="flex items-center justify-between border-t border-ink pt-2">
                <dt class="text-sm font-extrabold text-ink">TOTAL</dt>
                <dd class="text-lg font-extrabold text-ink">@rupiah($order->total)</dd>
            </div>
        </dl>

        <dl class="mt-3 space-y-1.5 border-t border-dashed border-gray-300 pt-3 text-xs">
            <div class="flex justify-between">
                <dt class="text-gray-500">Metode</dt>
                <dd class="text-ink">{{ $order->latestPayment?->method->label() ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Status Bayar</dt>
                <dd class="font-semibold text-ink">{{ $order->paymentStatus()->label() }}</dd>
            </div>
            @if ($order->latestPayment?->change_due > 0)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Kembalian</dt>
                    <dd class="text-ink">@rupiah($order->latestPayment->change_due)</dd>
                </div>
            @endif
        </dl>

        <footer class="mt-4 border-t-2 border-ink pt-3 text-center">
            <p class="text-xs font-semibold text-ink">Lacak pesanan Anda di menu “Lacak Pesanan”</p>
            <p class="mt-0.5 text-[11px] text-gray-500">
                Gunakan nomor <span class="font-bold">{{ $order->order_number }}</span> atau nomor WhatsApp Anda.
            </p>
            <p class="mt-2 text-[10px] text-gray-400">Terima kasih telah mempercayakan sepatu Anda kepada OhMyWash.</p>
        </footer>
    </div>
</body>
</html>