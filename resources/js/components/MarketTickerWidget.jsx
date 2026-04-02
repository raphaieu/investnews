import { useState, useEffect, useMemo } from 'react'
import { usePublicChannel } from '../hooks/useEcho'
import api from '../services/api'

/** Formato estilo US (milhares com virgula) — indices amplamente cotados assim. */
const US_STYLE_SYMBOLS = new Set(['US30', 'US100', 'US500', 'DXY', 'GOLD', 'SILVER', 'BTCUSD'])

function formatPrice(value, symbol) {
    const num = parseFloat(value) || 0
    if (US_STYLE_SYMBOLS.has(symbol)) {
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    }
    return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatPercent(value) {
    const num = parseFloat(value) || 0
    const sign = num >= 0 ? '+' : ''
    return `${sign}${num.toFixed(2)}%`
}

function QuoteRow(tick) {
    const positive = parseFloat(tick.variationPercent) >= 0
    const name = tick.display_name || tick.symbol

    return (
        <div className="flex items-center justify-between py-2 px-3 rounded-lg bg-white border border-gray-100 hover:border-gray-200 transition-colors">
            <div className="flex flex-col">
                <span className="text-xs font-semibold text-gray-500 uppercase">{tick.symbol}</span>
                <span className="text-xs text-gray-400">{name}</span>
            </div>
            <div className="flex flex-col items-end">
                <span className="text-sm font-bold text-gray-800">{formatPrice(tick.last, tick.symbol)}</span>
                <span className={`text-xs font-medium ${positive ? 'text-green-600' : 'text-red-500'}`}>
                    {formatPercent(tick.variationPercent)}
                </span>
            </div>
        </div>
    )
}

export default function MarketTickerWidget({ symbols = [] }) {
    const [ticks, setTicks] = useState({})
    const [filterText, setFilterText] = useState('')
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
                        display_name: q.display_name,
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
                    display_name:
                        prev[data.symbol]?.display_name ??
                        data.symbol,
                },
            }))
        })

        return () => channel.stopListening('.market.tick')
    }, [channel, symbols])

    const displayed = useMemo(() => {
        if (symbols.length > 0) {
            return symbols.map(s => ticks[s]).filter(Boolean)
        }
        return Object.values(ticks)
    }, [ticks, symbols])

    const filterQuery = filterText.trim().toLowerCase()

    const filteredDisplayed = useMemo(() => {
        if (!filterQuery) return displayed
        return displayed.filter(tick => {
            const name = tick.display_name || tick.symbol
            return (
                tick.symbol.toLowerCase().includes(filterQuery) ||
                String(name).toLowerCase().includes(filterQuery)
            )
        })
    }, [displayed, filterQuery])

    return (
        <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div className="flex items-center justify-between mb-3">
                <h3 className="text-sm font-semibold text-gray-700">Grade de Cotações</h3>
                <span className={`inline-block w-2 h-2 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-400'}`} />
            </div>

            <div className="mb-3">
                <label htmlFor="market-ticker-filter" className="sr-only">
                    Filtrar cotações
                </label>
                <input
                    id="market-ticker-filter"
                    type="search"
                    value={filterText}
                    onChange={e => setFilterText(e.target.value)}
                    placeholder="Filtrar por símbolo ou nome…"
                    className="w-full text-xs rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:border-gray-300"
                    autoComplete="off"
                />
            </div>

            {!isConnected && (
                <p className="text-xs text-gray-400 text-center py-4">Conectando...</p>
            )}

            {isConnected && displayed.length === 0 && (
                <p className="text-xs text-gray-400 text-center py-4">Aguardando dados do mercado...</p>
            )}

            {isConnected && displayed.length > 0 && filteredDisplayed.length === 0 && (
                <p className="text-xs text-gray-400 text-center py-4">
                    Nenhuma cotação corresponde ao filtro.
                </p>
            )}

            {isConnected && filteredDisplayed.length > 0 && (
                <div className="space-y-2">
                    {filteredDisplayed.map(tick => (
                        <QuoteRow key={tick.symbol} {...tick} />
                    ))}
                </div>
            )}
        </div>
    )
}
