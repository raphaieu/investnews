<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicNewsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $news = News::with('category')
            ->published()
            ->search($request->query('search'))
            ->inCategory($request->query('category'))
            ->orderByDesc('published_at')
            ->paginate(12);

        return NewsResource::collection($news);
    }

    public function show(string $slug): NewsResource
    {
        $news = News::with('category')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return new NewsResource($news);
    }
}
