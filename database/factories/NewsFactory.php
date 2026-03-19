<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = fake('pt_BR')->sentence(rand(5, 10));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => implode("\n\n", fake('pt_BR')->paragraphs(rand(3, 6))),
            'category_id' => Category::factory(),
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}
