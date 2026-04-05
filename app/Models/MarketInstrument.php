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

    /**
     * Remove caracteres que conflitam com channel names do broadcasting.
     * Ex.: #AAPL → AAPL, #PETR4 → PETR4
     */
    public static function sanitizeSymbol(string $symbol): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '', strtoupper(trim($symbol)));
    }

    protected static function booted(): void
    {
        static::saving(function (MarketInstrument $instrument) {
            $instrument->symbol = static::sanitizeSymbol($instrument->symbol);
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
                $clean = static::sanitizeSymbol($symbol);
                $defaults[$clean] = is_array($entry) ? ($entry['display_name'] ?? $clean) : $entry;
            }

            $fromDb = static::query()->pluck('display_name', 'symbol')->all();

            return array_merge($defaults, $fromDb);
        });
    }
}
