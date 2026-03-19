<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicCategoryController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            Category::orderBy('name')->get()
        );
    }
}
