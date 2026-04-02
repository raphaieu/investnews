<?php

namespace App\Http\Controllers\Api;

use App\Events\MarketTickReceived;
use App\Http\Controllers\Controller;
use App\Models\MarketInstrument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class MarketIngestController extends Controller
{
    public function snapshot(Request $request): JsonResponse
    {
        if ($request->bearerToken() !== config('services.market.key')) {
            return response()->json(['ok' => false], 401);
        }

        $jsonData = $request->json()->all();

        if (empty($jsonData)) {
            $rawBody = $request->getContent();
            $jsonData = $this->parseRawBody($rawBody, $request->all());
        }

        $feedId = $jsonData['feed_id'] ?? null;
        $market = $jsonData['market'] ?? null;
        $seq = $jsonData['seq'] ?? null;
        $ticks = $jsonData['ticks'] ?? [];

        $processedCount = 0;

        foreach ($ticks as $tick) {
            $symbol = strtoupper($tick['symbol'] ?? '');
            if ($symbol === '') {
                continue;
            }

            $last = $tick['last'] ?? 0;
            $prevClose = $tick['prev_close'] ?? 0;
            $variation = $last - $prevClose;
            $variationPercent = $prevClose > 0 ? ($variation / $prevClose) * 100 : 0;

            $tickData = [
                'symbol' => $symbol,
                'last' => $last,
                'bid' => $tick['bid'] ?? 0,
                'ask' => $tick['ask'] ?? 0,
                'prev_close' => $prevClose,
                'variation' => $variation,
                'variationPercent' => $variationPercent,
                'ts' => $tick['ts'] ?? time() * 1000,
                'feed_id' => $feedId,
                'market' => $market,
                'seq' => $seq,
            ];

            Redis::setex("ticks:{$symbol}", 10, json_encode($tickData));
            Redis::sadd('market:symbols', $symbol);

            event(new MarketTickReceived($tickData, $symbol));

            $processedCount++;
        }

        return response()->json([
            'ok' => true,
            'feed_id' => $feedId,
            'market' => $market,
            'seq' => $seq,
            'processedCount' => $processedCount,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function quotes(Request $request): JsonResponse
    {
        $symbols = $request->query('symbols');
        $symbolList = $symbols
            ? array_values(array_filter(array_map('strtoupper', explode(',', $symbols))))
            : Redis::smembers('market:symbols');

        $nameMap = MarketInstrument::resolvedNameMap();

        $quotes = [];
        foreach ($symbolList as $symbol) {
            $raw = Redis::get("ticks:{$symbol}");
            if (! $raw) {
                continue;
            }
            $data = json_decode($raw, true);
            if ($data && isset($data['symbol'])) {
                $sym = $data['symbol'];
                $data['display_name'] = $nameMap[$sym] ?? $sym;
                $quotes[$sym] = $data;
            }
        }

        return response()->json(['ok' => true, 'quotes' => $quotes]);
    }

    public function health(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    private function parseRawBody(string $rawBody, array $requestAll): array
    {
        $candidates = [
            $rawBody,
            urldecode($rawBody),
        ];

        foreach ($requestAll as $v) {
            if (is_string($v)) {
                $candidates[] = $v;
            }
        }

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || $candidate === '') {
                continue;
            }

            $candidate = rtrim($candidate, "\0");

            // Tentativa direta
            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Aspas simples → duplas (MQL5 pode enviar assim)
            $decoded = json_decode(str_replace("'", '"', $candidate), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Strip slashes
            $decoded = json_decode(stripslashes($candidate), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // JSON duplamente serializado
            $firstDecode = json_decode($candidate, true);
            if (is_string($firstDecode)) {
                $decoded = json_decode($firstDecode, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return [];
    }
}
