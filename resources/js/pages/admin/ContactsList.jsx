import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '../../services/api';
import { formatApiDatePtBr } from '../../utils/date';

const messagePreview = (text, max = 120) => {
    if (!text || typeof text !== 'string') return '-';
    const trimmed = text.replace(/\s+/g, ' ').trim();
    if (trimmed.length <= max) return trimmed;
    return `${trimmed.slice(0, max)}…`;
};

export default function ContactsList() {
    const [contacts, setContacts] = useState([]);
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

    const fetchContacts = () => {
        setLoading(true);
        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', '10');
        if (search) params.set('search', search);

        api.get(`/admin/contacts?${params.toString()}`)
            .then(({ data }) => {
                setContacts(data.data || []);
                setPagination(data.meta || {});
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchContacts();
    }, [page, search]);

    const totalPages = useMemo(() => pagination.last_page || 1, [pagination.last_page]);

    const handlePageChange = (targetPage) => {
        const params = new URLSearchParams(searchParams);
        params.set('page', String(targetPage));
        setSearchParams(params);
    };

    return (
        <div className="space-y-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 className="text-2xl font-bold">Contatos</h1>
                    <p className="i10-muted text-sm mt-1">Mensagens recebidas pelo formulário público.</p>
                </div>
            </div>

            <div className="i10-card p-4">
                <div className="flex flex-wrap gap-3 items-center">
                    <div className="relative flex-1 min-w-[220px]">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            placeholder="Filtrar por nome, e-mail ou mensagem..."
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
                        {pagination.total || contacts.length} registro(s)
                    </span>
                </div>
            </div>

            {loading ? (
                <p className="i10-muted">Carregando...</p>
            ) : contacts.length === 0 ? (
                <div className="i10-card p-10 text-center i10-muted">Nenhum contato recebido ainda.</div>
            ) : (
                <div className="i10-card overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm min-w-[760px]">
                            <thead className="i10-table-head">
                                <tr>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">Nome</th>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">E-mail</th>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">Mensagem</th>
                                    <th className="text-left px-6 py-3 font-medium i10-muted">Recebido</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {contacts.map((item) => (
                                    <tr key={item.id}>
                                        <td className="px-6 py-4 align-top">{item.name}</td>
                                        <td className="px-6 py-4 align-top i10-muted break-all">{item.email}</td>
                                        <td className="px-6 py-4 align-top text-(--i10-text) whitespace-pre-wrap max-w-md">
                                            {messagePreview(item.message, 200)}
                                        </td>
                                        <td className="px-6 py-4 align-top i10-muted whitespace-nowrap">
                                            {item.created_at
                                                ? formatApiDatePtBr(item.created_at)
                                                : '-'}
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
