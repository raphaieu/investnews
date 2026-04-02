<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\Categories\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicCategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function __invoke(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            $this->categoryService->all()
        );
    }
}
