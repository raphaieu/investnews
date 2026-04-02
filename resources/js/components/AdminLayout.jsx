import { Outlet, Link, Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth.jsx';

export default function AdminLayout() {
    const { user, loading, logout } = useAuth();
    const location = useLocation();

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center i10-page">
                <p className="i10-muted">Carregando...</p>
            </div>
        );
    }

    if (!user) {
        return <Navigate to="/admin/login" state={{ from: location }} replace />;
    }

    const isActive = (path) => location.pathname.startsWith(path);

    return (
        <div className="min-h-screen i10-page">
            <header className="i10-header sticky top-0 z-20 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-4 sm:gap-6">
                        <Link to="/" className="text-2xl font-bold">
                            InvestNews
                        </Link>
                        <span className="text-xs i10-badge px-2 py-1">
                            Admin
                        </span>
                    </div>

                    <nav className="flex items-center gap-2 sm:gap-3">
                        <Link
                            to="/admin/categorias"
                            className={`text-sm px-3 py-1.5 rounded-md border transition-colors ${isActive('/admin/categorias') ? 'bg-(--i10-brand) text-white border-(--i10-brand)' : 'bg-(--i10-surface) i10-muted border-(--i10-border) hover:text-(--i10-text)'}`}
                        >
                            Categorias
                        </Link>
                        <Link
                            to="/admin/noticias"
                            className={`text-sm px-3 py-1.5 rounded-md border transition-colors ${isActive('/admin/noticias') ? 'bg-(--i10-brand) text-white border-(--i10-brand)' : 'bg-(--i10-surface) i10-muted border-(--i10-border) hover:text-(--i10-text)'}`}
                        >
                            Notícias
                        </Link>
                        <Link
                            to="/admin/contatos"
                            className={`text-sm px-3 py-1.5 rounded-md border transition-colors ${isActive('/admin/contatos') ? 'bg-(--i10-brand) text-white border-(--i10-brand)' : 'bg-(--i10-surface) i10-muted border-(--i10-border) hover:text-(--i10-text)'}`}
                        >
                            Contatos
                        </Link>
                        <Link
                            to="/admin/ativos"
                            className={`text-sm px-3 py-1.5 rounded-md border transition-colors ${isActive('/admin/ativos') ? 'bg-(--i10-brand) text-white border-(--i10-brand)' : 'bg-(--i10-surface) i10-muted border-(--i10-border) hover:text-(--i10-text)'}`}
                        >
                            Cotações
                        </Link>
                        <a
                            href="/horizon"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm px-3 py-1.5 rounded-md border bg-(--i10-surface) i10-muted border-(--i10-border) hover:text-(--i10-text) transition-colors"
                        >
                            Horizon
                        </a>
                        <button
                            onClick={logout}
                            className="text-sm px-3 py-1.5 rounded-md border border-red-200 text-(--i10-danger) hover:bg-red-50 transition-colors"
                        >
                            Sair
                        </button>
                    </nav>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 py-8">
                <Outlet />
            </main>
        </div>
    );
}
