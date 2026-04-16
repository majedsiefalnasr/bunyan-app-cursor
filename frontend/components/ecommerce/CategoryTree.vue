<script setup lang="ts">
    import type { CategoryNode } from '~/types/category';

    const props = defineProps<{
        nodes: CategoryNode[];
    }>();

    const emit = defineEmits<{
        reorder: [payload: { id: number; parentId: number | null; newIndex: number }];
    }>();

    interface FlatRow {
        id: number;
        parent_id: number | null;
        name_ar: string;
        is_active: boolean;
        sort_order: number;
        depth: number;
    }

    function walk(nodes: CategoryNode[], depth: number, out: FlatRow[]): void {
        const sorted = [...nodes].sort((a, b) => a.sort_order - b.sort_order || a.id - b.id);
        for (const n of sorted) {
            out.push({
                id: n.id,
                parent_id: n.parent_id,
                name_ar: n.name_ar,
                is_active: n.is_active,
                sort_order: n.sort_order,
                depth,
            });
            if (n.children?.length) {
                walk(n.children, depth + 1, out);
            }
        }
    }

    const flatRows = computed(() => {
        const out: FlatRow[] = [];
        walk(props.nodes, 0, out);
        return out;
    });

    const rowsByParent = computed(() => {
        const m = new Map<string | number, FlatRow[]>();
        for (const r of flatRows.value) {
            const key = r.parent_id ?? 'root';
            const list = m.get(key) ?? [];
            list.push(r);
            m.set(key, list);
        }
        for (const list of m.values()) {
            list.sort((a, b) => a.sort_order - b.sort_order || a.id - b.id);
        }
        return m;
    });

    let draggingId: number | null = null;

    function onDragStart(row: FlatRow, ev: DragEvent) {
        draggingId = row.id;
        ev.dataTransfer?.setData('text/plain', String(row.id));
        ev.dataTransfer!.effectAllowed = 'move';
    }

    function onDrop(target: FlatRow, ev: DragEvent) {
        ev.preventDefault();
        const raw = ev.dataTransfer?.getData('text/plain');
        const dragId = raw ? Number(raw) : draggingId;
        draggingId = null;
        if (!dragId || dragId === target.id) return;

        const pKey = target.parent_id ?? 'root';
        const dragRow = flatRows.value.find((r) => r.id === dragId);
        if (!dragRow || (dragRow.parent_id ?? 'root') !== pKey) return;

        const siblings = [...(rowsByParent.value.get(pKey) ?? [])];
        const from = siblings.findIndex((r) => r.id === dragId);
        if (from < 0) return;
        const [moved] = siblings.splice(from, 1);
        const to = siblings.findIndex((r) => r.id === target.id);
        siblings.splice(to, 0, moved!);

        const newIndex = siblings.findIndex((r) => r.id === dragId);
        emit('reorder', {
            id: dragId,
            parentId: target.parent_id,
            newIndex,
        });
    }
</script>

<template>
    <ul class="space-y-1">
        <li
            v-for="row in flatRows"
            :key="row.id"
            class="rounded-md bg-white py-2 ps-3 pe-3 shadow-[0_0_0_1px_rgba(0,0,0,0.08)]"
            :style="{ marginInlineStart: `${row.depth * 14}px` }"
            draggable="true"
            @dragstart="onDragStart(row, $event)"
            @dragover.prevent
            @drop="onDrop(row, $event)"
        >
            <div class="flex items-center justify-between gap-2">
                <span class="font-medium tracking-tight text-[#171717]">{{ row.name_ar }}</span>
                <UBadge v-if="!row.is_active" color="neutral" variant="subtle">غير نشط</UBadge>
            </div>
        </li>
    </ul>
</template>
