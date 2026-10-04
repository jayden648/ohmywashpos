<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * The till's payment screen for one order.
     */
    public function create(Order $order): View
    {
        $this->authorize('pay', $order);

        $order->load('payments');

        return view('payments.create', [
            'order' => $order,
            'due' => max(0, round((float) $order->total - $order->paidAmount(), 2)),
            'paymentMethods' => PaymentMethod::ordered(),
        ]);
    }

    /**
     * Record a payment against an order.
     *
     * The amount due is recalculated from the database, and change is
     * recomputed server-side in integer rupiah.
     */
    public function store(StorePaymentRequest $request, Order $order): RedirectResponse
    {
        $method = PaymentMethod::from($request->string('method')->toString());

        $due = max(0, round((float) $order->total - $order->paidAmount(), 2));

        // Partial payments are allowed; the order settles once fully covered.
        $amount = $request->filled('amount') ? (float) $request->input('amount') : $due;
        $amount = min($amount, $due);

        $amountReceived = $method->requiresTenderedAmount()
            ? (float) $request->input('amount_received')
            : $amount;

        $change = max(0, round($amountReceived - $amount, 2));

        $payment = DB::transaction(function () use ($order, $amount, $method, $request, $amountReceived, $change): Payment {
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $method,
                // Provisional; corrected below once the order total is known.
                'status' => PaymentStatus::Paid,
                'reference' => $request->input('reference'),
                'amount_received' => $amountReceived,
                'change_due' => $change,
                'paid_at' => now(),
                'received_by' => $request->user()->id,
            ]);

            $order->refresh();

            // Advance to "Payment" the first time money is taken.
            if ($order->status === OrderStatus::OrderCreated && $order->paymentStatus() === PaymentStatus::Paid) {
                $order->update(['status' => OrderStatus::Payment]);

                OrderStatusHistory::record($order, OrderStatus::Payment, $request->user()->id, 'Pembayaran diterima.');
            }

            return $payment;
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('status', $change > 0
                ? "Pembayaran diterima. Kembalian: Rp".number_format($change, 0, ',', '.').'.'
                : 'Pembayaran berhasil dicatat.');
    }

    /**
     * A ledger of every payment taken.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::query()
            ->with(['order:id,order_number,status', 'receiver:id,name'])
            ->when($request->filled('method'), fn ($query) => $query->where('method', $request->string('method')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim();

                $query->whereHas('order', fn ($q) => $q->where('order_number', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
            'methods' => PaymentMethod::ordered(),
            'statuses' => PaymentStatus::cases(),
            'filters' => $request->only('search', 'method', 'status'),
        ]);
    }
}