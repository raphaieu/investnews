<?php

namespace App\Services\FeedConfigs;

use App\Models\MarketInstrument;
use App\Repositories\FeedConfigs\FeedConfigRepositoryInterface;

class FeedConfigService
{
    public function __construct(
        private readonly FeedConfigRepositoryInterface $feedConfigRepository
    ) {}

    public function getConfigPayload(string $feedId): ?array
    {
        $config = $this->feedConfigRepository->findByFeedId($feedId);

        if (! $config) {
            return null;
        }

        $symbols = MarketInstrument::query()
            ->where('feed_id', $feedId)
            ->orderBy('symbol')
            ->pluck('symbol')
            ->all();

        return [
            'feed_id' => $config->feed_id,
            'enabled' => $config->enabled,
            'interval_sec' => $config->interval_sec,
            'symbols' => $symbols,
        ];
    }

    public function listAll(): array
    {
        return $this->feedConfigRepository->all()
            ->map(fn ($config) => [
                'feed_id' => $config->feed_id,
                'enabled' => $config->enabled,
                'interval_sec' => $config->interval_sec,
            ])
            ->all();
    }

    public function toggle(string $feedId): ?array
    {
        $config = $this->feedConfigRepository->toggleEnabled($feedId);

        if (! $config) {
            return null;
        }

        return [
            'feed_id' => $config->feed_id,
            'enabled' => $config->enabled,
            'interval_sec' => $config->interval_sec,
        ];
    }
}
