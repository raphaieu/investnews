<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Models\Category;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $slugRule = function (string $attribute, mixed $value, \Closure $fail): void {
            $name = (string) $value;
            $slug = Str::slug($name);

            if ($slug === '') {
                return;
            }

            $exists = Category::query()->where('slug', $slug)->exists();

            if ($exists) {
                $fail('Já existe uma categoria com este nome.');
            }
        };

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
                $slugRule,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.unique' => 'Já existe uma categoria com esse nome.',
        ];
    }
}
