<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        User::factory()->create([
            'email' => 'admin@investnews.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Origin' => config('app.url'),
            'Referer' => rtrim(config('app.url'), '/').'/',
        ])->postJson('/api/login', [
            'email' => 'admin@investnews.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['email' => 'admin@investnews.com']);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@investnews.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->withHeaders([
            'Origin' => config('app.url'),
            'Referer' => rtrim(config('app.url'), '/').'/',
        ])->postJson('/api/login', [
            'email' => 'admin@investnews.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }
}
