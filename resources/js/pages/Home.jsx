import { useState, useEffect, useMemo } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import api from '../services/api';
import MarketTickerWidget from '../components/MarketTickerWidget';

const COLOR_STYLE_MAP = {
    sky:     { badge: 'bg-sky-200 text-sky-800 border-sky-300',         placeholder: 'from-sky-200 to-blue-300' },
    emerald: { badge: 'bg-emerald-200 text-emerald-800 border-emerald-300', placeholder: 'from-emerald-200 to-teal-300' },
    indigo:  { badge: 'bg-indigo-200 text-indigo-800 border-indigo-300',   placeholder: 'from-indigo-200 to-violet-300' },
    amber:   { badge: 'bg-amber-200 text-amber-800 border-amber-300',     placeholder: 'from-amber-200 to-orange-300' },
    rose:    { badge: 'bg-rose-200 text-rose-800 border-rose-300',         placeholder: 'from-rose-200 to-pink-300' },
    violet:  { badge: 'bg-violet-200 text-violet-800 border-violet-300',   placeholder: 'from-violet-200 to-purple-300' },
    cyan:    { badge: 'bg-cyan-200 text-cyan-800 border-cyan-300',         placeholder: 'from-cyan-200 to-teal-300' },
    orange:  { badge: 'bg-orange-200 text-orange-800 border-orange-300',   placeholder: 'from-orange-200 to-amber-300' },
    yellow:  { badge: 'bg-yellow-200 text-yellow-800 border-yellow-300',   placeholder: 'from-yellow-200 to-amber-300' },
    lime:    { badge: 'bg-lime-200 text-lime-800 border-lime-300',         placeholder: 'from-lime-200 to-green-300' },
};

const DEFAULT_STYLE = { badge: 'bg-slate-100 text-slate-600 border-slate-200', placeholder: 'from-slate-100 to-slate-200' };

const escapeRegExp = (value) => value.replace(/[|\\{}()[\]^$+*?.-]/g, '\\$&');

export default function Home() {
    const [news, setNews] = useState([]);
    const [pagination, setPagination] = useState({});
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchParams, setSearchParams] = useSearchParams();

    const currentPage = parseInt(searchParams.get('page') || '1');
    const search = searchParams.get('search') || '';
    const category = searchParams.get('category') || '';

    useEffect(() => {
        api.get('/categories').then(({ data }) => setCategories(data.data || []));
    }, []);

    useEffect(() => {
        setLoading(true);
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (category) params.set('category', category);
        params.set('page', currentPage);

        api.get(`/news?${params.toString()}`)
            .then(({ data }) => {
                setNews(data.data || []);
                setPagination(data.meta || {});
            })
            .finally(() => setLoading(false));
    }, [search, category, currentPage]);

    const categoryColorMap = useMemo(() => {
        const map = {};
        categories.forEach((cat) => {
            if (cat.color) {
                map[cat.slug] = cat.color;
            }
        });
        return map;
    }, [categories]);

    const handleCategoryFilter = (slug) => {
        const params = new URLSearchParams(searchParams);
        if (slug) {
            params.set('category', slug);
        } else {
            params.delete('category');
        }
        params.delete('page');
        setSearchParams(params);
    };

    const handlePageChange = (page) => {
        const params = new URLSearchParams(searchParams);
        params.set('page', page);
        setSearchParams(params);
    };

    const clearSearch = () => {
        const params = new URLSearchParams(searchParams);
        params.delete('search');
        params.delete('page');
        setSearchParams(params);
    };

    const clearAllFilters = () => {
        const params = new URLSearchParams(searchParams);
        params.delete('search');
        params.delete('category');
        params.delete('page');
        setSearchParams(params);
    };

    const getCategoryStyle = (slug) => {
        const color = categoryColorMap[slug];
        return COLOR_STYLE_MAP[color] || DEFAULT_STYLE;
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

    return (
        <div className="lg:grid lg:grid-cols-[1fr_280px] lg:gap-8 lg:items-start">
        <div className="space-y-7">
            <section className="hidden md:block i10-card p-6 md:p-8 bg-linear-to-r from-slate-50 to-slate-100/70">
                <p className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold i10-badge uppercase tracking-wide">
                    Portal financeiro
                </p>
                <h1 className="mt-4 text-2xl md:text-3xl font-extrabold tracking-tight">
                    Notícias para decisões de investimento mais inteligentes
                </h1>
                <p className="mt-3 i10-muted max-w-3xl">
                    Busque por título ou categoria, navegue pelos filtros e acompanhe os principais temas do mercado em um só lugar.
                </p>
                <div className="mt-5 flex flex-wrap items-center gap-3 text-sm">
                    <span className="i10-card-soft px-3 py-1.5">
                        {pagination.total || news.length} notícias disponíveis
                    </span>
                    {category && (
                        <span className="i10-card-soft px-3 py-1.5 inline-flex items-center gap-2">
                            Filtro ativo: <strong>{category}</strong>
                            <button
                                type="button"
                                onClick={() => handleCategoryFilter('')}
                                className="text-slate-500 hover:text-slate-800 transition-colors"
                                aria-label="Remover filtro de categoria"
                            >
                                x
                            </button>
                        </span>
                    )}
                    {search && (
                        <span className="i10-card-soft px-3 py-1.5 inline-flex items-center gap-2">
                            Busca: <strong>&quot;{search}&quot;</strong>
                            <button
                                type="button"
                                onClick={clearSearch}
                                className="text-slate-500 hover:text-slate-800 transition-colors"
                                aria-label="Limpar busca"
                            >
                                x
                            </button>
                        </span>
                    )}
                    {(search || category) && (
                        <button
                            type="button"
                            onClick={clearAllFilters}
                            className="px-3 py-1.5 rounded-md text-sm border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors"
                        >
                            Limpar filtros
                        </button>
                    )}
                </div>
            </section>

            <div className="flex flex-wrap justify-center gap-2 p-1.5 rounded-xl bg-(--i10-surface-soft) border border-(--i10-border)">
                <button
                    onClick={() => handleCategoryFilter('')}
                    className={`px-4 py-1.5 rounded-full text-sm border cursor-pointer transition-colors ${!category ? 'bg-(--i10-brand) text-white border-(--i10-brand) shadow-sm' : 'bg-(--i10-surface) text-(--i10-text-muted) border-(--i10-border) hover:bg-slate-200'}`}
                >
                    Todas
                </button>
                {categories.map((cat) => (
                    <button
                        key={cat.id}
                        onClick={() => handleCategoryFilter(cat.slug)}
                        className={`px-4 py-1.5 rounded-full text-sm border cursor-pointer transition-colors ${category === cat.slug ? 'bg-(--i10-brand) text-white border-(--i10-brand) shadow-sm' : 'bg-(--i10-surface) text-(--i10-text-muted) border-(--i10-border) hover:bg-slate-200'}`}
                    >
                        {cat.name}
                    </button>
                ))}
            </div>

            {loading ? (
                <p className="i10-muted text-center py-12">Carregando notícias...</p>
            ) : news.length === 0 ? (
                <div className="i10-card p-10 text-center">
                    <h2 className="text-xl font-bold">Nenhuma notícia encontrada</h2>
                    <p className="i10-muted mt-2">
                        Tente outro termo de busca ou remova o filtro de categoria para ampliar os resultados.
                    </p>
                </div>
            ) : (
                <>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
                        {news.map((item) => {
                            const style = getCategoryStyle(item.category?.slug);
                            return (
                            <Link
                                key={item.id}
                                to={`/noticias/${item.slug}`}
                                className="i10-card overflow-hidden flex flex-col transition-all duration-300 hover:-translate-y-1 hover:shadow-lg h-full cursor-pointer group"
                            >
                                <div className={`h-40 bg-linear-to-br ${style.placeholder} border-b border-slate-200`}>
                                    {item.image_url ? (
                                        <img
                                            src={item.image_url}
                                            alt={item.title}
                                            className="w-full h-full object-cover"
                                            loading="lazy"
                                        />
                                    ) : (
                                        <div className="h-full w-full bg-white/10" />
                                    )}
                                </div>
                                <div className="p-6 flex-1">
                                    {item.category && (
                                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border uppercase tracking-wide ${style.badge}`}>
                                            {item.category.name}
                                        </span>
                                    )}
                                    <h2 className="text-xl font-bold leading-tight mt-3 mb-4 group-hover:text-(--i10-brand-hover) transition-colors">
                                        {highlightText(item.title)}
                                    </h2>
                                    <p className="text-sm text-slate-500 line-clamp-4">
                                        {highlightText(item.excerpt)}
                                    </p>
                                </div>
                                <div className="px-6 pb-6">
                                    <span className="inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-medium bg-slate-900 text-white border border-slate-900 transition-colors group-hover:bg-slate-800">
                                        Acessar
                                    </span>
                                </div>
                            </Link>
                            );
                        })}
                    </div>

                    {pagination.last_page > 1 && (
                        <div className="flex justify-center gap-2 mt-8">
                            {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                                <button
                                    key={page}
                                    onClick={() => handlePageChange(page)}
                                    className={`px-3.5 py-1.5 rounded-md text-sm border transition-colors ${page === currentPage ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-(--i10-surface) text-(--i10-text-muted) border-(--i10-border) hover:bg-slate-200'}`}
                                >
                                    {page}
                                </button>
                            ))}
                        </div>
                    )}
                </>
            )}
        </div>

        <aside className="hidden lg:block lg:sticky lg:top-24 space-y-4">
            <MarketTickerWidget />
        </aside>
        </div>
    );
}
