import { useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';

export default function Contact() {
    const [form, setForm] = useState({
        name: '',
        email: '',
        message: '',
    });
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState(false);

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
        setSuccess(false);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setSuccess(false);
        setSubmitting(true);

        try {
            const { status } = await api.post('/contacts', form);
            if (status !== 202 && status !== 201) {
                return;
            }
            setForm({ name: '', email: '', message: '' });
            setSuccess(true);
        } catch (err) {
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto space-y-6">
            <div>
                <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight">Contato</h1>
                <p className="mt-2 i10-muted text-sm">
                    Envie sua mensagem. Responderemos quando possível.
                </p>
            </div>

            {success && (
                <div
                    className="i10-card p-4 border border-emerald-200 bg-emerald-50/80 text-emerald-900 text-sm"
                    role="status"
                >
                    Recebemos sua mensagem; ela será processada em breve. Obrigado pelo contato!
                </div>
            )}

            <form onSubmit={handleSubmit} className="i10-card p-6 space-y-4">
                <div>
                    <label htmlFor="contact-name" className="block text-sm font-medium mb-1">
                        Nome
                    </label>
                    <input
                        id="contact-name"
                        type="text"
                        name="name"
                        value={form.name}
                        onChange={handleChange}
                        autoComplete="name"
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.name && (
                        <p className="text-(--i10-danger) text-xs mt-1">{errors.name[0]}</p>
                    )}
                </div>

                <div>
                    <label htmlFor="contact-email" className="block text-sm font-medium mb-1">
                        E-mail
                    </label>
                    <input
                        id="contact-email"
                        type="email"
                        name="email"
                        value={form.email}
                        onChange={handleChange}
                        autoComplete="email"
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.email && (
                        <p className="text-(--i10-danger) text-xs mt-1">{errors.email[0]}</p>
                    )}
                </div>

                <div>
                    <label htmlFor="contact-message" className="block text-sm font-medium mb-1">
                        Mensagem
                    </label>
                    <textarea
                        id="contact-message"
                        name="message"
                        value={form.message}
                        onChange={handleChange}
                        rows={8}
                        className="w-full i10-input px-3 py-2 text-sm"
                    />
                    {errors.message && (
                        <p className="text-(--i10-danger) text-xs mt-1">{errors.message[0]}</p>
                    )}
                </div>

                <div className="flex flex-wrap gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={submitting}
                        className="i10-btn-primary px-4 py-2 text-sm disabled:opacity-50"
                    >
                        {submitting ? 'Enviando...' : 'Enviar'}
                    </button>
                    <Link to="/" className="i10-btn-outline px-4 py-2 text-sm inline-flex items-center">
                        Voltar às notícias
                    </Link>
                </div>
            </form>
        </div>
    );
}
