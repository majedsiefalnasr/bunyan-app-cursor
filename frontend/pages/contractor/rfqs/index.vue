<script setup lang="ts">
    import type { RfqDetail } from '~/types/rfq';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['contractor'],
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
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('rfq.contractor_list_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('rfq.contractor_list_subtitle') }}
            </p>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('rfq.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <UCard
                v-for="r in rows"
                :key="r.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-[#171717] dark:text-white">
                        {{ r.title }}
                    </h2>
                    <RfqStatusBadge :status="r.status" :status-label="r.status_label" />
                    <UButton
                        :to="localePath(`/contractor/rfqs/${r.id}`)"
                        variant="soft"
                        color="gray"
                    >
                        {{ $t('rfq.quote_heading') }}
                    </UButton>
                </div>
            </UCard>
        </div>
    </div>
</template>
