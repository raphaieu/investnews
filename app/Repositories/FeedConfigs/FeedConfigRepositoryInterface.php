<?php

namespace App\Repositories\FeedConfigs;

use App\Models\FeedConfig;

interface FeedConfigRepositoryInterface
{
    public function findByFeedId(string $feedId): ?FeedConfig;

    /** @return \Illuminate\Database\Eloquent\Collection<int, FeedConfig> */
    public function all(): \Illuminate\Database\Eloquent\Collection;

    public function toggleEnabled(string $feedId): ?FeedConfig;
}
