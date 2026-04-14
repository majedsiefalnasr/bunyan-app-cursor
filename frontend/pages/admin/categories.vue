<script setup lang="ts">
    import type { CategoryNode } from '~/types/category';

    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { apiFetch } = useApi();
    const toast = useToast();

    const tree = ref<CategoryNode[]>([]);
    const isLoading = ref(false);
    const modalOpen = ref(false);
    const form = reactive({
        name_ar: '',
        name_en: '',
        parent_id: null as number | null,
    });

    function findSlugById(nodes: CategoryNode[], id: number): string | null {
        for (const n of nodes) {
            if (n.id === id) {
                return n.slug;
            }
            if (n.children?.length) {
                const found = findSlugById(n.children, id);
                if (found) {
                    return found;
                }
            }
        }
        return null;
    }

    async function fetchTree() {
        isLoading.value = true;
        try {
            const res = await apiFetch<{ success: boolean; data: CategoryNode[] }>(
                '/v1/categories?include_inactive=1'
            );
            tree.value = res.data ?? [];
        } catch {
            toast.add({
                title: 'خطأ',
                description: 'تعذر تحميل التصنيفات',
                color: 'red',
            });
        } finally {
            isLoading.value = false;
        }
    }

    async function onReorder(payload: { id: number; parentId: number | null; newIndex: number }) {
        try {
            const slug = findSlugById(tree.value, payload.id);
            if (!slug) {
                toast.add({
                    title: 'خطأ',
                    description: 'تعذر العثور على التصنيف',
                    color: 'red',
                });
                return;
            }
            await apiFetch(`/v1/categories/${slug}/reorder`, {
                method: 'PUT',
                body: { sort_order: payload.newIndex },
            });
            toast.add({ title: 'تم', description: 'تم تحديث الترتيب', color: 'green' });
            await fetchTree();
        } catch {
            toast.add({ title: 'خطأ', description: 'فشل إعادة الترتيب', color: 'red' });
        }
    }

    async function submitCreate() {
        try {
            await apiFetch('/v1/categories', {
                method: 'POST',
                body: {
                    name_ar: form.name_ar,
                    name_en: form.name_en,
                    parent_id: form.parent_id,
                },
            });
            toast.add({ title: 'تم', description: 'تم إنشاء التصنيف', color: 'green' });
            modalOpen.value = false;
            form.name_ar = '';
            form.name_en = '';
            form.parent_id = null;
            await fetchTree();
        } catch {
            toast.add({ title: 'خطأ', description: 'فشل إنشاء التصنيف', color: 'red' });
        }
    }

    const demoTrail = computed(() => {
        const first = tree.value[0];
        if (!first) return [];
        return [{ id: first.id, name_ar: first.name_ar }];
    });

    onMounted(() => fetchTree());
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">التصنيفات</h1>
                <EcommerceCategoryBreadcrumb
                    class="mt-2"
                    :trail="demoTrail"
                    :current="{ name_ar: 'إدارة الشجرة' }"
                />
            </div>
            <UButton color="gray" @click="modalOpen = true">تصنيف جديد</UButton>
        </div>

        <UCard v-if="isLoading">
            <USkeleton class="h-40 w-full" />
        </UCard>
        <EcommerceCategoryTree v-else :nodes="tree" @reorder="onReorder" />

        <UCard>
            <template #header>
                <span class="font-medium text-[#171717]">اختيار تصنيف (معاينة)</span>
            </template>
            <EcommerceCategorySelect v-model="form.parent_id" :categories="tree" />
        </UCard>

        <UModal v-model="modalOpen">
            <UCard>
                <template #header>
                    <span class="font-medium">تصنيف جديد</span>
                </template>
                <form class="space-y-4" @submit.prevent="submitCreate">
                    <UFormGroup label="الاسم بالعربية">
                        <UInput v-model="form.name_ar" required />
                    </UFormGroup>
                    <UFormGroup label="الاسم بالإنجليزية">
                        <UInput v-model="form.name_en" required />
                    </UFormGroup>
                    <UFormGroup label="تصنيف أب (اختياري)">
                        <EcommerceCategorySelect v-model="form.parent_id" :categories="tree" />
                    </UFormGroup>
                    <div class="flex justify-end gap-2">
                        <UButton color="gray" variant="ghost" @click="modalOpen = false"
                            >إلغاء</UButton
                        >
                        <UButton type="submit">حفظ</UButton>
                    </div>
                </form>
            </UCard>
        </UModal>
    </div>
</template>
