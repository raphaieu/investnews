<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Models\News;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $slugRule = function (string $attribute, mixed $value, \Closure $fail): void {
            $title = (string) $value;
            $slug = Str::slug($title);

            if ($slug === '') {
                return;
            }

            $exists = News::query()->where('slug', $slug)->exists();

            if ($exists) {
                $fail('Já existe uma notícia com este título.');
            }
        };

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                $slugRule,
            ],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'published_at' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'content.required' => 'O conteúdo é obrigatório.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists' => 'Categoria inválida.',
        ];
    }
}
