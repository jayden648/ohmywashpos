<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order !== null && ($this->user()?->can('advance', $order) ?? false);
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}