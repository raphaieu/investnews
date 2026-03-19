import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../services/api';
import { formatApiDateLongPtBr } from '../utils/date';

export default function NewsDetail() {
    const { slug } = useParams();
    const [article, setArticle] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        setLoading(true);
        api.get(`/news/${slug}`)
            .then(({ data }) => setArticle(data.data))
            .catch(() => setError('Notícia não encontrada.'))
            .finally(() => setLoading(false));
    }, [slug]);

    if (loading) {
        return <p className="i10-muted text-center py-12">Carregando...</p>;
    }

    if (error) {
        return (
            <div className="text-center py-12">
                <p className="i10-muted mb-4">{error}</p>
                <Link to="/" className="i10-link hover:underline">Voltar para a home</Link>
            </div>
        );
    }

    return (
        <article className="max-w-3xl mx-auto i10-card p-8">
            {article.category && (
                <Link
                    to={`/?category=${article.category.slug}`}
                    className="text-xs i10-link font-medium uppercase tracking-wider hover:underline"
                >
                    {article.category.name}
                </Link>
            )}
            <h1 className="text-3xl font-bold mt-2 mb-4">{article.title}</h1>
            {article.published_at && (
                <p className="text-sm i10-muted mb-6">
                    {formatApiDateLongPtBr(article.published_at)}
                </p>
            )}
            <div
                className="prose max-w-none text-(--i10-text) leading-relaxed whitespace-pre-line"
            >
                {article.content}
            </div>
            <div className="mt-8 pt-6 border-t border-(--i10-border)">
                <Link to="/" className="text-sm i10-link hover:underline">&larr; Voltar para notícias</Link>
            </div>
        </article>
    );
}
