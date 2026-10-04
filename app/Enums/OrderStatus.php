<?php

namespace App\Enums;

/**
 * The controlled order lifecycle, mirroring the OhMyWash POS workflow.
 *
 * Transitions are strictly sequential; see nextStatus() for the rules.
 */
enum OrderStatus: string
{
    case OrderCreated = 'order_created';
    case Payment = 'payment';
    case ShoesReceived = 'shoes_received';
    case Inspection = 'inspection';
    case Washing = 'washing';
    case Drying = 'drying';
    case Finishing = 'finishing';
    case QualityControl = 'quality_control';
    case ReadyForPickup = 'ready_for_pickup';
    case PickedUp = 'picked_up';
    case Cancelled = 'cancelled';

    /**
     * The full forward path, in order.
     *
     * @return array<int, self>
     */
    public static function flow(): array
    {
        return [
            self::OrderCreated,
            self::Payment,
            self::ShoesReceived,
            self::Inspection,
            self::Washing,
            self::Drying,
            self::Finishing,
            self::QualityControl,
            self::ReadyForPickup,
            self::PickedUp,
        ];
    }

    /**
     * Human readable label (Indonesian, matching the POS interface).
     */
    public function label(): string
    {
        return match ($this) {
            self::OrderCreated => 'Pesanan Dibuat',
            self::Payment => 'Pembayaran',
            self::ShoesReceived => 'Sepatu Diterima',
            self::Inspection => 'Inspeksi',
            self::Washing => 'Pencucian',
            self::Drying => 'Pengeringan',
            self::Finishing => 'Finishing',
            self::QualityControl => 'Quality Control',
            self::ReadyForPickup => 'Siap Diambil',
            self::PickedUp => 'Diambil',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /**
     * Short label used where the full name would not fit.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::OrderCreated => 'Dibuat',
            self::Payment => 'Bayar',
            self::ShoesReceived => 'Diterima',
            self::Inspection => 'Inspeksi',
            self::Washing => 'Cuci',
            self::Drying => 'Kering',
            self::Finishing => 'Finishing',
            self::QualityControl => 'QC',
            self::ReadyForPickup => 'Siap',
            self::PickedUp => 'Selesai',
            self::Cancelled => 'Batal',
        };
    }

    /**
     * Zero based position within the forward flow, or null when off-flow.
     */
    public function stepIndex(): ?int
    {
        $index = array_search($this, self::flow(), true);

        return $index === false ? null : (int) $index;
    }

    /**
     * The single status this one is allowed to advance to.
     */
    public function nextStatus(): ?self
    {
        $index = $this->stepIndex();

        if ($index === null) {
            return null;
        }

        return self::flow()[$index + 1] ?? null;
    }

    /**
     * How far along the workflow this order is, as a percentage.
     */
    public function progress(): int
    {
        if ($this === self::Cancelled) {
            return 0;
        }

        if ($this === self::PickedUp) {
            return 100;
        }

        return (int) round(((int) $this->stepIndex() / (count(self::flow()) - 1)) * 100);
    }

    /**
     * A cancelled order is off the forward path and cannot progress.
     */
    public function isCancelled(): bool
    {
        return $this === self::Cancelled;
    }

    /**
     * Whether the order is still moving through the workflow.
     */
    public function isInProgress(): bool
    {
        $index = $this->stepIndex();

        return $index !== null && $index > 1 && $index < 8;
    }

    /**
     * Tailwind classes for the status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::OrderCreated, self::Payment => 'bg-gray-100 text-gray-700 ring-gray-200',
            self::ShoesReceived, self::Inspection => 'bg-brand-soft text-brand-dark ring-brand/40',
            self::Washing, self::Drying, self::Finishing => 'bg-brand-soft text-brand-dark ring-brand/50',
            self::QualityControl => 'bg-amber-100 text-amber-800 ring-amber-200',
            self::ReadyForPickup => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
            self::PickedUp => 'bg-black text-brand ring-black',
            self::Cancelled => 'bg-red-100 text-red-700 ring-red-200',
        };
    }

    /**
     * The statuses an order may be cancelled from.
     *
     * @return array<int, self>
     */
    public static function cancellableFrom(): array
    {
        return [
            self::OrderCreated,
            self::Payment,
            self::ShoesReceived,
            self::Inspection,
            self::Washing,
            self::Drying,
            self::Finishing,
            self::QualityControl,
        ];
    }
}