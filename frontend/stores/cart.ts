import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';

export type CartLine = {
    product_id: number;
    name: string;
    price: string; // decimal string from API
    quantity: number;
    sku?: string | null;
};

function clampQty(qty: number): number {
    if (!Number.isFinite(qty)) return 1;
    return Math.max(1, Math.min(9999, Math.floor(qty)));
}

export const useCartStore = defineStore('cart', () => {
    const lines = ref<CartLine[]>([]);

    // Simple persistence for local dev testing (per-browser).
    if (import.meta.client) {
        try {
            const raw = localStorage.getItem('bunyan_cart_v1');
            if (raw) {
                const parsed = JSON.parse(raw) as unknown;
                if (Array.isArray(parsed)) {
                    lines.value = parsed
                        .filter((x) => x && typeof x === 'object')
                        .map((x) => {
                            const o = x as Record<string, unknown>;
                            return {
                                product_id: Number(o.product_id),
                                name: String(o.name ?? ''),
                                price: String(o.price ?? '0'),
                                quantity: clampQty(Number(o.quantity ?? 1)),
                                sku: (o.sku as string | null | undefined) ?? null,
                            };
                        })
                        .filter((x) => Number.isFinite(x.product_id) && x.product_id > 0 && x.name);
                }
            }
        } catch {
            // ignore
        }
    }

    watch(
        lines,
        (v) => {
            if (!import.meta.client) return;
            try {
                localStorage.setItem('bunyan_cart_v1', JSON.stringify(v));
            } catch {
                // ignore
            }
        },
        { deep: true }
    );

    const count = computed(() => lines.value.reduce((sum, l) => sum + l.quantity, 0));
    const total = computed(() =>
        lines.value.reduce((sum, l) => sum + Number(l.price) * l.quantity, 0)
    );

    function add(line: Omit<CartLine, 'quantity'>, quantity = 1) {
        const q = clampQty(quantity);
        const idx = lines.value.findIndex((l) => l.product_id === line.product_id);
        if (idx !== -1) {
            lines.value[idx] = {
                ...lines.value[idx],
                quantity: clampQty(lines.value[idx].quantity + q),
            };
            return;
        }
        lines.value.push({ ...line, quantity: q });
    }

    function setQuantity(productId: number, quantity: number) {
        const q = clampQty(quantity);
        const idx = lines.value.findIndex((l) => l.product_id === productId);
        if (idx === -1) return;
        lines.value[idx] = { ...lines.value[idx], quantity: q };
    }

    function remove(productId: number) {
        lines.value = lines.value.filter((l) => l.product_id !== productId);
    }

    function clear() {
        lines.value = [];
    }

    return { lines, count, total, add, setQuantity, remove, clear };
});
