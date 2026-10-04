<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Shoe;
use App\Services\OrderCalculator;
use App\Services\OrderNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private readonly OrderCalculator $calculator,
        private readonly OrderNumberGenerator $orderNumbers,
    ) {}

    /**
     * Render the till. Everything shown comes from MySQL.
     */
    public function index(Request $request): View
    {
        $this->authorize('create', Order::class);

        $categories = ServiceCategory::query()
            ->active()
            ->with(['services' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'customer_code', 'name', 'phone']);

        $promotions = Promotion::query()->active()->orderBy('code')->get();

        $cart = $request->session()->get('pos.cart', []);

        $totals = $this->preview(
            $cart,
            $request->session()->get('pos.promotion_code'),
            (float) $request->session()->get('pos.additional_fee', 0),
        );

        return view('pos.index', [
            'categories' => $categories,
            'customers' => $customers,
            'promotions' => $promotions,
            'cart' => $cart,
            'shoe' => $request->session()->get('pos.shoe', []),
            'customerId' => $request->session()->get('pos.customer_id'),
            'promotionCode' => $request->session()->get('pos.promotion_code'),
            'additionalFee' => (float) $request->session()->get('pos.additional_fee', 0),
            'totals' => $totals,
            'paymentMethods' => PaymentMethod::ordered(),
        ]);
    }

    /**
     * Recalculate the cart preview purely for display. The authoritative
     * figures are recomputed again when the order is stored.
     */
    private function preview(array $cart, ?string $promotionCode, float $additionalFee): array
    {
        $empty = [
            'lines' => [], 'subtotal' => 0, 'discount' => 0,
            'additional_fee' => 0, 'tax' => 0, 'total' => 0, 'promotion' => null,
        ];

        $lines = collect($cart)->map(fn (array $item): array => [
            'service_id' => $item['service_id'],
            'quantity' => $item['quantity'] ?? 1,
        ])->values()->all();

        if ($lines === []) {
            return $empty;
        }

        $promotion = $promotionCode !== null
            ? Promotion::query()->where('code', $promotionCode)->first()
            : null;

        return $this->calculator->calculate($lines, $promotion, $additionalFee) + ['promotion' => $promotion];
    }
/**
     * Persist a cart change for the cashier to keep building the order.
     */
    public function updateCart(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $data = $request->validate([
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'action' => ['required', 'in:add,increment,decrement,remove,clear'],
        ]);

        $cart = $request->session()->get('pos.cart', []);

        switch ($data['action']) {
            case 'add':
                $serviceId = (int) $data['service_id'];

                if (! Service::query()->active()->whereKey($serviceId)->exists()) {
                    return back()->with('error', 'Layanan tidak tersedia.');
                }

                $existing = collect($cart)->firstWhere('service_id', $serviceId);

                $cart = $existing !== null
                    ? collect($cart)->map(fn (array $item): array => $item['service_id'] === $serviceId
                        ? [...$item, 'quantity' => $item['quantity'] + 1]
                        : $item)->values()->all()
                    : [...$cart, ['service_id' => $serviceId, 'quantity' => 1]];
                break;

            case 'increment':
            case 'decrement':
                $delta = $data['action'] === 'increment' ? 1 : -1;

                $cart = collect($cart)->map(fn (array $item): array => $item['service_id'] === (int) ($data['service_id'] ?? 0)
                    ? [...$item, 'quantity' => max(1, $item['quantity'] + $delta)]
                    : $item)->values()->all();
                break;

            case 'remove':
                $cart = collect($cart)
                    ->reject(fn (array $item): bool => $item['service_id'] === (int) ($data['service_id'] ?? 0))
                    ->values()
                    ->all();
                break;

            case 'clear':
                $cart = [];
                break;
        }

        $request->session()->put('pos.cart', $cart);

        return back();
    }

    /**
     * Update the customer, shoe details, promo and fee held in the session.
     */
    public function updateContext(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
            'additional_fee' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'shoe' => ['nullable', 'array'],
            'shoe.brand' => ['nullable', 'string', 'max:100'],
            'shoe.model' => ['nullable', 'string', 'max:100'],
            'shoe.color' => ['nullable', 'string', 'max:100'],
            'shoe.size' => ['nullable', 'string', 'max:32'],
            'shoe.material' => ['nullable', 'string', 'max:100'],
            'shoe.condition_before' => ['nullable', 'string', 'max:255'],
            'shoe.damage_notes' => ['nullable', 'string', 'max:1000'],
            'shoe.customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->session()->put('pos.customer_id', $data['customer_id'] ?? null);
        $request->session()->put('pos.shoe', $data['shoe'] ?? []);
        $request->session()->put('pos.additional_fee', (float) ($data['additional_fee'] ?? 0));

        $code = isset($data['promotion_code']) ? strtoupper(trim($data['promotion_code'])) : null;

        if ($code === null || $code === '') {
            $request->session()->forget('pos.promotion_code');

            return back();
        }

        $promotion = Promotion::query()->where('code', $code)->first();

        if ($promotion === null || ! $promotion->isRedeemable()) {
            return back()->with('error', 'Kode promo tidak valid atau sudah tidak berlaku.');
        }

        $request->session()->put('pos.promotion_code', $promotion->code);

        return back();
    }

    /**
     * Create the order, items, shoe record and history in one transaction.
     * Every amount is recomputed here from MySQL; nothing is trusted from
     * the browser.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        // The validated request is the single source of truth; the session
        // cart is only a convenience for the on-screen interaction.
        $lines = collect($request->validated('items'))
            ->map(fn (array $item): array => [
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
            ])
            ->values()
            ->all();

        if ($lines === []) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        $promotionCode = $request->session()->get('pos.promotion_code');

        $promotion = $promotionCode !== null
            ? Promotion::query()->where('code', $promotionCode)->first()
            : null;

        $additionalFee = (float) $request->session()->get('pos.additional_fee', 0);

        $order = DB::transaction(function () use ($request, $lines, $promotion, $additionalFee): Order {
            $totals = $this->calculator->calculate($lines, $promotion, $additionalFee);

            $order = Order::create([
                'order_number' => $this->orderNumbers->reserve(),
                'customer_id' => $request->integer('customer_id'),
                'status' => OrderStatus::OrderCreated,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'additional_fee' => $totals['additional_fee'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'notes' => $request->input('notes'),
                'received_at' => now(),
                'created_by' => $request->user()->id,
            ]);

            foreach ($totals['lines'] as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $line['service']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                    'service_name' => $line['service']->name,
                ]);
            }

            if ($shoe = $request->input('shoe')) {
                Shoe::create(['order_id' => $order->id] + array_filter([
                    'brand' => $shoe['brand'] ?? null,
                    'model' => $shoe['model'] ?? null,
                    'color' => $shoe['color'] ?? null,
                    'size' => $shoe['size'] ?? null,
                    'material' => $shoe['material'] ?? null,
                    'condition_before' => $shoe['condition_before'] ?? null,
                    'damage_notes' => $shoe['damage_notes'] ?? null,
                    'customer_notes' => $shoe['customer_notes'] ?? null,
                ], fn ($value): bool => $value !== null));
            }

            OrderStatusHistory::record($order, OrderStatus::OrderCreated, $request->user()->id, 'Pesanan dibuat di kasir.');

            if ($promotion !== null && $totals['discount'] > 0) {
                $promotion->increment('used_count');
            }

            return $order;
        });

        $request->session()->forget([
            'pos.cart', 'pos.shoe', 'pos.customer_id', 'pos.promotion_code', 'pos.additional_fee',
        ]);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Pesanan {$order->order_number} berhasil dibuat. Lanjutkan ke pembayaran.");
    }
}
