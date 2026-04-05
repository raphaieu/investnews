import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';

export default function MarketInstrumentForm() {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEditing = Boolean(id);

    const [symbol, setSymbol] = useState('');
    const [displayName, setDisplayName] = useState('');
    const [feedId, setFeedId] = useState('mt5-forex');
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (isEditing) {
            api.get(`/admin/market-instruments/${id}`).then(({ data }) => {
                setSymbol(data.data.symbol);
                setDisplayName(data.data.display_name);
                setFeedId(data.data.feed_id || 'mt5-forex');
            });
        }
    }, [id, isEditing]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setSubmitting(true);

        try {
            if (isEditing) {
                await api.put(`/admin/market-instruments/${id}`, {
                    symbol,
                    display_name: displayName,
                    feed_id: feedId,
                });
            } else {
                await api.post('/admin/market-instruments', {
                    symbol,
                    display_name: displayName,
                    feed_id: feedId,
                });
            }
            navigate('/admin/ativos');
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
                {isEditing ? 'Editar ativo' : 'Novo ativo'}
            </h1>

            <form onSubmit={handleSubmit} className="i10-card p-6 space-y-4">
                <div>
                    <label className="block text-sm font-medium mb-1">Símbolo (MT5)</label>
                    <input
                        type="text"
                        value={symbol}
                        onChange={(e) => setSymbol(e.target.value.toUpperCase())}
                        className="w-full i10-input px-3 py-2 text-sm font-mono"
                        placeholder="XAUUSD"
                    />
                    {errors.symbol && <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.symbol[0]}</p>}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">Nome na grade</label>
                    <input
                        type="text"
                        value={displayName}
                        onChange={(e) => setDisplayName(e.target.value)}
                        className="w-full i10-input px-3 py-2 text-sm"
                        placeholder="Ouro"
                    />
                    {errors.display_name && (
                        <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.display_name[0]}</p>
                    )}
                </div>

                <div>
                    <label className="block text-sm font-medium mb-1">Mercado</label>
                    <select
                        value={feedId}
                        onChange={(e) => setFeedId(e.target.value)}
                        className="w-full i10-input px-3 py-2 text-sm"
                    >
                        <option value="mt5-forex">Forex</option>
                        <option value="mt5-b3">B3</option>
                    </select>
                    {errors.feed_id && (
                        <p className="text-[var(--i10-danger)] text-xs mt-1">{errors.feed_id[0]}</p>
                    )}
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
                        onClick={() => navigate('/admin/ativos')}
                        className="i10-btn-outline px-4 py-2 text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    );
}
