<?php

namespace App\Repositories\News;

use App\Models\News;
use Illuminate\Pagination\LengthAwarePaginator;

interface NewsRepositoryInterface
{
    public function paginatePublished(
        ?string $search,
        ?string $categorySlug,
        int $perPage,
        int $page
    ): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): News;

    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator;

    public function create(array $data): News;

    public function update(News $news, array $data): News;

    public function delete(News $news): void;
}

