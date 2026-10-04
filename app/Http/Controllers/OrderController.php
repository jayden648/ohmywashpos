<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\QualityCheck;
use App\Enums\UserRole;
use App\Http\Requests\AdvanceOrderStatusRequest;
use App\Http\Requests\StoreQualityControlRequest;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->with(['customer:id,name,customer_code,phone', 'items:id,order_id,service_name'])
            ->search($request->query('search'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'statuses' => [...OrderStatus::flow(), OrderStatus::Cancelled],
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'customer',
            'items',
            'shoes',
            'statusHistories.changedBy',
            'payments.receiver',
            'qualityControl.checker',
        ]);

        $nextStatus = $order->status->nextStatus();

        // The QC checklist must be filled in before the order can be released.
        $requiresQc = $nextStatus === OrderStatus::ReadyForPickup;

        return view('orders.show', [
            'order' => $order,
            'nextStatus' => $nextStatus,
            'requiresQc' => $requiresQc,
            'qualityChecks' => QualityCheck::ordered(),
            'qc' => $order->qualityControl?->checks ?? [],
            'canCancel' => request()->user()->can('cancel', $order),
            'canSeePrices' => request()->user()->hasAnyRole(UserRole::Admin, UserRole::Cashier),
        ]);
    }

    /**
     * Advance an order one step along the workflow.
     *
     * Transitions are validated server-side: arbitrary status jumps are
     * impossible because only `nextStatus()` is ever applied.
     */
    public function advance(AdvanceOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $next = $order->status->nextStatus();

        if ($next === null) {
            return back()->with('error', 'Pesanan sudah selesai dan tidak dapat dilanjutkan.');
        }

        // A failed quality control blocks release for pickup.
        if ($next === OrderStatus::ReadyForPickup) {
            $qc = $order->qualityControl;

            if ($qc === null || ! $qc->isPassed()) {
                return back()->with('error', 'Quality Control belum lengkap. Semua checklist wajib lolos sebelum siap diambil.');
            }
        }

        DB::transaction(function () use ($order, $next, $request): void {
            $order->update(['status' => $next]);

            if ($next === OrderStatus::PickedUp) {
                $order->update(['completed_at' => now()]);
            }

            OrderStatusHistory::record($order, $next, $request->user()->id, $request->input('note'));
        });

        return back()->with('status', "Status pesanan diperbarui: {$next->label()}.");
    }

    /**
     * Store the quality control checklist for an order.
     */
    public function storeQualityControl(StoreQualityControlRequest $request, Order $order): RedirectResponse
    {
        $checks = QualityCheck::fromInput($request->input('checks', []));

        $order->qualityControl()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'checked_by' => $request->user()->id,
                'checks' => $checks,
                'notes' => $request->input('notes'),
                'checked_at' => now(),
            ],
        );

        return back()->with('status', 'Checklist QC tersimpan.');
    }

    /**
     * Cancel an order. Refunds, if any, are left visible in the history.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        if (! in_array($order->status, OrderStatus::cancellableFrom(), true)) {
            return back()->with('error', 'Pesanan pada status ini tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($order, $request): void {
            $order->update(['status' => OrderStatus::Cancelled]);

            OrderStatusHistory::record(
                $order,
                OrderStatus::Cancelled,
                $request->user()->id,
                $request->input('note', 'Pesanan dibatalkan.'),
            );
        });

        return back()->with('status', "Pesanan {$order->order_number} dibatalkan.");
    }
}