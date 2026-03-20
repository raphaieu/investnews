<?php

namespace App\Observers;

use App\Cache\NewsCache;
use App\Models\Category;
use App\Models\News;

class NewsObserver
{
    public function __construct(private readonly NewsCache $newsCache)
    {
    }

    public function created(News $news): void
    {
        $categorySlug = $this->resolveCategorySlug($news->category_id);

        $this->newsCache->bumpListVersion($categorySlug);
        // Também invalida a listagem "sem filtro" (endpoint /api/news com category ausente).
        $this->newsCache->bumpListVersion(null);
        $this->newsCache->bumpShowVersion($news->slug);
    }

    public function updated(News $news): void
    {
        $oldSlug = (string) $news->getOriginal('slug');
        $newSlug = (string) $news->slug;

        if ($oldSlug !== $newSlug) {
            $this->newsCache->bumpShowVersion($oldSlug);
        }

        $this->newsCache->bumpShowVersion($newSlug);

        $oldCategorySlug = $this->resolveCategorySlug($news->getOriginal('category_id'));
        $newCategorySlug = $this->resolveCategorySlug($news->category_id);

        if ($oldCategorySlug !== $newCategorySlug) {
            $this->newsCache->bumpListVersion($oldCategorySlug);
        }

        $this->newsCache->bumpListVersion($newCategorySlug);
        // Também invalida a listagem "sem filtro" (categoria ausente).
        $this->newsCache->bumpListVersion(null);
    }

    public function deleted(News $news): void
    {
        $categorySlug = $this->resolveCategorySlug($news->category_id);

        $this->newsCache->bumpListVersion($categorySlug);
        // Também invalida a listagem "sem filtro" (categoria ausente).
        $this->newsCache->bumpListVersion(null);
        $this->newsCache->bumpShowVersion($news->slug);
    }

    private function resolveCategorySlug(?int $categoryId): ?string
    {
        if ($categoryId === null) {
            return null;
        }

        return Category::query()->whereKey($categoryId)->value('slug');
    }
}

