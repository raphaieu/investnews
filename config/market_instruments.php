<?php

/**
 * Nomes padrão por símbolo (MT5 / feed). Registros na tabela market_instruments
 * sobrescrevem estes valores. Use o admin ou o seeder para popular o banco.
 *
 * Símbolos com # no MT5 são armazenados sem o # (ex.: #UBER → UBER)
 * para evitar conflitos com nomes de channels do broadcasting.
 */
return [
    'defaults' => [
        // Commodities / metais
        'XBRUSD' => ['display_name' => 'Petróleo Brent', 'feed_id' => 'mt5-forex'],
        'XAUUSD' => ['display_name' => 'Ouro', 'feed_id' => 'mt5-forex'],
        'XAGUSD' => ['display_name' => 'Prata', 'feed_id' => 'mt5-forex'],
        'XTIUSD' => ['display_name' => 'Petróleo WTI', 'feed_id' => 'mt5-forex'],
        'XNGUSD' => ['display_name' => 'Gás natural', 'feed_id' => 'mt5-forex'],
        // Dólar
        'USDOLLAR' => ['display_name' => 'Índice do dólar (DXY)', 'feed_id' => 'mt5-forex'],
        'DXY' => ['display_name' => 'Índice do dólar (DXY)', 'feed_id' => 'mt5-forex'],
        // Índices EUA
        'US30' => ['display_name' => 'Dow Jones', 'feed_id' => 'mt5-forex'],
        'US100' => ['display_name' => 'Nasdaq 100', 'feed_id' => 'mt5-forex'],
        'US500' => ['display_name' => 'S&P 500', 'feed_id' => 'mt5-forex'],
        // Ações / CFDs
        'TESLA' => ['display_name' => 'Tesla', 'feed_id' => 'mt5-forex'],
        'TSLA' => ['display_name' => 'Tesla', 'feed_id' => 'mt5-forex'],
        'SBUX' => ['display_name' => 'Starbucks', 'feed_id' => 'mt5-forex'],
        'PYPL' => ['display_name' => 'PayPal', 'feed_id' => 'mt5-forex'],
        'NVIDIA' => ['display_name' => 'Nvidia', 'feed_id' => 'mt5-forex'],
        'NETFLIX' => ['display_name' => 'Netflix', 'feed_id' => 'mt5-forex'],
        'MICROSOFT' => ['display_name' => 'Microsoft', 'feed_id' => 'mt5-forex'],
        'INTEL' => ['display_name' => 'Intel', 'feed_id' => 'mt5-forex'],
        'GOOGLE' => ['display_name' => 'Google', 'feed_id' => 'mt5-forex'],
        'FACEBOOK' => ['display_name' => 'Meta (Facebook)', 'feed_id' => 'mt5-forex'],
        'DISNEY' => ['display_name' => 'Disney', 'feed_id' => 'mt5-forex'],
        'COIN' => ['display_name' => 'Coinbase', 'feed_id' => 'mt5-forex'],
        'AMD' => ['display_name' => 'AMD', 'feed_id' => 'mt5-forex'],
        'AMAZON' => ['display_name' => 'Amazon', 'feed_id' => 'mt5-forex'],
        'ALIBABA' => ['display_name' => 'Alibaba', 'feed_id' => 'mt5-forex'],
        'APPLE' => ['display_name' => 'Apple', 'feed_id' => 'mt5-forex'],
        'UBER' => ['display_name' => 'Uber', 'feed_id' => 'mt5-forex'],
        'SHOP' => ['display_name' => 'Shopify', 'feed_id' => 'mt5-forex'],
        'META' => ['display_name' => 'Meta', 'feed_id' => 'mt5-forex'],
        'ADBE' => ['display_name' => 'Adobe', 'feed_id' => 'mt5-forex'],
        // Cripto
        'BTCUSD' => ['display_name' => 'Bitcoin', 'feed_id' => 'mt5-forex'],
        'ETHUSD' => ['display_name' => 'Ethereum', 'feed_id' => 'mt5-forex'],
        'DOGUSD' => ['display_name' => 'Dogecoin', 'feed_id' => 'mt5-forex'],
        'DOGEUSD' => ['display_name' => 'Dogecoin', 'feed_id' => 'mt5-forex'],
        // Metais genéricos
        'GOLD' => ['display_name' => 'Ouro', 'feed_id' => 'mt5-forex'],
        'SILVER' => ['display_name' => 'Prata', 'feed_id' => 'mt5-forex'],
        // Brasil (feed B3)
        'WIN' => ['display_name' => 'Mini índice Bovespa', 'feed_id' => 'mt5-b3'],
        'WDO' => ['display_name' => 'Mini dólar', 'feed_id' => 'mt5-b3'],
        'PETR4' => ['display_name' => 'Petrobras', 'feed_id' => 'mt5-b3'],
        'VALE3' => ['display_name' => 'Vale', 'feed_id' => 'mt5-b3'],
        'ITUB4' => ['display_name' => 'Itaú', 'feed_id' => 'mt5-b3'],
        'BBDC4' => ['display_name' => 'Bradesco', 'feed_id' => 'mt5-b3'],
        'BBAS3' => ['display_name' => 'Banco do Brasil', 'feed_id' => 'mt5-b3'],
        'WEGE3' => ['display_name' => 'WEG', 'feed_id' => 'mt5-b3'],
    ],
];
