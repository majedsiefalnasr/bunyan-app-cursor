<script setup lang="ts">
    import type { QuotationRow, RfqDetail } from '~/types/rfq';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer'],
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const toast = useToast();
    const { t } = useI18n();
    const { getRfq, sendRfq, beginEvaluation, closeRfq, listQuotations, acceptQuotation } =
        useRfqs();

    const id = computed(() => Number(route.params.id));
    const rfq = ref<RfqDetail | null>(null);
    const quotations = ref<QuotationRow[]>([]);
    const loading = ref(true);
    const busy = ref(false);
    const sendDeadline = ref('');

    async function refresh() {
        loading.value = true;
        try {
            rfq.value = await getRfq(id.value);
            const q = await listQuotations(id.value, { per_page: 30 });
            quotations.value = q.data;
            if (rfq.value?.response_deadline) {
                const d = new Date(rfq.value.response_deadline);
                sendDeadline.value = Number.isNaN(d.getTime()) ? '' : d.toISOString().slice(0, 16);
            } else {
                sendDeadline.value = '';
            }
        } finally {
            loading.value = false;
        }
    }

    onMounted(refresh);

    async function onSend() {
        busy.value = true;
        try {
            const body =
                sendDeadline.value !== ''
                    ? { response_deadline: new Date(sendDeadline.value).toISOString() }
                    : {};
            rfq.value = await sendRfq(id.value, body);
            toast.add({ title: t('rfq.send'), color: 'green' });
            await refresh();
        } finally {
            busy.value = false;
        }
    }

    async function onEvaluate() {
        busy.value = true;
        try {
            rfq.value = await beginEvaluation(id.value);
            toast.add({ title: t('rfq.begin_eval'), color: 'green' });
            await refresh();
        } finally {
            busy.value = false;
        }
    }

    async function onClose() {
        busy.value = true;
        try {
            rfq.value = await closeRfq(id.value);
            toast.add({ title: t('rfq.close'), color: 'green' });
            await refresh();
        } finally {
            busy.value = false;
        }
    }

    async function onAccept(qid: number) {
        busy.value = true;
        try {
            rfq.value = await acceptQuotation(id.value, qid);
            toast.add({ title: t('rfq.accept'), color: 'green' });
            await refresh();
        } finally {
            busy.value = false;
        }
    }
</script>

<template>
    <div class="space-y-6">
        <div v-if="loading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <template v-else-if="rfq">
            <div class="space-y-4">
                <div>
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    >
                        {{ rfq.title }}
                    </h1>
                    <div class="mt-2">
                        <RfqStatusBadge :status="rfq.status" :status-label="rfq.status_label" />
                    </div>
                </div>
                <div v-if="rfq.status === 'draft'" class="max-w-md">
                    <UFormGroup :label="$t('rfq.field_deadline')">
                        <UInput v-model="sendDeadline" type="datetime-local" />
                    </UFormGroup>
                </div>
                <div class="flex flex-wrap gap-2">
                    <UButton
                        v-if="rfq.status === 'draft'"
                        color="primary"
                        :loading="busy"
                        size="sm"
                        @click="onSend"
                    >
                        {{ $t('rfq.send') }}
                    </UButton>
                    <UButton
                        v-if="rfq.status === 'quoting'"
                        color="primary"
                        variant="soft"
                        :loading="busy"
                        size="sm"
                        @click="onEvaluate"
                    >
                        {{ $t('rfq.begin_eval') }}
                    </UButton>
                    <UButton
                        v-if="rfq.status === 'awarded'"
                        variant="soft"
                        color="gray"
                        :loading="busy"
                        size="sm"
                        @click="onClose"
                    >
                        {{ $t('rfq.close') }}
                    </UButton>
                    <UButton variant="soft" color="gray" :to="localePath('/rfqs')" size="sm">
                        {{ $t('rfq.back_list') }}
                    </UButton>
                    <UButton
                        variant="soft"
                        color="gray"
                        :to="localePath(`/rfqs/${rfq.id}/compare`)"
                        size="sm"
                    >
                        {{ $t('rfq.compare_link') }}
                    </UButton>
                </div>
            </div>

            <UCard
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <p v-if="rfq.description" class="text-sm text-[#4d4d4d]">
                    {{ rfq.description }}
                </p>
                <ul v-if="rfq.items?.length" class="mt-4 space-y-2 text-sm">
                    <li v-for="it in rfq.items" :key="it.id" class="flex justify-between gap-2">
                        <span>{{ it.description }}</span>
                        <span class="tabular-nums text-[#666666]"
                            >{{ it.quantity }} {{ it.unit }}</span
                        >
                    </li>
                </ul>
            </UCard>

            <div v-if="quotations.length">
                <h2 class="mb-2 text-lg font-semibold text-[#171717] dark:text-white">
                    {{ $t('rfq.quotations_heading') }}
                </h2>
                <div class="space-y-3">
                    <UCard
                        v-for="q in quotations"
                        :key="q.id"
                        class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="font-medium">
                                    {{
                                        q.supplier_profile?.company_name_ar ||
                                        q.supplier_profile?.company_name_en ||
                                        $t('rfq.compare_unknown_supplier')
                                    }}
                                </p>
                                <p class="text-sm text-[#666666] tabular-nums">
                                    {{ q.total_price }} — {{ q.status }}
                                </p>
                            </div>
                            <UButton
                                v-if="rfq.status === 'evaluation'"
                                size="sm"
                                color="primary"
                                :loading="busy"
                                @click="onAccept(q.id)"
                            >
                                {{ $t('rfq.accept') }}
                            </UButton>
                        </div>
                    </UCard>
                </div>
            </div>
        </template>
    </div>
</template>
