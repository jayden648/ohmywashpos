<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generates the gap-free daily order numbers OMW-YYYYMMDD-XXXX.
 *
 * A row per calendar day in `order_sequences` is locked with
 * `lockForUpdate()` inside the caller's transaction, so two cashiers
 * creating orders at the same moment can never receive the same number.
 */
class OrderNumberGenerator
{
    /**
     * Reserve the next order number for today.
     *
     * Must be called inside a database transaction; the lock is held until
     * that transaction commits.
     */
    public function reserve(): string
    {
        $today = Carbon::today();

        $sequence = DB::table('order_sequences')
            ->where('sequence_date', $today->toDateString())
            ->lockForUpdate()
            ->first();

        if ($sequence === null) {
            // Concurrent inserts are resolved by the unique index; on a
            // duplicate key we fall through and re-read the locked row.
            try {
                DB::table('order_sequences')->insert([
                    'sequence_date' => $today->toDateString(),
                    'last_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $number = 1;
            } catch (\Illuminate\Database\QueryException $exception) {
                if (! $this->isDuplicateKey($exception)) {
                    throw $exception;
                }

                $sequence = DB::table('order_sequences')
                    ->where('sequence_date', $today->toDateString())
                    ->lockForUpdate()
                    ->first();

                $number = ((int) ($sequence->last_number ?? 0)) + 1;

                DB::table('order_sequences')
                    ->where('sequence_date', $today->toDateString())
                    ->update(['last_number' => $number, 'updated_at' => now()]);
            }
        } else {
            $number = ((int) $sequence->last_number) + 1;

            DB::table('order_sequences')
                ->where('sequence_date', $today->toDateString())
                ->update(['last_number' => $number, 'updated_at' => now()]);
        }

        return sprintf('OMW-%s-%04d', $today->format('Ymd'), $number);
    }

    /**
     * Detect a unique-constraint violation across drivers.
     */
    private function isDuplicateKey(\Illuminate\Database\QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        if ($sqlState === '23000' || $sqlState === '23505') {
            return true;
        }

        return str_contains(strtolower($exception->getMessage()), 'unique');
    }
}