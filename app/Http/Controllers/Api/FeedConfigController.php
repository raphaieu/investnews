<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FeedConfigs\FeedConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedConfigController extends Controller
{
    public function __construct(private readonly FeedConfigService $feedConfigService) {}

    /**
     * Public feed status — no auth required.
     * Returns enabled/disabled state of all feeds for the public widget.
     */
    public function status(): JsonResponse
    {
        $feeds = $this->feedConfigService->listAll();

        return response()->json([
            'ok' => true,
            'feeds' => collect($feeds)->keyBy('feed_id')->map(fn ($f) => $f['enabled']),
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        if ($request->bearerToken() !== config('services.market.key')) {
            return response()->json(['ok' => false], 401);
        }

        $feedId = trim((string) $request->query('feed_id', ''));

        if ($feedId === '') {
            return response()->json(['ok' => false, 'error' => 'feed_id is required'], 422);
        }

        $payload = $this->feedConfigService->getConfigPayload($feedId);

        if (! $payload) {
            return response()->json(['ok' => false, 'error' => 'feed_id not found'], 404);
        }

        return response()->json($payload);
    }
}
