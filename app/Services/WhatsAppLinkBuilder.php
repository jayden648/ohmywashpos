<?php

namespace App\Services;

use App\Models\Order;

/**
 * Builds the WhatsApp links offered by the "Send WhatsApp" button.
 *
 * Nothing is ever sent automatically; the cashier must click, which opens
 * WhatsApp with the message pre-filled.
 */
class WhatsAppLinkBuilder
{
    /**
     * A wa.me URL carrying the order status message.
     */
    public function forOrder(Order $order): string
    {
        $order->loadMissing('customer');

        return $this->link(
            (string) $order->customer->phone,
            sprintf(
                "Halo %s,\nPesanan %s saat ini %s.\nTotal: Rp%s\n— OhMyWash",
                $order->customer->name,
                $order->order_number,
                $order->status->label(),
                number_format((float) $order->total, 0, ',', '.'),
            ),
        );
    }

    /**
     * A wa.me URL carrying the receipt summary.
     */
    public function forReceipt(Order $order): string
    {
        $order->loadMissing('customer');

        $status = $order->isPaid() ? 'Lunas' : 'Belum Lunas';

        return $this->link(
            (string) $order->customer->phone,
            sprintf(
                "Struk OhMyWash %s\nTotal: Rp%s (%s)\n— OhMyWash",
                $order->order_number,
                number_format((float) $order->total, 0, ',', '.'),
                $status,
            ),
        );
    }

    /**
     * Normalise an Indonesian number to international form and encode the text.
     */
    private function link(string $phone, string $message): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}