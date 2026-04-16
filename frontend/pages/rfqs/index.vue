<script setup lang="ts">
    import type { RfqDetail } from '~/types/rfq';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer'],
    });

    const localePath = useLocalePath();
    const { listRfqs } = useRfqs();

    const rows = ref<RfqDetail[]>([]);
    const isLoading = ref(true);

    onMounted(async () => {
        try {
            const page = await listRfqs({ per_page: 20, sort: '-created_at' });
            rows.value = page.data;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                    {{ $t('rfq.list_title') }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('rfq.list_subtitle') }}
                </p>
            </div>
            <UButton :to="localePath('/rfqs/new')" color="primary" variant="solid" size="sm">
                {{ $t('rfq.new_title') }}
            </UButton>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('rfq.empty') }}
        </p>

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="r in rows"
                :key="r.id"
                variant="subtle"
                :title="r.title"
                :ui="{
                    container: 'gap-y-2',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <RfqStatusBadge :status="r.status" :status-label="r.status_label" />
                    <div class="flex gap-2">
                        <UButton
                            :to="localePath(`/rfqs/${r.id}`)"
                            variant="soft"
                            color="neutral"
                            size="xs"
                        >
                            {{ $t('rfq.detail_link') }}
                        </UButton>
                        <UButton
                            :to="localePath(`/rfqs/${r.id}/compare`)"
                            variant="soft"
                            color="neutral"
                            size="xs"
                        >
                            {{ $t('rfq.compare_link') }}
                        </UButton>
                    </div>
                </div>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
