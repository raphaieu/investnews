<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->category = Category::factory()->create(['name' => 'Ações']);
    }

    public function test_admin_can_create_news(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/news', [
                'title' => 'Nova alta nas ações',
                'content' => 'O mercado registrou uma nova alta hoje.',
                'category_id' => $this->category->id,
                'published_at' => '2026-03-19',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Nova alta nas ações']);

        $this->assertDatabaseHas('news', ['title' => 'Nova alta nas ações']);
    }

    public function test_news_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/news', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'category_id']);
    }

    public function test_admin_can_list_news_with_pagination_and_title_filter(): void
    {
        News::factory()->create([
            'title' => 'Mercado futuro em alta',
            'category_id' => $this->category->id,
        ]);
        News::factory()->create([
            'title' => 'Cenário global de juros',
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/news?search=Mercado&per_page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Mercado futuro em alta');
    }
}
