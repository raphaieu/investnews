import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';

export default function NewsForm() {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEditing = Boolean(id);

    const [form, setForm] = useState({
        title: '',
        content: '',
        category_id: '',
        published_at: '',
    });
    const [categories, setCategories] = useState([]);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        api.get('/admin/categories').then(({ data }) => setCategories(data.data || []));
    }, []);

    useEffect(() => {
        if (isEditing) {
            api.get(`/admin/news/${id}`).then(({ data }) => {
                const n = data.data;
                setForm({
                    title: n.title,
                    content: n.content,
                    category_id: n.category_id || '',
                    published_at: n.published_at ? n.published_at.split('T')[0] : '',
                });
            });
        }
    }, [id, isEditing]);

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setSubmitting(true);

        try {
            const payload = {
                ...form,
                published_at: form.published_at || null,
            };

            if (isEditing) {
                await api.put(`/admin/news/${id}`, payload);
            } else {
                await api.post('/admin/news', payload);
            }
            navigate('/admin/noticias');
        } catch (err) {
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="max-w-2xl">
            <h1 className="text-2xl font-bold mb-6">
                {isEditing ? 'Editar Notícia' : 'Nova Notícia'}
            </h1>

            <form onSubmit={handleSubmit} className="i10-card p-6 space-y-4">
                <div>
                    <label className="block text-sm font-medium mb-1">Título</label>
                    <input
                        type="text"
                        name="title"
                        value={form.title}
                        onChange={handleChange}
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.title && <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.title[0]}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">Categoria</label>
                    <select
                        name="category_id"
                        value={form.category_id}
                        onChange={handleChange}
                        className="w-full i10-input px-3 py-2 text-sm"
                    >
                        <option value="">Selecione...</option>
                        {categories.map((cat) => (
                            <option key={cat.id} value={cat.id}>{cat.name}</option>
                        ))}
                    </select>
                    {errors.category_id && <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.category_id[0]}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">Conteúdo</label>
                    <textarea
                        name="content"
                        value={form.content}
                        onChange={handleChange}
                        rows={10}
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.content && <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.content[0]}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">Data de publicação</label>
                    <input
                        type="date"
                        name="published_at"
                        value={form.published_at}
                        onChange={handleChange}
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.published_at && <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.published_at[0]}</p>}
                </div>

                <div className="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={submitting}
                        className="i10-btn-primary px-4 py-2 text-sm disabled:opacity-50"
                    >
                        {submitting ? 'Salvando...' : 'Salvar'}
                    </button>
                    <button
                        type="button"
                        onClick={() => navigate('/admin/noticias')}
                        className="i10-btn-outline px-4 py-2 text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    );
}
