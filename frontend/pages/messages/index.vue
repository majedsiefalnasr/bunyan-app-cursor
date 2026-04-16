<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface ParticipantUser {
        id: number;
        name: string;
    }

    interface ConversationRow {
        id: number;
        type: string;
        title: string | null;
        participants?: ParticipantUser[];
        latest_message?: { body: string | null } | null;
        updated_at?: string;
    }

    const conversations = ref<ConversationRow[]>([]);
    const isLoading = ref(true);
    const partnerId = ref<string>('');
    const isCreating = ref(false);

    async function loadList() {
        isLoading.value = true;
        try {
            const res = await apiFetch<{ data: { data?: ConversationRow[] } | ConversationRow[] }>(
                '/v1/conversations'
            );
            const payload = res.data as { data?: ConversationRow[] } | ConversationRow[];
            conversations.value = Array.isArray(payload) ? payload : (payload.data ?? []);
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(loadList);

    function previewTitle(c: ConversationRow): string {
        if (c.title) {
            return c.title;
        }
        const others = (c.participants ?? []).map((p) => p.name).filter(Boolean);
        return others.length ? others.join('، ') : `#${c.id}`;
    }

    async function startDirect() {
        const id = Number.parseInt(partnerId.value, 10);
        if (!Number.isFinite(id) || id < 1) {
            return;
        }
        isCreating.value = true;
        try {
            await apiFetch('/v1/conversations', {
                method: 'POST',
                body: { type: 'direct', participant_ids: [id] },
            });
            partnerId.value = '';
            await loadList();
        } finally {
            isCreating.value = false;
        }
    }
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('messages.list_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('messages.list_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <UFormGroup :label="$t('messages.partner_id_label')" class="flex-1">
                    <UInput
                        v-model="partnerId"
                        type="number"
                        min="1"
                        :placeholder="$t('messages.partner_id_label')"
                    />
                </UFormGroup>
                <UButton color="primary" :loading="isCreating" @click="startDirect">
                    {{ $t('messages.send') }}
                </UButton>
            </div>
            <p class="mt-2 text-xs text-[#808080]">
                {{ $t('messages.partner_hint') }}
            </p>
        </UCard>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="conversations.length === 0" class="text-sm text-[#666666]">
            {{ $t('messages.empty') }}
        </p>

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="c in conversations"
                :key="c.id"
                variant="subtle"
                :title="previewTitle(c)"
                :ui="{
                    container: 'gap-y-2',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
            >
                <div class="space-y-2">
                    <p v-if="c.latest_message?.body" class="line-clamp-2 text-sm text-[#4d4d4d]">
                        {{ c.latest_message.body }}
                    </p>
                    <UButton
                        :to="localePath(`/messages/${c.id}`)"
                        variant="soft"
                        color="gray"
                        size="xs"
                    >
                        {{ $t('messages.thread_title') }}
                    </UButton>
                </div>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
