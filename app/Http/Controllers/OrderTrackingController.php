<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Customer-facing tracking lookup.
     *
     * A result is only revealed for an exact order number, or a WhatsApp
     * number that matches; no unrelated orders are ever listed.
     */
    public function index(Request $request): View
    {
        $term = $request->query('q');
        $order = null;

        if (filled($term)) {
            $order = $this->findOrder($term);
        }

        return view('tracking.index', [
            'order' => $order,
            'term' => $term,
            'statuses' => OrderStatus::flow(),
        ]);
    }

    /**
     * Resolve a single order from an order number or a phone number.
     */
    private function findOrder(string $term): ?Order
    {
        $term = trim($term);
        $digits = preg_replace('/\D+/', '', $term) ?? '';

        return Order::query()
            ->with(['customer:id,name,phone', 'items:id,order_id,service_name,quantity', 'statusHistories.changedBy:id,name'])
            ->where(function ($query) use ($term, $digits): void {
                $query->where('order_number', $term);

                if ($digits !== '') {
                    $query->orWhereHas('customer', fn ($customer) => $customer->where('phone', $digits));
                }
            })
            ->latest()
            ->first();
    }
}