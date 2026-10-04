<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Service;

/**
 * The single source of truth for order arithmetic.
 *
 * The browser may preview a total for the cashier, but every figure that
 * reaches the database is produced here, from prices read out of MySQL.
 * All arithmetic runs in integer cents so rounding never leaks in.
 */
class OrderCalculator
{
    /**
     * Price a set of requested services straight from the database.
     *
     * @param  array<int, array{service_id: int|string, quantity: int}>  $lines
     * @return array{
     *     lines: array<int, array{service: Service, quantity: int, unit_price: float, subtotal: float}>,
     *     subtotal: float,
     *     discount: float,
     *     additional_fee: float,
     *     tax: float,
     *     total: float
     * }
     */
    public function calculate(array $lines, ?Promotion $promotion = null, float $additionalFee = 0.0): array
    {
        $priced = [];
        $subtotalCents = 0;

        // Merge duplicate service lines so a repeated tap cannot double-charge
        // different unit prices for the same product.
        $quantities = [];

        foreach ($lines as $line) {
            $serviceId = (int) $line['service_id'];
            $quantity = max(1, (int) ($line['quantity'] ?? 1));
            $quantities[$serviceId] = ($quantities[$serviceId] ?? 0) + $quantity;
        }

        if ($quantities !== []) {
            $services = Service::query()
                ->whereIn('id', array_keys($quantities))
                ->get()
                ->keyBy('id');

            foreach ($quantities as $serviceId => $quantity) {
                // A service that vanished or was deactivated mid-sale is refused
                // rather than silently priced at zero.
                $service = $services->get($serviceId);

                if ($service === null) {
                    throw new \InvalidArgumentException("Layanan #{$serviceId} tidak tersedia.");
                }

                $unitPriceCents = (int) round((float) $service->price * 100);
                $subtotalCents = $unitPriceCents * $quantity;

                $priced[] = [
                    'service' => $service,
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceCents / 100,
                    'subtotal' => $subtotalCents / 100,
                ];

                $subtotalCents += $subtotalCents;
            }
        }

        $subtotal = $subtotalCents / 100;

        $discountCents = 0;

        if ($promotion !== null && $promotion->isRedeemable() && $promotion->meetsMinimum($subtotal)) {
            $discountCents = (int) round($promotion->discountFor($subtotal) * 100);
        }

        $additionalFeeCents = max(0, (int) round($additionalFee * 100));

        // Tax is currently not levied; the column exists for future use.
        $taxCents = 0;

        $totalCents = max(0, $subtotalCents - $discountCents + $additionalFeeCents + $taxCents);

        return [
            'lines' => $priced,
            'subtotal' => $subtotalCents / 100,
            'discount' => $discountCents / 100,
            'additional_fee' => $additionalFeeCents / 100,
            'tax' => $taxCents / 100,
            'total' => $totalCents / 100,
        ];
    }
}