<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarketInstrumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $instrument = $this->route('market_instrument');

        return [
            'symbol' => [
                'sometimes',
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('market_instruments', 'symbol')->ignore($instrument->id),
            ],
            'display_name' => ['sometimes', 'required', 'string', 'max:255'],
            'feed_id' => ['nullable', 'string', 'max:32'],
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->has('symbol')) {
            $this->merge([
                'symbol' => strtoupper(trim((string) $this->input('symbol'))),
            ]);
        }
    }
}
