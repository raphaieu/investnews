import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';

export default function CategoryForm() {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEditing = Boolean(id);

    const [name, setName] = useState('');
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (isEditing) {
            api.get(`/admin/categories/${id}`).then(({ data }) => {
                setName(data.data.name);
            });
        }
    }, [id, isEditing]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setSubmitting(true);

        try {
            if (isEditing) {
                await api.put(`/admin/categories/${id}`, { name });
            } else {
                await api.post('/admin/categories', { name });
            }
            navigate('/admin/categorias');
        } catch (err) {
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="max-w-lg">
            <h1 className="text-2xl font-bold mb-6">
                {isEditing ? 'Editar Categoria' : 'Nova Categoria'}
            </h1>

            <form onSubmit={handleSubmit} className="i10-card p-6 space-y-4">
                <div>
                    <label className="block text-sm font-medium mb-1">Nome</label>
                    <input
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.name && <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.name[0]}</p>}
                </div>

                <div className="flex gap-3">
                    <button
                        type="submit"
                        disabled={submitting}
                        className="i10-btn-primary px-4 py-2 text-sm disabled:opacity-50"
                    >
                        {submitting ? 'Salvando...' : 'Salvar'}
                    </button>
                    <button
                        type="button"
                        onClick={() => navigate('/admin/categorias')}
                        className="i10-btn-outline px-4 py-2 text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    );
}
