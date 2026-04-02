<?php

namespace App\Repositories\FeedConfigs;

use App\Models\FeedConfig;

class EloquentFeedConfigRepository implements FeedConfigRepositoryInterface
{
    public function findByFeedId(string $feedId): ?FeedConfig
    {
        return FeedConfig::query()->where('feed_id', $feedId)->first();
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return FeedConfig::query()->orderBy('feed_id')->get();
    }

    public function toggleEnabled(string $feedId): ?FeedConfig
    {
        $config = $this->findByFeedId($feedId);

        if (! $config) {
            return null;
        }

        $config->enabled = ! $config->enabled;
        $config->save();

        return $config;
    }
}
