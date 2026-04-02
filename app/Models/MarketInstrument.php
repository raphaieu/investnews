<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['symbol', 'display_name'])]
class MarketInstrument extends Model
{
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
     * Busca por símbolo ou nome: frase completa OU todos os tokens (como notícias).
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $term = preg_replace('/\s+/', ' ', $term) ?: '';
        $tokens = array_values(array_filter(preg_split('/\s+/', $term) ?: []));

        $matchToken = function (Builder $builder, string $token): void {
            $builder
                ->where('symbol', 'like', "%{$token}%")
                ->orWhere('display_name', 'like', "%{$token}%");
        };

        return $query->where(function (Builder $q) use ($term, $tokens, $matchToken) {
            $q->where(function (Builder $phraseQuery) use ($term) {
                $phraseQuery
                    ->where('symbol', 'like', "%{$term}%")
                    ->orWhere('display_name', 'like', "%{$term}%");
            })->orWhere(function (Builder $tokenGroupQuery) use ($tokens, $matchToken) {
                foreach ($tokens as $token) {
                    $tokenGroupQuery->where(function (Builder $tokenQuery) use ($matchToken, $token) {
                        $matchToken($tokenQuery, $token);
                    });
                }
            });
        });
    }

    /**
     * Defaults do config mesclados com o banco (banco sobrescreve).
     *
     * @return array<string, string>
     */
    public static function resolvedNameMap(): array
    {
        return Cache::remember('market_instruments_resolved', 3600, function () {
            $defaults = config('market_instruments.defaults', []);
            $fromDb = static::query()->pluck('display_name', 'symbol')->all();

            return array_merge($defaults, $fromDb);
        });
    }
}
