<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketInstrumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symbol' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('market_instruments', 'symbol'),
            ],
            'display_name' => ['required', 'string', 'max:255'],
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
