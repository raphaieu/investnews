<?php

/**
 * Nomes padrão por símbolo (MT5 / feed). Registros na tabela market_instruments
 * sobrescrevem estes valores. Use o admin ou o seeder para popular o banco.
 *
 * Símbolos com # são comuns em CFDs de ações no MT5.
 */
return [
    'defaults' => [
        // Commodities / metais
        'XBRUSD' => 'Petróleo Brent',
        'XAUUSD' => 'Ouro',
        'XAGUSD' => 'Prata',
        'XTIUSD' => 'Petróleo WTI',
        'XNGUSD' => 'Gás natural',
        // Dólar (USDOLLAR cobre variações como "UsDollar" após strtoupper no ingest)
        'USDOLLAR' => 'Índice do dólar (DXY)',
        'DXY' => 'Índice do dólar (DXY)',
        // Índices EUA
        'US30' => 'Dow Jones',
        'US100' => 'Nasdaq 100',
        'US500' => 'S&P 500',
        // Ações / CFDs (lista padrão solicitada)
        'TESLA' => 'Tesla',
        'TSLA' => 'Tesla',
        'SBUX' => 'Starbucks',
        'PYPL' => 'PayPal',
        'NVIDIA' => 'Nvidia',
        'NETFLIX' => 'Netflix',
        'MICROSOFT' => 'Microsoft',
        'INTEL' => 'Intel',
        'GOOGLE' => 'Google',
        'FACEBOOK' => 'Meta (Facebook)',
        'DISNEY' => 'Disney',
        'COIN' => 'Coinbase',
        'AMD' => 'AMD',
        'AMAZON' => 'Amazon',
        'ALIBABA' => 'Alibaba',
        'APPLE' => 'Apple',
        '#UBER' => 'Uber',
        '#SHOP' => 'Shopify',
        '#META' => 'Meta',
        '#ADBE' => 'Adobe',
        // Cripto
        'BTCUSD' => 'Bitcoin',
        'ETHUSD' => 'Ethereum',
        'DOGUSD' => 'Dogecoin',
        'DOGEUSD' => 'Dogecoin',
        // Brasil (feed B3)
        'WIN' => 'Mini índice Bovespa',
        'WDO' => 'Mini dólar',
        'PETR4' => 'Petrobras',
        'VALE3' => 'Vale',
        'ITUB4' => 'Itaú',
        'BBDC4' => 'Bradesco',
        'BBAS3' => 'Banco do Brasil',
        'WEGE3' => 'WEG',
        'GOLD' => 'Ouro',
        'SILVER' => 'Prata',
    ],
];
