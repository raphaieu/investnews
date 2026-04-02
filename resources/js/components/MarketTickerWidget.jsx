import { useState, useEffect } from 'react'
import { usePublicChannel } from '../hooks/useEcho'
import api from '../services/api'

const SYMBOL_NAMES = {
    WIN: 'Mini Índice',
    WDO: 'Mini Dólar',
    PETR4: 'Petrobras',
    VALE3: 'Vale',
    ITUB4: 'Itaú',
    BBDC4: 'Bradesco',
    BBAS3: 'Banco do Brasil',
    WEGE3: 'WEG',
    US30: 'Dow Jones',
    US100: 'Nasdaq 100',
    US500: 'S&P 500',
    DXY: 'Dólar Index',
    GOLD: 'Ouro',
    SILVER: 'Prata',
    BTCUSD: 'Bitcoin',
}

function formatPrice(value, symbol) {
    const num = parseFloat(value) || 0
    if (['US30', 'US100', 'US500', 'DXY', 'GOLD', 'SILVER', 'BTCUSD'].includes(symbol)) {
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    }
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatPercent(value) {
    const num = parseFloat(value) || 0
    const sign = num >= 0 ? '+' : ''
    return `${sign}${num.toFixed(2)}%`
}

function QuoteRow({ symbol, last, variation, variationPercent }) {
    const positive = parseFloat(variationPercent) >= 0
    const name = SYMBOL_NAMES[symbol] || symbol

    return (
        <div className="flex items-center justify-between py-2 px-3 rounded-lg bg-white border border-gray-100 hover:border-gray-200 transition-colors">
            <div className="flex flex-col">
                <span className="text-xs font-semibold text-gray-500 uppercase">{symbol}</span>
                <span className="text-xs text-gray-400">{name}</span>
            </div>
            <div className="flex flex-col items-end">
                <span className="text-sm font-bold text-gray-800">{formatPrice(last, symbol)}</span>
                <span className={`text-xs font-medium ${positive ? 'text-green-600' : 'text-red-500'}`}>
                    {formatPercent(variationPercent)}
                </span>
            </div>
        </div>
    )
}

export default function MarketTickerWidget({ symbols = [] }) {
    const [ticks, setTicks] = useState({})
    const { channel, isConnected } = usePublicChannel('market-ticks')

    useEffect(() => {
        const params = symbols.length ? `?symbols=${symbols.join(',')}` : ''
        api.get(`/market/quotes${params}`)
            .then(({ data }) => {
                if (!data.ok) return
                const initial = {}
                Object.values(data.quotes || {}).forEach(q => {
                    initial[q.symbol] = {
                        symbol: q.symbol,
                        last: q.last,
                        variation: q.variation,
                        variationPercent: q.variationPercent,
                    }
                })
                setTicks(initial)
            })
            .catch(() => {})
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        if (!channel) return

        channel.listen('.market.tick', (data) => {
            if (!data.symbol || !data.tick) return
            if (symbols.length > 0 && !symbols.includes(data.symbol)) return

            setTicks(prev => ({
                ...prev,
                [data.symbol]: {
                    symbol: data.symbol,
                    last: data.tick.last,
                    variation: data.tick.variation,
                    variationPercent: data.tick.variationPercent,
                },
            }))
        })

        return () => channel.stopListening('.market.tick')
    }, [channel, symbols])

    const displayed = symbols.length > 0
        ? symbols.map(s => ticks[s]).filter(Boolean)
        : Object.values(ticks)

    return (
        <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div className="flex items-center justify-between mb-3">
                <h3 className="text-sm font-semibold text-gray-700">Grade de Cotações</h3>
                <span className={`inline-block w-2 h-2 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-400'}`} />
            </div>

            {!isConnected && (
                <p className="text-xs text-gray-400 text-center py-4">Conectando...</p>
            )}

            {isConnected && displayed.length === 0 && (
                <p className="text-xs text-gray-400 text-center py-4">Aguardando dados do mercado...</p>
            )}

            {displayed.length > 0 && (
                <div className="space-y-2">
                    {displayed.map(tick => (
                        <QuoteRow key={tick.symbol} {...tick} />
                    ))}
                </div>
            )}
        </div>
    )
}
