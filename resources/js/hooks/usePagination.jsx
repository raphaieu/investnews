import { useCallback, useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '../services/api';

export default function usePagination(fetchUrl, { perPage = 10, debounceMs = 300 } = {}) {
    const [items, setItems] = useState([]);
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
        }, debounceMs);

        return () => window.clearTimeout(timer);
    }, [searchTerm, search, searchParams, setSearchParams, debounceMs]);

    const refresh = useCallback(() => {
        setLoading(true);
        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', String(perPage));
        if (search) params.set('search', search);

        api.get(`${fetchUrl}?${params.toString()}`)
            .then(({ data }) => {
                setItems(data.data || []);
                setPagination(data.meta || {});
            })
            .finally(() => setLoading(false));
    }, [fetchUrl, page, search, perPage]);

    useEffect(() => {
        refresh();
    }, [refresh]);

    const totalPages = useMemo(() => pagination.last_page || 1, [pagination.last_page]);

    const handlePageChange = (targetPage) => {
        const params = new URLSearchParams(searchParams);
        params.set('page', String(targetPage));
        setSearchParams(params);
    };

    return {
        items,
        pagination,
        loading,
        page,
        search,
        searchTerm,
        setSearchTerm,
        totalPages,
        handlePageChange,
        refresh,
    };
}
