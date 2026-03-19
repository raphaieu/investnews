<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_published_news(): void
    {
        $category = Category::factory()->create();
        News::factory(3)->create(['category_id' => $category->id]);
        News::factory()->draft()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/news');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_public_can_search_news_by_title(): void
    {
        $category = Category::factory()->create();
        News::factory()->create([
            'title' => 'Bitcoin atinge novo recorde',
            'category_id' => $category->id,
        ]);
        News::factory()->create([
            'title' => 'Mercado de ações em alta',
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/news?search=Bitcoin');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Bitcoin atinge novo recorde']);
    }

    public function test_public_can_filter_news_by_category(): void
    {
        $acoes = Category::factory()->create(['name' => 'Ações', 'slug' => 'acoes']);
        $cripto = Category::factory()->create(['name' => 'Cripto', 'slug' => 'cripto']);

        News::factory(2)->create(['category_id' => $acoes->id]);
        News::factory(3)->create(['category_id' => $cripto->id]);

        $response = $this->getJson('/api/news?category=cripto');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_public_can_search_news_by_category_name_or_slug(): void
    {
        $economia = Category::factory()->create(['name' => 'Economia', 'slug' => 'economia']);
        $tecnologia = Category::factory()->create(['name' => 'Tecnologia', 'slug' => 'tecnologia']);

        News::factory()->create([
            'title' => 'PIB brasileiro fecha trimestre em alta',
            'category_id' => $economia->id,
        ]);
        News::factory()->create([
            'title' => 'Nova plataforma de IA para investidores',
            'category_id' => $tecnologia->id,
        ]);

        $response = $this->getJson('/api/news?search=economia');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'PIB brasileiro fecha trimestre em alta']);
    }

    public function test_public_can_search_news_by_title_or_description_and_category_terms_in_single_query(): void
    {
        $rendaFixa = Category::factory()->create(['name' => 'Renda Fixa', 'slug' => 'renda-fixa']);
        $commodities = Category::factory()->create(['name' => 'Commodities', 'slug' => 'commodities']);

        News::factory()->create([
            'title' => 'Id commodi rerum voluptatem.',
            'content' => 'Analise do mercado para renda fixa e perfil conservador.',
            'category_id' => $rendaFixa->id,
        ]);
        News::factory()->create([
            'title' => 'Id commodi no setor de metais',
            'content' => 'Movimento recente de commodities no exterior.',
            'category_id' => $commodities->id,
        ]);

        $response = $this->getJson('/api/news?search=commodi renda fixa');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category.slug', 'renda-fixa');
    }

    public function test_public_can_view_news_detail_by_slug(): void
    {
        $category = Category::factory()->create();
        $news = News::factory()->create([
            'slug' => 'minha-noticia-teste',
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/news/minha-noticia-teste');

        $response->assertOk()
            ->assertJsonFragment(['slug' => 'minha-noticia-teste']);
    }
}
