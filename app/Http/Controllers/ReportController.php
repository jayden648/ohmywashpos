<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * A sales summary over a selectable window, built from MySQL.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $days = (int) $request->input('days', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;

        $since = Carbon::today()->subDays($days - 1);

        $revenue = (float) Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->where('paid_at', '>=', $since)
            ->sum('amount');

        $orders = Order::query()
            ->notCancelled()
            ->where('created_at', '>=', $since)
            ->count();

        $cancelled = Order::query()
            ->where('status', OrderStatus::Cancelled->value)
            ->where('created_at', '>=', $since)
            ->count();

        $byMethod = Payment::query()
            ->selectRaw('method, count(*) as aggregate, sum(amount) as total')
            ->where('status', PaymentStatus::Paid->value)
            ->where('paid_at', '>=', $since)
            ->groupBy('method')
            ->get()
            // The model casts `method` to a PaymentMethod enum, so the raw
            // value is already resolved here; calling tryFrom() on it again
            // would pass an enum where a string is expected.
            ->map(fn ($row): array => [
                'method' => $row->method,
                'label' => $row->method instanceof PaymentMethod
                    ? $row->method->label()
                    : (PaymentMethod::tryFrom((string) $row->method)?->label() ?? (string) $row->method),
                'count' => (int) $row->aggregate,
                'total' => (float) $row->total,
            ]);

        // Table-qualified: `subtotal` exists on both orders and order_items.
        $topServices = OrderItem::query()
            ->selectRaw('order_items.service_name, sum(order_items.quantity) as total_quantity, sum(order_items.subtotal) as revenue')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', OrderStatus::Cancelled->value)
            ->where('orders.created_at', '>=', $since)
            ->groupBy('order_items.service_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return view('reports.index', [
            'days' => $days,
            'since' => $since,
            'revenue' => $revenue,
            'orders' => $orders,
            'cancelled' => $cancelled,
            'averageOrder' => $orders > 0 ? round($revenue / $orders, 2) : 0.0,
            'byMethod' => $byMethod,
            'topServices' => $topServices,
        ]);
    }
}