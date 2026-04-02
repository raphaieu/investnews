<?php

namespace Tests\Feature;

use App\Models\MarketInstrument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketInstrumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_admin_can_create_market_instrument(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/market-instruments', [
                'symbol' => 'xauusd',
                'display_name' => 'Ouro spot',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.symbol', 'XAUUSD')
            ->assertJsonPath('data.display_name', 'Ouro spot');

        $this->assertDatabaseHas('market_instruments', [
            'symbol' => 'XAUUSD',
            'display_name' => 'Ouro spot',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_market_instrument(): void
    {
        $response = $this->postJson('/api/admin/market-instruments', [
            'symbol' => 'EURUSD',
            'display_name' => 'Euro',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_list_market_instruments_with_search(): void
    {
        MarketInstrument::query()->create([
            'symbol' => 'XBRUSD',
            'display_name' => 'Brent',
        ]);
        MarketInstrument::query()->create([
            'symbol' => 'PETR4',
            'display_name' => 'Petrobras',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/market-instruments?search=XBR');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.symbol', 'XBRUSD');
    }

    public function test_admin_search_matches_full_phrase_or_all_tokens(): void
    {
        MarketInstrument::query()->create([
            'symbol' => 'US100',
            'display_name' => 'Nasdaq 100',
        ]);

        $this->actingAs($this->admin)
            ->getJson('/api/admin/market-instruments?search=nasdaq%20100')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->admin)
            ->getJson('/api/admin/market-instruments?search=nasdaq%20cento')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($this->admin)
            ->getJson('/api/admin/market-instruments?search=nasdaq%20us')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
