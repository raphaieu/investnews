<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/categories', [
                'name' => 'Ações',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Ações', 'slug' => 'acoes']);

        $this->assertDatabaseHas('categories', ['name' => 'Ações']);
    }

    public function test_category_creation_requires_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::factory()->create(['name' => 'Ações']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/categories', ['name' => 'Ações']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_unauthenticated_user_cannot_create_category(): void
    {
        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Ações',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_list_categories_with_pagination_and_name_filter(): void
    {
        Category::factory()->create(['name' => 'Renda Fixa']);
        Category::factory()->create(['name' => 'Tecnologia']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/categories?search=Renda&per_page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Renda Fixa');
    }

    public function test_admin_cannot_create_category_with_duplicated_slug(): void
    {
        Category::factory()->create(['name' => 'Ações', 'slug' => 'acoes']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/categories', [
                'name' => 'Acoes', // slug = "acoes"
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_admin_cannot_update_category_to_duplicated_slug(): void
    {
        $other = Category::factory()->create(['name' => 'Ações', 'slug' => 'acoes']);
        $category = Category::factory()->create(['name' => 'Tecnologia', 'slug' => 'tecnologia']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/categories/{$category->id}", [
                'name' => 'Acoes', // slug = "acoes"
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
