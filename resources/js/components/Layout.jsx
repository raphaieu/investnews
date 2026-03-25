import { Outlet, Link, useLocation, useNavigate, useSearchParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { useAuth } from '../hooks/useAuth.jsx';

export default function Layout() {
    const [searchParams, setSearchParams] = useSearchParams();
    const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '');
    const navigate = useNavigate();
    const location = useLocation();
    const { user, loading } = useAuth();
    const userInitial = user?.name?.trim()?.charAt(0)?.toUpperCase() || 'A';
    const searchParamValue = searchParams.get('search') || '';
    const searchParamsString = searchParams.toString();

    useEffect(() => {
        setSearchTerm(searchParamValue);
    }, [searchParamValue]);

    const applySearch = (value, options = {}) => {
        const params = new URLSearchParams(searchParamsString);
        const normalizedValue = value.trim();

        if (normalizedValue) {
            params.set('search', normalizedValue);
        } else {
            params.delete('search');
        }

        params.delete('page');
        const query = params.toString();
        const homeUrl = query ? `/?${query}` : '/';

        if (location.pathname === '/') {
            setSearchParams(params, options);
            return;
        }

        navigate(homeUrl, options);
    };

    useEffect(() => {
        if (searchTerm.trim() === searchParamValue) {
            return;
        }

        const timer = window.setTimeout(() => {
            applySearch(searchTerm, { replace: true });
        }, 350);

        return () => window.clearTimeout(timer);
    }, [searchTerm, searchParamValue, searchParamsString]);

    const handleSearch = (e) => {
        e.preventDefault();
        applySearch(searchTerm);
    };

    return (
        <div className="min-h-screen i10-page">
            <header className="i10-header sticky top-0 z-20 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 py-5">
                    <div className="flex flex-wrap items-center gap-4 md:gap-6">
                        <Link to="/" className="shrink-0 min-w-0">
                            <span className="block text-[1.82rem] font-extrabold leading-tight tracking-tight text-(--i10-brand)">
                                InvestNews
                            </span>
                            <span className="block text-xs text-slate-500 mt-1">
                                Ultimas noticias e analises
                            </span>
                        </Link>

                        <form onSubmit={handleSearch} className="hidden md:flex flex-1 min-w-[240px] lg:min-w-[320px] max-w-2xl mx-auto">
                            <div className="relative flex-1 min-w-0">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 i10-muted"
                                    aria-hidden="true"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.63 2.63a.75.75 0 1 0 1.06-1.06l-2.629-2.63A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                                <input
                                    type="text"
                                    placeholder="Buscar por titulo ou categoria..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="i10-input w-full rounded-r-none pl-9 pr-10 py-2.5 text-sm"
                                />
                                {searchTerm && (
                                    <button
                                        type="button"
                                        onClick={() => setSearchTerm('')}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors"
                                        aria-label="Limpar busca"
                                    >
                                        x
                                    </button>
                                )}
                            </div>
                            <button
                                type="submit"
                                className="i10-btn-primary px-5 py-2.5 rounded-l-none text-sm font-medium"
                            >
                                Pesquisar
                            </button>
                        </form>

                        <div className="w-full md:w-auto md:ml-auto flex flex-wrap items-center justify-end gap-2">
                            <Link
                                to="/contato"
                                className="i10-btn-outline px-3 py-2 text-sm font-medium shrink-0"
                            >
                                Contato
                            </Link>
                            {loading ? (
                                <span className="text-sm i10-muted">Verificando acesso...</span>
                            ) : user ? (
                                <div className="i10-card-soft px-3 py-2 w-full md:w-auto md:min-w-[250px] max-w-full flex items-center gap-3 overflow-hidden">
                                    <span className="h-9 w-9 rounded-full bg-(--i10-brand) text-white flex items-center justify-center text-sm font-semibold">
                                        {userInitial}
                                    </span>
                                    <div className="min-w-0 flex-1">
                                        <span className="block text-sm font-semibold leading-tight truncate">{user.name}</span>
                                        <span className="block text-xs i10-muted leading-tight truncate">{user.email}</span>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-3 text-sm">
                                        <Link to="/admin/noticias" className="i10-link font-medium hover:underline underline-offset-2">
                                            Painel
                                        </Link>
                                        <Link to="/admin/login" className="i10-muted hover:text-(--i10-text) hover:underline underline-offset-2">
                                            Sair
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <Link to="/admin/login" className="i10-btn-outline px-3 py-2 text-sm font-medium">
                                    Login admin
                                </Link>
                            )}
                        </div>
                    </div>
                </div>

                <div className="md:hidden px-4 sm:px-6 pb-5">
                    <form onSubmit={handleSearch} className="flex">
                        <div className="relative flex-1">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 i10-muted"
                                aria-hidden="true"
                            >
                                <path
                                    fillRule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.63 2.63a.75.75 0 1 0 1.06-1.06l-2.629-2.63A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z"
                                    clipRule="evenodd"
                                />
                            </svg>
                            <input
                                type="text"
                                placeholder="Buscar por titulo ou categoria..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="i10-input w-full rounded-r-none pl-9 pr-9 py-2 text-sm"
                            />
                            {searchTerm && (
                                <button
                                    type="button"
                                    onClick={() => setSearchTerm('')}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors"
                                    aria-label="Limpar busca"
                                >
                                    x
                                </button>
                            )}
                        </div>
                        <button
                            type="submit"
                            className="i10-btn-primary px-4 py-2 rounded-l-none text-sm"
                        >
                            Pesquisar
                        </button>
                    </form>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 py-8">
                <Outlet />
            </main>

            <footer className="i10-header mt-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 py-6 text-center text-sm i10-muted">
                    Desenvolvido por <a href="https://raphael-martins.com" target="_blank" rel="noopener noreferrer">Raphael Martins</a>
                    <br/><a href="https://github.com/raphaieu/investnews" target="_blank" rel="noopener noreferrer">Repositório do Projeto no GitHub</a>
                </div>
            </footer>
        </div>
    );
}
