<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service === null
            ? ($this->user()?->can('create', App\Models\Service::class) ?? false)
            : ($this->user()?->can('update', $service) ?? false);
    }

    public function rules(): array
    {
        return [
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'estimated_duration' => ['required', 'integer', 'min:1', 'max:365'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.min' => 'Harga tidak boleh negatif.',
            'service_category_id.required' => 'Kategori wajib dipilih.',
            'estimated_duration.required' => 'Estimasi durasi wajib diisi.',
        ];
    }
}