const monthNamesPtBr = [
    'janeiro',
    'fevereiro',
    'março',
    'abril',
    'maio',
    'junho',
    'julho',
    'agosto',
    'setembro',
    'outubro',
    'novembro',
    'dezembro',
];

const normalizeDateParts = (value) => {
    if (!value || typeof value !== 'string') return null;

    const datePart = value.split('T')[0];
    const match = datePart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) return null;

    return {
        year: Number(match[1]),
        month: Number(match[2]),
        day: Number(match[3]),
    };
};

export const formatApiDatePtBr = (value) => {
    const parts = normalizeDateParts(value);
    if (!parts) return '-';

    return `${String(parts.day).padStart(2, '0')}/${String(parts.month).padStart(2, '0')}/${parts.year}`;
};

export const formatApiDateLongPtBr = (value) => {
    const parts = normalizeDateParts(value);
    if (!parts) return '';

    const monthName = monthNamesPtBr[parts.month - 1];
    return `${String(parts.day).padStart(2, '0')} de ${monthName} de ${parts.year}`;
};
