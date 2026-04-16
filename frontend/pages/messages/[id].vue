<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface MessageRow {
        id: number;
        body: string | null;
        sender_id: number;
        created_at?: string;
    }

    const conversationId = computed(() => route.params.id as string);
    const messages = ref<MessageRow[]>([]);
    const body = ref('');
    const isLoading = ref(true);
    const isSending = ref(false);

    async function loadMessages() {
        isLoading.value = true;
        try {
            const res = await apiFetch<{ data: { data?: MessageRow[] } | MessageRow[] }>(
                `/v1/conversations/${conversationId.value}/messages`
            );
            const payload = res.data as { data?: MessageRow[] } | MessageRow[];
            const list = Array.isArray(payload) ? payload : (payload.data ?? []);
            messages.value = [...list].reverse();
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(async () => {
        await apiFetch(`/v1/conversations/${conversationId.value}/read`, { method: 'PUT' }).catch(
            () => {}
        );
        await loadMessages();
    });

    async function sendMessage() {
        const text = body.value.trim();
        if (!text) {
            return;
        }
        isSending.value = true;
        try {
            await apiFetch(`/v1/conversations/${conversationId.value}/messages`, {
                method: 'POST',
                body: { body: text },
            });
            body.value = '';
            await loadMessages();
        } finally {
            isSending.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto flex max-w-3xl flex-col gap-4">
        <div class="flex items-center justify-between gap-3">
            <UButton :to="localePath('/messages')" variant="ghost" color="neutral">
                {{ $t('messages.back') }}
            </UButton>
            <h1
                class="text-xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.05em"
            >
                {{ $t('messages.thread_title') }}
            </h1>
        </div>

        <UCard class="min-h-[240px] shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div v-if="isLoading" class="text-sm text-[#666666]">
                {{ $t('shell.loading') }}
            </div>
            <ul v-else class="space-y-3">
                <li
                    v-for="m in messages"
                    :key="m.id"
                    class="rounded-lg bg-[#fafafa] px-3 py-2 text-sm text-[#171717] shadow-[0px_0px_0px_1px_rgba(0,0,0,0.06)]"
                >
                    <span class="text-xs text-[#808080]">#{{ m.sender_id }}</span>
                    <p class="mt-1 whitespace-pre-wrap">{{ m.body }}</p>
                </li>
            </ul>
        </UCard>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <UFormGroup :label="$t('messages.composer_placeholder')" class="flex-1">
                    <UTextarea
                        v-model="body"
                        :rows="3"
                        :placeholder="$t('messages.composer_placeholder')"
                    />
                </UFormGroup>
                <UButton color="primary" :loading="isSending" @click="sendMessage">
                    {{ $t('messages.send') }}
                </UButton>
            </div>
        </UCard>
    </div>
</template>
