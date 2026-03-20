<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Category;

class UpdateCategoryRequest extends FormRequest
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

            $routeCategory = $this->route('category');
            $currentId = $routeCategory?->id;

            $query = Category::query()->where('slug', $slug);
            if ($currentId) {
                $query->where('id', '!=', $currentId);
            }

            if ($query->exists()) {
                $fail('Já existe uma categoria com este nome.');
            }
        };

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
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
