<?php

namespace App\Http\Requests;

use App\Enums\QualityCheck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQualityControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order !== null && ($this->user()?->can('performQc', $order) ?? false);
    }

    public function rules(): array
    {
        return [
            'checks' => ['required', 'array'],
            'checks.*' => ['boolean'],
            'checks.'.QualityCheck::UpperClean->value => ['accepted'],
            'checks.'.QualityCheck::MidsoleClean->value => ['accepted'],
            'checks.'.QualityCheck::OutsoleClean->value => ['accepted'],
            'checks.'.QualityCheck::ShoelaceClean->value => ['accepted'],
            'checks.'.QualityCheck::NoChemicalResidue->value => ['accepted'],
            'checks.'.QualityCheck::CompletelyDry->value => ['accepted'],
            'checks.'.QualityCheck::NoDamageAfterCleaning->value => ['accepted'],
            'checks.'.QualityCheck::CustomerRequestFulfilled->value => ['accepted'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        $messages = ['checks.required' => 'Checklist QC wajib diisi.'];

        foreach (QualityCheck::cases() as $check) {
            $messages['checks.'.$check->value.'.accepted'] = 'QC gagal: '.$check->label().' wajib lolos.';
        }

        return $messages;
    }
}