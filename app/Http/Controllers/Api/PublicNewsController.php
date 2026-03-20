<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\News\NewsService;
use Illuminate\Http\Request;

class PublicNewsController extends Controller
{
    public function __construct(private readonly NewsService $newsService)
    {
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $categorySlug = $request->query('category');

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 12);

        $payload = $this->newsService->publicIndexPayload($search, $categorySlug, $page, $perPage);

        return response()->json($payload);
    }

    public function show(string $slug)
    {
        $payload = $this->newsService->publicShowPayload($slug);

        return response()->json($payload);
    }
}
