<?php

namespace Tests\Feature;

use App\Models\FeedConfig;
use App\Models\MarketInstrument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedConfigTest extends TestCase
{
    use RefreshDatabase;

    private string $bearerToken = 'test-feed-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.market.key' => $this->bearerToken]);
    }

    public function test_feed_config_returns_config_and_symbols(): void
    {
        FeedConfig::create(['feed_id' => 'mt5-forex', 'enabled' => true, 'interval_sec' => 2]);
        MarketInstrument::create(['symbol' => 'XAUUSD', 'display_name' => 'Ouro', 'feed_id' => 'mt5-forex']);
        MarketInstrument::create(['symbol' => 'US500', 'display_name' => 'S&P 500', 'feed_id' => 'mt5-forex']);
        MarketInstrument::create(['symbol' => 'PETR4', 'display_name' => 'Petrobras', 'feed_id' => 'mt5-b3']);

        $response = $this->getJson('/api/feed/config?feed_id=mt5-forex', [
            'Authorization' => "Bearer {$this->bearerToken}",
        ]);

        $response->assertOk()
            ->assertJson([
                'feed_id' => 'mt5-forex',
                'enabled' => true,
                'interval_sec' => 2,
                'symbols' => ['US500', 'XAUUSD'],
            ]);

        $this->assertCount(2, $response->json('symbols'));
    }

    public function test_feed_config_returns_404_for_unknown_feed(): void
    {
        $response = $this->getJson('/api/feed/config?feed_id=unknown', [
            'Authorization' => "Bearer {$this->bearerToken}",
        ]);

        $response->assertNotFound();
    }

    public function test_feed_config_returns_422_without_feed_id(): void
    {
        $response = $this->getJson('/api/feed/config', [
            'Authorization' => "Bearer {$this->bearerToken}",
        ]);

        $response->assertUnprocessable();
    }

    public function test_feed_config_returns_401_without_bearer_token(): void
    {
        $response = $this->getJson('/api/feed/config?feed_id=mt5-forex');

        $response->assertUnauthorized();
    }

    public function test_feed_config_returns_disabled_feed(): void
    {
        FeedConfig::create(['feed_id' => 'mt5-b3', 'enabled' => false, 'interval_sec' => 5]);

        $response = $this->getJson('/api/feed/config?feed_id=mt5-b3', [
            'Authorization' => "Bearer {$this->bearerToken}",
        ]);

        $response->assertOk()
            ->assertJson([
                'feed_id' => 'mt5-b3',
                'enabled' => false,
                'interval_sec' => 5,
                'symbols' => [],
            ]);
    }
}
