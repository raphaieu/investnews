<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    private const CATEGORY_COLORS = [
        'Ações' => 'amber',
        'Fundos Imobiliários' => 'violet',
        'Renda Fixa' => 'indigo',
        'Criptomoedas' => 'orange',
        'Economia' => 'emerald',
        'Mercado Internacional' => 'cyan',
        'Commodities' => 'yellow',
        'Tecnologia' => 'sky',
        'Finanças Pessoais' => 'lime',
        'Startups' => 'rose',
    ];

    public function definition(): array
    {
        $name = fake('pt_BR')->unique()->randomElement(array_keys(self::CATEGORY_COLORS));

        return [
            'name' => $name,
            'color' => self::CATEGORY_COLORS[$name] ?? 'slate',
        ];
    }
}
