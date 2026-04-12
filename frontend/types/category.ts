export interface CategoryNode {
    id: number;
    parent_id: number | null;
    name_ar: string;
    name_en: string;
    slug: string;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
    children?: CategoryNode[];
}
