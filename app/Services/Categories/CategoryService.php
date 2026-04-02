<?php

namespace App\Services\Categories;

use App\Models\Category;
use App\Repositories\Categories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function all(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function adminIndex(Request $request): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        return $this->categoryRepository->paginateForAdmin($search, $perPage);
    }

    public function create(array $validated): Category
    {
        return $this->categoryRepository->create($validated);
    }

    public function update(Category $category, array $validated): Category
    {
        return $this->categoryRepository->update($category, $validated);
    }

    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
    }
}
