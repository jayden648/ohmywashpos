<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $customer === null
            ? ($this->user()?->can('create', App\Models\Customer::class) ?? false)
            : ($this->user()?->can('update', $customer) ?? false);
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            // Indonesian mobile numbers, stored in national format.
            'phone' => ['required', 'string', 'max:32', 'regex:/^0\d{8,13}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp harus diawali 0 dan berisi 9-14 digit (contoh: 081234567890).',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}