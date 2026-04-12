export function useSarPriceFormat() {
    function formatSar(amount: string | number | null | undefined): string {
        if (amount === null || amount === undefined || amount === '') {
            return '—';
        }
        const n = typeof amount === 'string' ? Number.parseFloat(amount) : amount;
        if (Number.isNaN(n)) {
            return '—';
        }
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(n);
    }

    return { formatSar };
}
