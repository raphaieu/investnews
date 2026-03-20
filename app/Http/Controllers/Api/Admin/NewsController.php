<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\News;
use App\Services\News\NewsService;

class NewsController extends Controller
{
    public function __construct(private readonly NewsService $newsService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->newsService->adminIndex($request);

        return NewsResource::collection($paginator);
    }

    public function store(StoreNewsRequest $request): JsonResponse
    {
        $news = $this->newsService->adminCreate($request->validated());

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
        $news = $this->newsService->adminUpdate($news, $request->validated());

        return new NewsResource($news->load('category'));
    }

    public function destroy(News $news): JsonResponse
    {
        $this->newsService->adminDelete($news);

        return response()->json(null, 204);
    }
}
