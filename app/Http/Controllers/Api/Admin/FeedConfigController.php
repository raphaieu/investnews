<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\FeedConfigs\FeedConfigService;
use Illuminate\Http\JsonResponse;

class FeedConfigController extends Controller
{
    public function __construct(private readonly FeedConfigService $feedConfigService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->feedConfigService->listAll());
    }

    public function toggle(string $feedId): JsonResponse
    {
        $result = $this->feedConfigService->toggle($feedId);

        if (! $result) {
            return response()->json(['error' => 'Feed not found'], 404);
        }

        return response()->json($result);
    }
}
