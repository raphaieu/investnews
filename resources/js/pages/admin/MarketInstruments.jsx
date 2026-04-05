import { useCallback, useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import usePagination from '../../hooks/usePagination';

const escapeRegExp = (value) => value.replace(/[|\\{}()[\]^$+*?.-]/g, '\\$&');

export default function MarketInstruments() {
    const [feeds, setFeeds] = useState([]);
    const [togglingFeed, setTogglingFeed] = useState(null);

    const {
        items: rows,
        pagination,
        loading,
        page,
        search,
        searchTerm,
        setSearchTerm,
        totalPages,
        handlePageChange,
        refresh,
    } = usePagination('/admin/market-instruments', { perPage: 15 });

    const loadFeeds = useCallback(() => {
        api.get('/admin/feed-configs').then(({ data }) => setFeeds(data));
    }, []);

    useEffect(() => { loadFeeds(); }, [loadFeeds]);

    const handleToggleFeed = async (feedId) => {
        setTogglingFeed(feedId);
        try {
            const { data } = await api.put(`/admin/feed-configs/${feedId}/toggle`);
            setFeeds((prev) =>
                prev.map((f) => (f.feed_id === feedId ? { ...f, enabled: data.enabled } : f))
            );
        } finally {
            setTogglingFeed(null);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Excluir este ativo? O nome voltará ao padrão do sistema, se existir.')) return;
        await api.delete(`/admin/market-instruments/${id}`);
        refresh();
    };

    const searchTokens = useMemo(() =>
        Array.from(
            new Set(
                search
                    .trim()
                    .split(/\s+/)
                    .filter((token) => token.length > 1)
            )
        ),
        [search]
    );

    const highlightText = (text) => {
        if (!text || searchTokens.length === 0) {
            return text;
        }

        const pattern = searchTokens.map((token) => escapeRegExp(token)).join('|');
        const regex = new RegExp(`(${pattern})`, 'gi');
        const parts = text.split(regex);

        return parts.map((part, index) => {
            if (searchTokens.some((token) => token.toLowerCase() === part.toLowerCase())) {
                return (
                    <mark key={`${part}-${index}`} className="i10-highlight">
                        {part}
                    </mark>
                );
            }

            return <span key={`${part}-${index}`}>{part}</span>;
        });
    };

    const clearSearch = () => setSearchTerm('');

    const emptyMessage = search
        ? 'Nenhum ativo encontrado para esta busca. Tente outro símbolo, nome ou menos palavras-chave.'
        : 'Nenhum ativo cadastrado no banco. Use os padrões do sistema ou cadastre aqui para sobrescrever.';

    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-bold">Cotações</h1>
                    <p className="i10-muted text-sm mt-1">
                        Gerencie ativos e controle o envio de cotações pelo MT5.
                    </p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    {feeds.map((feed) => (
                        <button
                            key={feed.feed_id}
                            type="button"
                            disabled={togglingFeed === feed.feed_id}
                            onClick={() => handleToggleFeed(feed.feed_id)}
                            className={`inline-flex items-center gap-2 px-4 py-2 text-sm rounded-md border transition-colors ${
                                feed.enabled
                                    ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700'
                                    : 'bg-(--i10-surface) i10-muted border-(--i10-border) hover:text-(--i10-text)'
                            } disabled:opacity-50`}
                            title={`${feed.enabled ? 'Desligar' : 'Ligar'} feed ${feed.feed_id}`}
                        >
                            <span
                                className={`relative inline-flex h-5 w-9 shrink-0 rounded-full transition-colors ${
                                    feed.enabled ? 'bg-white/30' : 'bg-(--i10-border)'
                                }`}
                            >
                                <span
                                    className={`inline-block h-4 w-4 rounded-full bg-white shadow transform transition-transform mt-0.5 ${
                                        feed.enabled ? 'translate-x-4.5' : 'translate-x-0.5'
                                    }`}
                                />
                            </span>
                            {feed.feed_id}
                        </button>
                    ))}
                    <Link to="/admin/ativos/criar" className="i10-btn-primary px-4 py-2 text-sm">
                        Novo ativo
                    </Link>
                </div>
            </div>

            {search && (
                <div className="flex flex-wrap items-center gap-2 text-sm">
                    <span className="i10-card-soft px-3 py-1.5 inline-flex items-center gap-2">
                        Busca: <strong className="font-semibold">&quot;{search}&quot;</strong>
                        <button
                            type="button"
                            onClick={clearSearch}
                            className="i10-muted hover:text-(--i10-text) transition-colors"
                            aria-label="Limpar busca"
                        >
                            ×
                        </button>
                    </span>
                </div>
            )}

            <div className="i10-card p-4">
                <div className="flex flex-wrap gap-3 items-center">
                    <div className="relative flex-1 min-w-[220px]">
                        <input
                            type="search"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            placeholder="Símbolo ou nome (ex.: XAU, ouro, nasdaq 100)…"
                            className="i10-input w-full pl-3 pr-10 py-2 text-sm"
                            autoComplete="off"
                        />
                        {searchTerm && (
                            <button
                                type="button"
                                onClick={() => {
                                    setSearchTerm('');
                                    clearSearch();
                                }}
                                aria-label="Limpar busca"
                                className="absolute right-3 top-1/2 -translate-y-1/2 i10-muted hover:text-(--i10-text)"
                            >
                                ×
                            </button>
                        )}
                    </div>
                    <span className="text-sm i10-muted">
                        {pagination.total ?? rows.length} registro(s)
                        {search ? ' (filtrado)' : ''}
                    </span>
                </div>
            </div>

            {loading ? (
                <p className="i10-muted">Carregando...</p>
            ) : rows.length === 0 ? (
                <div className="i10-card p-10 text-center i10-muted">{emptyMessage}</div>
            ) : (
                <div className="i10-card overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm min-w-[640px]">
                            <thead className="i10-table-head">
                                <tr>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">Símbolo</th>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">Nome na grade</th>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">Mercado</th>
                                    <th className="text-right px-6 py-3 font-medium i10-muted">Ações</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {rows.map((row) => (
                                    <tr key={row.id}>
                                        <td className="px-6 py-4 font-mono">{highlightText(row.symbol)}</td>
                                        <td className="px-6 py-4">{highlightText(row.display_name)}</td>
                                        <td className="px-6 py-4">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                                                row.feed_id === 'mt5-b3'
                                                    ? 'bg-amber-100 text-amber-800'
                                                    : 'bg-sky-100 text-sky-800'
                                            }`}>
                                                {row.feed_id === 'mt5-b3' ? 'B3' : 'Forex'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="inline-flex items-center gap-3">
                                                <Link
                                                    to={`/admin/ativos/${row.id}/editar`}
                                                    className="i10-link inline-flex items-center gap-1.5 hover:underline"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="h-4 w-4">
                                                        <path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-8.9 8.9a2 2 0 0 1-.878.497l-2.667.667a.75.75 0 0 1-.91-.91l.667-2.667a2 2 0 0 1 .497-.878l8.9-8.9Z" />
                                                    </svg>
                                                    Editar
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(row.id)}
                                                    className="text-(--i10-danger) inline-flex items-center gap-1.5 hover:underline"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="h-4 w-4">
                                                        <path fillRule="evenodd" d="M8.75 2.5A1.75 1.75 0 0 0 7 4.25V5H4.5a.75.75 0 0 0 0 1.5h.568l.8 9.143A2 2 0 0 0 7.86 17.5h4.28a2 2 0 0 0 1.992-1.857l.8-9.143h.568a.75.75 0 0 0 0-1.5H13V4.25A1.75 1.75 0 0 0 11.25 2.5h-2.5ZM11.5 5V4.25a.25.25 0 0 0-.25-.25h-2.5a.25.25 0 0 0-.25.25V5h3Z" clipRule="evenodd" />
                                                    </svg>
                                                    Excluir
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {totalPages > 1 && (
                        <div className="px-4 sm:px-6 py-4 border-t border-(--i10-border) flex flex-wrap items-center justify-between gap-3">
                            <p className="text-sm i10-muted">
                                Página {pagination.current_page} de {totalPages}
                            </p>
                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    disabled={page <= 1}
                                    onClick={() => handlePageChange(page - 1)}
                                    className="px-3 py-1.5 rounded-md text-sm border border-(--i10-border) disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Anterior
                                </button>
                                <button
                                    type="button"
                                    disabled={page >= totalPages}
                                    onClick={() => handlePageChange(page + 1)}
                                    className="px-3 py-1.5 rounded-md text-sm border border-(--i10-border) disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Próxima
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
