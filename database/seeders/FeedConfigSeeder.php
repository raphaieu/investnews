<?php

namespace Database\Seeders;

use App\Models\FeedConfig;
use Illuminate\Database\Seeder;

class FeedConfigSeeder extends Seeder
{
    public function run(): void
    {
        $feeds = [
            ['feed_id' => 'mt5-forex', 'enabled' => true, 'interval_sec' => 1],
            ['feed_id' => 'mt5-b3', 'enabled' => true, 'interval_sec' => 1],
        ];

        foreach ($feeds as $feed) {
            FeedConfig::updateOrCreate(
                ['feed_id' => $feed['feed_id']],
                ['enabled' => $feed['enabled'], 'interval_sec' => $feed['interval_sec']]
            );
        }
    }
}
