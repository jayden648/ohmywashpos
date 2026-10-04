<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', App\Models\Payment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(array_column(PaymentMethod::cases(), 'value'))],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Cash must cover the total; the shortfall is rejected here and the
     * change is computed again in integer cents by the controller.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('method') !== PaymentMethod::Cash->value) {
                return;
            }

            if ($this->input('amount_received') === null) {
                $validator->errors()->add('amount_received', 'Uang diterima wajib diisi untuk pembayaran cash.');

                return;
            }

            $order = $this->route('order');
            $due = round(((float) ($order->total ?? 0)) - $order->paidAmount(), 2);

            if (round((float) $this->input('amount_received'), 2) < $due) {
                $validator->errors()->add(
                    'amount_received',
                    'Uang diterima kurang dari sisa tagihan Rp'.number_format($due, 0, ',', '.').'.',
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Metode pembayaran wajib dipilih.',
            'method.in' => 'Metode pembayaran tidak dikenal.',
        ];
    }
}