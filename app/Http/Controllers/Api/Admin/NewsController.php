<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(5, min($perPage, 50));

        return NewsResource::collection(
            News::with('category')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%");
                })
                ->orderByDesc('created_at')
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function store(StoreNewsRequest $request): JsonResponse
    {
        $news = News::create($request->validated());

        return (new NewsResource($news->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(News $news): NewsResource
    {
        return new NewsResource($news->load('category'));
    }

    public function update(UpdateNewsRequest $request, News $news): NewsResource
    {
        $news->update($request->validated());

        return new NewsResource($news->load('category'));
    }

    public function destroy(News $news): JsonResponse
    {
        $news->delete();

        return response()->json(null, 204);
    }
}
