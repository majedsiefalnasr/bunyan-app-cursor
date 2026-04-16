<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const { t } = useI18n();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface MediaRow {
        id: number;
        collection: string;
        original_filename: string;
        mime_type: string;
        url: string;
        thumb_url: string | null;
        size_bytes: number;
        is_temporary: boolean;
        created_at?: string;
    }

    const items = ref<MediaRow[]>([]);
    const isLoading = ref(true);
    const isUploading = ref(false);
    const fileInput = ref<HTMLInputElement | null>(null);
    const isDragging = ref(false);

    function extractList(res: unknown): MediaRow[] {
        const root = res as { data?: unknown };
        const d = root.data;
        if (Array.isArray(d)) {
            return d as MediaRow[];
        }
        if (
            d &&
            typeof d === 'object' &&
            'data' in d &&
            Array.isArray((d as { data: MediaRow[] }).data)
        ) {
            return (d as { data: MediaRow[] }).data;
        }
        return [];
    }

    async function loadMedia() {
        isLoading.value = true;
        try {
            const res = await apiFetch<unknown>('/v1/media?per_page=24');
            items.value = extractList(res);
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(loadMedia);

    async function uploadFile(file: File | null | undefined) {
        if (!file) {
            return;
        }
        isUploading.value = true;
        try {
            const body = new FormData();
            body.append('file', file);
            body.append('collection', 'library');
            await apiFetch('/v1/media/upload', { method: 'POST', body });
            await loadMedia();
        } finally {
            isUploading.value = false;
        }
    }

    function onFileChange(e: Event) {
        const input = e.target as HTMLInputElement;
        const file = input.files?.[0];
        void uploadFile(file);
        input.value = '';
    }

    function onDrop(e: DragEvent) {
        isDragging.value = false;
        const file = e.dataTransfer?.files?.[0];
        void uploadFile(file);
    }
</script>

<template>
    <UContainer class="py-8">
        <div class="mb-8 flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">
                {{ t('media.title') }}
            </h1>
            <p class="text-sm text-[#666666]">
                {{ t('media.subtitle') }}
            </p>
        </div>

        <UCard class="mb-8 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]">
            <div class="p-6">
                <div
                    class="flex min-h-[140px] cursor-pointer flex-col items-center justify-center gap-3 rounded-lg bg-[#fafafa] p-6 text-center transition"
                    :class="isDragging ? 'ring-2 ring-[#0a72ef] ring-offset-2' : ''"
                    role="button"
                    tabindex="0"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop"
                    @click="fileInput?.click()"
                    @keydown.enter.prevent="fileInput?.click()"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        class="sr-only"
                        :aria-label="t('media.upload_aria')"
                        @change="onFileChange"
                    />
                    <span class="text-sm font-medium text-[#171717]">{{
                        t('media.drop_label')
                    }}</span>
                    <UButton :loading="isUploading" color="neutral" variant="solid">
                        {{ t('media.choose_file') }}
                    </UButton>
                </div>
            </div>
        </UCard>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ t('media.loading') }}
        </div>

        <div v-else-if="!items.length" class="text-sm text-[#666666]">
            {{ t('media.empty') }}
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <UCard
                v-for="m in items"
                :key="m.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
            >
                <div class="p-4">
                    <div
                        class="mb-3 aspect-video overflow-hidden rounded-md bg-[#fafafa] shadow-[rgb(235,235,235)_0px_0px_0px_1px]"
                    >
                        <img
                            v-if="m.mime_type.startsWith('image/')"
                            :src="m.thumb_url || m.url"
                            :alt="m.original_filename"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="flex h-full items-center justify-center text-xs text-[#666666]"
                        >
                            {{ m.mime_type }}
                        </div>
                    </div>
                    <p class="truncate text-sm font-medium text-[#171717]">
                        {{ m.original_filename }}
                    </p>
                    <p class="text-xs text-[#666666]">{{ m.collection }} · {{ m.size_bytes }} B</p>
                    <div class="mt-3">
                        <UButton
                            size="xs"
                            variant="soft"
                            color="neutral"
                            :to="m.url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ t('media.open') }}
                        </UButton>
                    </div>
                </div>
            </UCard>
        </div>

        <div class="mt-8">
            <UButton variant="ghost" color="neutral" :to="localePath('/dashboard')">
                {{ t('media.back_dashboard') }}
            </UButton>
        </div>
    </UContainer>
</template>
