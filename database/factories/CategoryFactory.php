<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake('pt_BR')->unique()->randomElement([
            'Ações', 'Fundos Imobiliários', 'Renda Fixa', 'Criptomoedas', 'Economia',
            'Mercado Internacional', 'Commodities', 'Tecnologia', 'Finanças Pessoais', 'Startups',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
