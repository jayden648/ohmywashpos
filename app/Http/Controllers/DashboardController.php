<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Every figure below is aggregated from MySQL; nothing is hard coded.
     */
    public function index(): View
    {
        $today = Order::query()
            ->notCancelled()
            ->createdOn(now())
            ->count();

        // Revenue counts settled money only, not merely ordered value.
        $revenueToday = (float) Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', now()->toDateString())
            ->sum('amount');

        $inProgress = Order::query()->inProgress()->count();
        $readyForPickup = Order::query()->where('status', OrderStatus::ReadyForPickup->value)->count();
        $completed = Order::query()->where('status', OrderStatus::PickedUp->value)->count();

        $unpaid = Order::query()
            ->notCancelled()
            ->whereDoesntHave('payments', fn ($query) => $query->where('status', PaymentStatus::Paid->value))
            ->count();

        return view('dashboard', [
            'todayOrders' => $today,
            'revenueToday' => $revenueToday,
            'inProgress' => $inProgress,
            'readyForPickup' => $readyForPickup,
            'completed' => $completed,
            'unpaid' => $unpaid,
            'statusDistribution' => $this->statusDistribution(),
            'popularServices' => $this->popularServices(),
            'lowStock' => InventoryItem::query()->lowStock()->orderBy('quantity')->limit(5)->get(),
            'recentOrders' => Order::query()
                ->with('customer:id,name,customer_code')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    /**
     * Order counts per workflow status, including statuses with zero orders.
     *
     * @return array<int, array{status: OrderStatus, count: int, percentage: int}>
     */
    private function statusDistribution(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $max = max(1, (int) $counts->max());

        return collect(OrderStatus::cases())
            ->map(fn (OrderStatus $status): array => [
                'status' => $status,
                'count' => (int) ($counts[$status->value] ?? 0),
                'percentage' => (int) round(((int) ($counts[$status->value] ?? 0) / $max) * 100),
            ])
            ->all();
    }

    /**
     * Best sellers by quantity sold.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function popularServices()
    {
        // Columns are table-qualified: both orders and order_items have `subtotal`.
        return OrderItem::query()
            ->selectRaw('order_items.service_name, sum(order_items.quantity) as total_quantity, sum(order_items.subtotal) as revenue')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', OrderStatus::Cancelled->value)
            ->groupBy('order_items.service_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();
    }
}