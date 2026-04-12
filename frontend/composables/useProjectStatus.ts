export type ProjectStatusBadgeColor = 'gray' | 'blue' | 'green' | 'orange' | 'red' | 'primary';

/**
 * Maps backend project status strings to Nuxt UI badge colors (DESIGN.md accents are functional only).
 */
export function projectStatusBadgeColor(status: string): ProjectStatusBadgeColor {
    const s = status.toLowerCase();

    if (s.includes('plan')) {
        return 'blue';
    }
    if (s.includes('progress') || s.includes('active')) {
        return 'green';
    }
    if (s.includes('hold') || s.includes('pause')) {
        return 'orange';
    }
    if (s.includes('complete') || s.includes('paid') || s.includes('closed')) {
        return 'primary';
    }
    if (s.includes('cancel') || s.includes('fail')) {
        return 'red';
    }

    return 'gray';
}
