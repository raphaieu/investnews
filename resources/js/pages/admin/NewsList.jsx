import { useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import api from '../../services/api';
import { formatApiDatePtBr } from '../../utils/date';

export default function NewsList() {
    const [news, setNews] = useState([]);
    const [pagination, setPagination] = useState({});
    const [loading, setLoading] = useState(true);
    const [searchParams, setSearchParams] = useSearchParams();

    const page = parseInt(searchParams.get('page') || '1', 10);
    const search = searchParams.get('search') || '';
    const [searchTerm, setSearchTerm] = useState(search);

    useEffect(() => {
        setSearchTerm(search);
    }, [search]);

    useEffect(() => {
        const timer = window.setTimeout(() => {
            const normalized = searchTerm.trim();
            if (normalized === search) return;

            const params = new URLSearchParams(searchParams);
            if (normalized) params.set('search', normalized);
            else params.delete('search');
            params.delete('page');
            setSearchParams(params, { replace: true });
        }, 300);

        return () => window.clearTimeout(timer);
    }, [searchTerm, search, searchParams, setSearchParams]);

    const fetchNews = () => {
        setLoading(true);
        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', '10');
        if (search) params.set('search', search);

        api.get(`/admin/news?${params.toString()}`)
            .then(({ data }) => {
                setNews(data.data || []);
                setPagination(data.meta || {});
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchNews();
    }, [page, search]);

    const totalPages = useMemo(() => pagination.last_page || 1, [pagination.last_page]);

    const handlePageChange = (targetPage) => {
        const params = new URLSearchParams(searchParams);
        params.set('page', String(targetPage));
        setSearchParams(params);
    };

    const handleDelete = async (id) => {
        if (!confirm('Tem certeza que deseja excluir esta notícia?')) return;
        await api.delete(`/admin/news/${id}`);
        fetchNews();
    };

    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-bold">Notícias</h1>
                    <p className="i10-muted text-sm mt-1">Gerencie notícias com busca e paginação.</p>
                </div>
                <Link to="/admin/noticias/criar" className="i10-btn-primary px-4 py-2 text-sm">
                    Nova notícia
                </Link>
            </div>

            <div className="i10-card p-4">
                <div className="flex flex-wrap gap-3 items-center">
                    <div className="relative flex-1 min-w-[220px]">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            placeholder="Filtrar por título..."
                            className="i10-input w-full pl-3 pr-10 py-2 text-sm"
                        />
                        {searchTerm && (
                            <button
                                type="button"
                                onClick={() => setSearchTerm('')}
                                aria-label="Limpar busca"
                                className="absolute right-3 top-1/2 -translate-y-1/2 i10-muted hover:text-(--i10-text)"
                            >
                                x
                            </button>
                        )}
                    </div>
                    <span className="text-sm i10-muted">
                        {pagination.total || news.length} registro(s)
                    </span>
                </div>
            </div>

            {loading ? (
                <p className="i10-muted">Carregando...</p>
            ) : news.length === 0 ? (
                <div className="i10-card p-10 text-center i10-muted">Nenhuma notícia cadastrada.</div>
            ) : (
                <div className="i10-card overflow-hidden">
                    <div className="overflow-x-auto">
                    <table className="w-full text-sm min-w-[760px]">
                        <thead className="i10-table-head">
                            <tr>
                                <th className="text-left px-6 py-3 font-medium i10-muted">Título</th>
                                <th className="text-left px-6 py-3 font-medium i10-muted">Categoria</th>
                                <th className="text-left px-6 py-3 font-medium i10-muted">Publicação</th>
                                <th className="text-right px-6 py-3 font-medium i10-muted">Ações</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {news.map((item) => (
                                <tr key={item.id}>
                                    <td className="px-6 py-4">{item.title}</td>
                                    <td className="px-6 py-4 i10-muted">{item.category?.name || '-'}</td>
                                    <td className="px-6 py-4 i10-muted">
                                        {item.published_at
                                            ? formatApiDatePtBr(item.published_at)
                                            : 'Rascunho'}
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <div className="inline-flex items-center gap-3">
                                        <Link to={`/admin/noticias/${item.id}/editar`} className="i10-link inline-flex items-center gap-1.5 hover:underline">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" className="h-4 w-4">
                                                <path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-8.9 8.9a2 2 0 0 1-.878.497l-2.667.667a.75.75 0 0 1-.91-.91l.667-2.667a2 2 0 0 1 .497-.878l8.9-8.9Z" />
                                            </svg>
                                            Editar
                                        </Link>
                                        <button onClick={() => handleDelete(item.id)} className="text-(--i10-danger) inline-flex items-center gap-1.5 hover:underline">
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
