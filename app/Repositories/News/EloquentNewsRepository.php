<?php

namespace App\Repositories\News;

use App\Models\News;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentNewsRepository implements NewsRepositoryInterface
{
    public function paginatePublished(
        ?string $search,
        ?string $categorySlug,
        int $perPage,
        int $page
    ): LengthAwarePaginator {
        $page = max(1, $page);

        return News::with('category')
            ->published()
            ->search($search)
            ->inCategory($categorySlug)
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findPublishedBySlug(string $slug): News
    {
        return News::with('category')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator
    {
        $search = trim((string) $search);

        return News::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): News
    {
        return News::create($data);
    }

    public function update(News $news, array $data): News
    {
        $news->update($data);

        return $news->refresh();
    }

    public function delete(News $news): void
    {
        $news->delete();
    }
}

