<?php

namespace Database\Seeders;

use App\Models\MarketInstrument;
use Illuminate\Database\Seeder;

class MarketInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('market_instruments.defaults', []) as $symbol => $entry) {
            $displayName = is_array($entry) ? ($entry['display_name'] ?? $symbol) : $entry;
            $feedId = is_array($entry) ? ($entry['feed_id'] ?? 'mt5-forex') : 'mt5-forex';

            MarketInstrument::updateOrCreate(
                ['symbol' => $symbol],
                ['display_name' => $displayName, 'feed_id' => $feedId]
            );
        }
    }
}
