<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache store
    |--------------------------------------------------------------------------
    |
    | O NewsCache (cache público de listagem/detalhe) pode usar um store
    | específico para não depender do CACHE_STORE global.
    |
    */
    'store' => env('NEWS_CACHE_STORE', env('CACHE_STORE', 'array')),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */
    'ttl' => (int) env('NEWS_CACHE_TTL', 300),
];

