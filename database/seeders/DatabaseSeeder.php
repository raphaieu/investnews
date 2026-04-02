<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@investnews.com',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            MarketInstrumentSeeder::class,
        ]);

        $categories = Category::factory(5)->create();

        $categories->each(function (Category $category) {
            News::factory(4)->create([
                'category_id' => $category->id,
            ]);
        });
    }
}
