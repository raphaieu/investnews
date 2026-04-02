<?php

namespace Database\Seeders;

use App\Models\MarketInstrument;
use Illuminate\Database\Seeder;

class MarketInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('market_instruments.defaults', []) as $symbol => $displayName) {
            MarketInstrument::updateOrCreate(
                ['symbol' => $symbol],
                ['display_name' => $displayName]
            );
        }
    }
}
