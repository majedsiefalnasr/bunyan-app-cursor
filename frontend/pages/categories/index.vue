<script setup lang="ts">
    import type { CategoryNode } from '~/types/category';

    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const { apiFetch } = useApi();
    const localePath = useLocalePath();

    const tree = ref<CategoryNode[]>([]);
    const isLoading = ref(true);

    async function load() {
        isLoading.value = true;
        try {
            const res = await apiFetch<{ data: CategoryNode[] }>('/v1/categories');
            tree.value = res.data ?? [];
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(() => {
        void load();
    });
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('categories.list_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('categories.list_subtitle') }}
            </p>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="tree.length === 0" class="text-sm text-[#666666]">
            {{ $t('categories.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <NuxtLink
                v-for="c in tree"
                :key="c.id"
                data-testid="category-card"
                :to="localePath(`/categories/${c.slug}`)"
                class="block rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] transition hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_8px_8px_-8px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <div
                    class="text-lg font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.04em"
                >
                    {{ c.name_ar }}
                </div>
                <p v-if="c.name_en" class="mt-1 text-xs text-[#666666]">{{ c.name_en }}</p>
            </NuxtLink>
        </div>
    </div>
</template>
