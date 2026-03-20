<?php

namespace App\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class NewsCache
{
    public function rememberPublicListPayload(
        ?string $category,
        string $search,
        int $page,
        int $perPage,
        Closure $callback
    ): array {
        $store = $this->store();
        $ttl = $this->ttl();

        $categoryToken = $this->categoryToken($category); // null => 'all'
        $searchHash = sha1($search);
        $version = $this->getListVersion($categoryToken);

        $key = $this->listPayloadKey($categoryToken, $version, $searchHash, $page, $perPage);

        /** @var array $payload */
        $payload = Cache::store($store)->remember($key, $ttl, $callback);

        return $payload;
    }

    public function rememberPublicShowPayload(string $slug, Closure $callback): array
    {
        $store = $this->store();
        $ttl = $this->ttl();

        $version = $this->getShowVersion($slug);
        $key = $this->showPayloadKey($slug, $version);

        /** @var array $payload */
        $payload = Cache::store($store)->remember($key, $ttl, $callback);

        return $payload;
    }

    /**
     * Invalida caches de listagem para uma categoria e/ou para o token global "all".
     *
     * Quando $category é null/empty (cache do endpoint sem filtro de categoria),
     * o token "all" é utilizado.
     */
    public function bumpListVersion(?string $category): void
    {
        $categoryToken = $this->categoryToken($category);

        $store = $this->store();
        $versionKey = $this->listVersionKey($categoryToken);
        $version = Cache::store($store)->get($versionKey, 0);
        Cache::store($store)->put($versionKey, $version + 1);
    }

    public function bumpShowVersion(string $slug): void
    {
        $store = $this->store();
        $versionKey = $this->showVersionKey($slug);
        $version = Cache::store($store)->get($versionKey, 0);
        Cache::store($store)->put($versionKey, $version + 1);
    }

    private function store(): string
    {
        // Garantia de testes: nunca depender de Redis fora do Docker.
        if (app()->environment('testing')) {
            return 'array';
        }

        return (string) (config('news.store') ?: config('cache.default', 'array'));
    }

    private function ttl(): int
    {
        return (int) (config('news.ttl', 300));
    }

    private function normalizeCategory(?string $category): ?string
    {
        $category = trim((string) $category);

        return $category !== '' ? $category : null;
    }

    private function categoryToken(?string $category): string
    {
        $category = $this->normalizeCategory($category);

        return $category ?? 'all';
    }

    private function listVersionKey(string $categoryToken): string
    {
        return "news:list:version:{$categoryToken}";
    }

    private function getListVersion(string $categoryToken): int
    {
        $store = $this->store();
        return (int) Cache::store($store)->get($this->listVersionKey($categoryToken), 0);
    }

    private function listPayloadKey(
        string $categoryToken,
        int $version,
        string $searchHash,
        int $page,
        int $perPage
    ): string {
        return "news:list:{$categoryToken}:v{$version}:p{$page}:pp{$perPage}:q{$searchHash}";
    }

    private function showVersionKey(string $slug): string
    {
        return "news:show:version:{$slug}";
    }

    private function getShowVersion(string $slug): int
    {
        $store = $this->store();
        return (int) Cache::store($store)->get($this->showVersionKey($slug), 0);
    }

    private function showPayloadKey(string $slug, int $version): string
    {
        return "news:show:{$slug}:v{$version}";
    }
}

