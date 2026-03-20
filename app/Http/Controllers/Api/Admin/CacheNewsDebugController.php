<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheNewsDebugController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Segurança: rota existe só para facilitar desenvolvimento/admin.
        // (Mesmo assim, também limitamos o padrão para chaves "news:*".)

        $pattern = (string) $request->query('pattern', 'news:*');
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 200));

        $contentLimit = (int) $request->query('content_limit', 240);
        $contentLimit = max(0, min($contentLimit, 2000));

        $includeVersions = filter_var(
            $request->query('include_versions', true),
            FILTER_VALIDATE_BOOLEAN
        );

        // Evita varrer o Redis inteiro.
        if (! str_starts_with($pattern, 'news:')) {
            return response()->json([
                'error' => 'pattern inválido (use news:*)',
            ], 400);
        }

        $store = (string) (config('news.store') ?: 'array');

        if ($store !== 'redis') {
            return response()->json([
                'store' => $store,
                'warning' => 'NewsCache não está usando Redis neste ambiente (sem listar chaves).',
            ]);
        }

        $cachePrefix = (string) (config('cache.prefix') ?: '');
        $redisClientPrefix = (string) (config('database.redis.options.prefix') ?: '');
        $fullPrefix = $redisClientPrefix.$cachePrefix;

        $redisPattern = $fullPrefix.$pattern;
        $keys = Redis::connection('cache')
            ->command('keys', [$redisPattern]);

        // Fallback: se por algum motivo o prefixo calculado não bater com o Redis,
        // tenta um match mais genérico para ainda permitir inspeção manual.
        $fallbackPattern = '*'.$pattern;
        $usedFallback = false;
        if ((is_array($keys) && count($keys) === 0) || (! is_array($keys) && empty($keys))) {
            $keys = Redis::connection('cache')
                ->command('keys', [$fallbackPattern]);
            $usedFallback = true;
        }

        $keys = is_array($keys) ? $keys : iterator_to_array($keys);
        $keys = array_slice($keys, 0, $limit);

        // Desprefixa para retornar também a "chave lógica" que o Cache::get espera.
        $logicalKeys = array_map(function (string $redisKey) use ($fullPrefix, $cachePrefix) {
            if ($fullPrefix !== '' && str_starts_with($redisKey, $fullPrefix)) {
                return substr($redisKey, strlen($fullPrefix));
            }

            // Fallback: alguns setups podem só aplicar o cache prefix.
            if ($cachePrefix !== '' && str_starts_with($redisKey, $cachePrefix)) {
                return substr($redisKey, strlen($cachePrefix));
            }

            return $redisKey;
        }, $keys);

        $items = [];
        foreach ($logicalKeys as $logicalKey) {
            $items[$logicalKey] = Cache::store($store)->get($logicalKey);

            if ($contentLimit > 0 && is_array($items[$logicalKey])) {
                $items[$logicalKey] = $this->truncateContentStrings($items[$logicalKey], $contentLimit);
            }
        }

        $versions = null;
        if ($includeVersions) {
            $versions = [
                'list' => [],
                'show' => [],
            ];

            foreach ($logicalKeys as $logicalKey) {
                if (str_starts_with($logicalKey, 'news:list:version:')) {
                    $versions['list'][$logicalKey] = $items[$logicalKey] ?? null;
                }

                if (str_starts_with($logicalKey, 'news:show:version:')) {
                    $versions['show'][$logicalKey] = $items[$logicalKey] ?? null;
                }
            }
        }

        return response()->json([
            'store' => $store,
            'redisConnection' => 'cache',
            'cachePrefix' => $cachePrefix,
            'redisClientPrefix' => $redisClientPrefix,
            'fullPrefix' => $fullPrefix,
            'redisPattern' => $redisPattern,
            'fallbackPattern' => $fallbackPattern,
            'usedFallback' => $usedFallback,
            'requestedPattern' => $pattern,
            'contentLimit' => $contentLimit,
            'foundKeys' => $logicalKeys,
            'versions' => $versions,
            'items' => $items,
        ]);
    }

    private function truncateContentStrings(array $value, int $limit): array
    {
        foreach ($value as $key => $item) {
            if ($key === 'content' && is_string($item)) {
                $value[$key] = $this->truncateString($item, $limit);
                continue;
            }

            if (is_array($item)) {
                $value[$key] = $this->truncateContentStrings($item, $limit);
            }
        }

        return $value;
    }

    private function truncateString(string $value, int $limit): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit).'... (truncado)';
    }
}

