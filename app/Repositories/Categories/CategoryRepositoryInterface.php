<?php

namespace App\Repositories\Categories;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function all(): \Illuminate\Database\Eloquent\Collection;

    public function paginateForAdmin(?string $search, int $perPage): LengthAwarePaginator;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;
}
