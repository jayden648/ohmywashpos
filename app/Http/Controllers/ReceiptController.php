<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\WhatsAppLinkBuilder;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly WhatsAppLinkBuilder $whatsapp,
    ) {}

    /**
     * A printable receipt for a single order.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'items', 'payments.receiver']);

        return view('receipts.show', [
            'order' => $order,
            'receiptLink' => $this->whatsapp->forReceipt($order),
        ]);
    }
}