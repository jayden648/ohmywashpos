<?php

namespace App\Enums;

/**
 * The quality control checklist an order must pass before pickup.
 */
enum QualityCheck: string
{
    case UpperClean = 'upper_clean';
    case MidsoleClean = 'midsole_clean';
    case OutsoleClean = 'outsole_clean';
    case ShoelaceClean = 'shoelace_clean';
    case NoChemicalResidue = 'no_chemical_residue';
    case CompletelyDry = 'completely_dry';
    case NoDamageAfterCleaning = 'no_damage_after_cleaning';
    case CustomerRequestFulfilled = 'customer_request_fulfilled';

    public function label(): string
    {
        return match ($this) {
            self::UpperClean => 'Upper clean',
            self::MidsoleClean => 'Midsole clean',
            self::OutsoleClean => 'Outsole clean',
            self::ShoelaceClean => 'Shoelace clean',
            self::NoChemicalResidue => 'No chemical residue',
            self::CompletelyDry => 'Shoes completely dry',
            self::NoDamageAfterCleaning => 'No damage after cleaning',
            self::CustomerRequestFulfilled => 'Customer request fulfilled',
        };
    }

    /**
     * @return array<int, self>
     */
    public static function ordered(): array
    {
        return self::cases();
    }

    /**
     * Evaluate a stored checklist.
     *
     * Every item must be present and true before an order may become ready
     * for pickup. Unknown keys are ignored, missing keys count as failures.
     *
     * @param  array<string, mixed>  $checks
     * @return array<int, self> The checks that have not passed.
     */
    public static function failed(array $checks): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $check): bool => ($checks[$check->value] ?? false) !== true,
        ));
    }

    /**
     * Build a checklist from validated input, defaulting every key to false.
     *
     * @param  array<int|string, mixed>  $input
     * @return array<string, bool>
     */
    public static function fromInput(array $input): array
    {
        $given = [];

        foreach ($input as $key => $value) {
            if (is_string($key)) {
                $given[$key] = filter_var($value, FILTER_VALIDATE_BOOL);
            }
        }

        $checks = [];

        foreach (self::cases() as $check) {
            $checks[$check->value] = $given[$check->value] ?? false;
        }

        return $checks;
    }
}