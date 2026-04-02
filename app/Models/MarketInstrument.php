<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['symbol', 'display_name', 'feed_id'])]
class MarketInstrument extends Model
{
    use Searchable;

    protected array $searchable = ['symbol', 'display_name'];

    protected static function booted(): void
    {
        static::saving(function (MarketInstrument $instrument) {
            $instrument->symbol = strtoupper(trim($instrument->symbol));
        });

        static::saved(function () {
            static::forgetResolvedCache();
        });

        static::deleted(function () {
            static::forgetResolvedCache();
        });
    }

    public static function forgetResolvedCache(): void
    {
        Cache::forget('market_instruments_resolved');
    }

    /**
     * @return array<string, string>
     */
    public static function resolvedNameMap(): array
    {
        return Cache::remember('market_instruments_resolved', 3600, function () {
            $defaults = [];
            foreach (config('market_instruments.defaults', []) as $symbol => $entry) {
                $defaults[$symbol] = is_array($entry) ? ($entry['display_name'] ?? $symbol) : $entry;
            }

            $fromDb = static::query()->pluck('display_name', 'symbol')->all();

            return array_merge($defaults, $fromDb);
        });
    }
}
