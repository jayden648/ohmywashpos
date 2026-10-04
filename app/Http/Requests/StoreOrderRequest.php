<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Staff must not reach the till even by posting directly.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', App\Models\Order::class) ?? false;
    }

    /**
     * Only identifiers are accepted; every price and total is recalculated
     * server-side from the database.
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['required', 'integer', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],

            'promotion_code' => ['nullable', 'string', 'max:50'],

            'additional_fee' => ['nullable', 'numeric', 'min:0', 'max:1000000'],

            'notes' => ['nullable', 'string', 'max:1000'],

            'shoe' => ['nullable', 'array'],
            'shoe.brand' => ['nullable', 'string', 'max:100'],
            'shoe.model' => ['nullable', 'string', 'max:100'],
            'shoe.color' => ['nullable', 'string', 'max:100'],
            'shoe.size' => ['nullable', 'string', 'max:32'],
            'shoe.material' => ['nullable', 'string', 'max:100'],
            'shoe.condition_before' => ['nullable', 'string', 'max:255'],
            'shoe.damage_notes' => ['nullable', 'string', 'max:1000'],
            'shoe.customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Brand and size are required by the prototype's POS workflow.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (blank($this->input('shoe.brand'))) {
                $validator->errors()->add('shoe.brand', 'Brand sepatu wajib diisi.');
            }

            if (blank($this->input('shoe.size'))) {
                $validator->errors()->add('shoe.size', 'Ukuran sepatu wajib diisi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'items.required' => 'Keranjang tidak boleh kosong.',
            'items.min' => 'Tambahkan minimal satu layanan.',
            'items.*.service_id.exists' => 'Layanan yang dipilih tidak tersedia.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
        ];
    }
}