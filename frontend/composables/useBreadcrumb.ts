export interface BreadcrumbItem {
    label: string;
    to?: string;
    icon?: string;
}

export function useBreadcrumb() {
    const items = useState<BreadcrumbItem[]>('breadcrumb', () => []);

    function setBreadcrumb(newItems: BreadcrumbItem[]) {
        items.value = newItems;
    }

    function clearBreadcrumb() {
        items.value = [];
    }

    return { items: readonly(items), setBreadcrumb, clearBreadcrumb };
}
