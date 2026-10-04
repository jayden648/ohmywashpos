<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Issues the C001, C002, ... customer codes shown in the POS.
 */
class CustomerCodeGenerator
{
    /**
     * Produce the next unused customer code.
     *
     * The unique index on customer_code is the real guarantee; this just
     * keeps the sequence tidy and skips any code already taken.
     */
    public function next(): string
    {
        $last = DB::table('customers')
            ->where('customer_code', 'like', 'C%')
            ->orderByDesc('customer_code')
            ->value('customer_code');

        $next = ((int) preg_replace('/\D+/', '', (string) $last)) + 1;

        do {
            $code = 'C'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (Customer::query()->where('customer_code', $code)->exists());

        return $code;
    }
}