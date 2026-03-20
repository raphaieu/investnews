<?php

namespace App\Services\News;

use App\Cache\NewsCache;
use App\Models\News;
use App\Repositories\News\NewsRepositoryInterface;
use App\Http\Resources\NewsResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsService
{
    public function __construct(
        private readonly NewsRepositoryInterface $newsRepository,
        private readonly NewsCache $newsCache
    ) {
    }

    public function publicIndexPayload(
        ?string $search,
        ?string $categorySlug,
        int $page,
        int $perPage
    ): array {
        $search = $this->normalizeSearch($search);
        $page = max(1, $page);
        $perPage = max(5, min($perPage, 50));

        return $this->newsCache->rememberPublicListPayload(
            $categorySlug,
            $search,
            $page,
            $perPage,
            function () use ($search, $categorySlug, $page, $perPage) {
                $paginator = $this->newsRepository->paginatePublished($search, $categorySlug, $perPage, $page);

                return NewsResource::collection($paginator)->response()->getData(true);
            }
        );
    }

    public function publicShowPayload(string $slug): array
    {
        return $this->newsCache->rememberPublicShowPayload($slug, function () use ($slug) {
            $news = $this->newsRepository->findPublishedBySlug($slug);

            return (new NewsResource($news))->response()->getData(true);
        });
    }

    public function adminIndex(Request $request): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        return $this->newsRepository->paginateForAdmin($search, $perPage);
    }

    public function adminCreate(array $validated): News
    {
        return $this->newsRepository->create($validated);
    }

    public function adminUpdate(News $news, array $validated): News
    {
        return $this->newsRepository->update($news, $validated);
    }

    public function adminDelete(News $news): void
    {
        $this->newsRepository->delete($news);
    }

    private function normalizeSearch(?string $search): string
    {
        $search = trim((string) $search);

        if ($search === '') {
            return '';
        }

        $search = preg_replace('/\s+/', ' ', $search) ?: '';

        return $search;
    }
}

