<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const { t } = useI18n();
    const route = useRoute();
    const { apiFetch } = useApi();
    const auth = useAuthStore();
    const config = useRuntimeConfig();

    interface DocumentRow {
        id: number;
        title: string;
        category: string;
        version: number;
        original_filename: string;
        mime_type: string;
        size_bytes: number;
        created_at?: string;
    }

    interface VersionRow {
        id: number;
        version: number;
        size_bytes: number;
        created_at?: string;
    }

    const DOCUMENT_CATEGORIES = [
        { value: 'blueprint', labelKey: 'projects.documents_cat_blueprint' },
        { value: 'contract', labelKey: 'projects.documents_cat_contract' },
        { value: 'permit', labelKey: 'projects.documents_cat_permit' },
        { value: 'invoice', labelKey: 'projects.documents_cat_invoice' },
        { value: 'photo', labelKey: 'projects.documents_cat_photo' },
        { value: 'report', labelKey: 'projects.documents_cat_report' },
        { value: 'other', labelKey: 'projects.documents_cat_other' },
    ] as const;

    const items = ref<DocumentRow[]>([]);
    const isLoading = ref(true);
    const isUploading = ref(false);
    const fileInput = ref<HTMLInputElement | null>(null);
    const isDragging = ref(false);
    const title = ref('');
    const category = ref<string>('photo');
    const versionFor = ref<DocumentRow | null>(null);
    const versionsOpen = ref(false);
    const versions = ref<VersionRow[]>([]);
    const versionsLoading = ref(false);

    function extractList(res: unknown): DocumentRow[] {
        const root = res as { data?: unknown };
        const d = root.data;
        if (Array.isArray(d)) {
            return d as DocumentRow[];
        }
        if (
            d &&
            typeof d === 'object' &&
            'data' in d &&
            Array.isArray((d as { data: DocumentRow[] }).data)
        ) {
            return (d as { data: DocumentRow[] }).data;
        }
        return [];
    }

    async function loadDocuments() {
        isLoading.value = true;
        try {
            const id = route.params.id;
            const res = await apiFetch<unknown>(`/v1/projects/${id}/documents?per_page=50`);
            items.value = extractList(res);
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(loadDocuments);

    async function uploadFile(file: File | null | undefined) {
        if (!file || !title.value.trim()) {
            return;
        }
        isUploading.value = true;
        try {
            const id = route.params.id;
            const body = new FormData();
            body.append('file', file);
            body.append('title', title.value.trim());
            body.append('category', category.value);
            await apiFetch(`/v1/projects/${id}/documents`, { method: 'POST', body });
            title.value = '';
            await loadDocuments();
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

    async function removeDocument(row: DocumentRow) {
        await apiFetch(`/v1/documents/${row.id}`, { method: 'DELETE' });
        await loadDocuments();
    }

    async function openVersions(row: DocumentRow) {
        versionFor.value = row;
        versionsOpen.value = true;
        versionsLoading.value = true;
        versions.value = [];
        try {
            const res = await apiFetch<unknown>(`/v1/documents/${row.id}/versions`);
            const root = res as { data?: VersionRow[] | { data: VersionRow[] } };
            let d = root.data;
            if (
                d &&
                typeof d === 'object' &&
                'data' in d &&
                Array.isArray((d as { data: VersionRow[] }).data)
            ) {
                d = (d as { data: VersionRow[] }).data;
            }
            versions.value = Array.isArray(d) ? d : [];
        } finally {
            versionsLoading.value = false;
        }
    }

    async function downloadDocument(row: DocumentRow) {
        const token = auth.token;
        if (!token) {
            return;
        }
        const baseRaw = (config.public.apiBaseUrl as string) || '';
        const path = `/v1/documents/${row.id}/download`;
        const url =
            baseRaw === ''
                ? path
                : `${baseRaw.replace(/\/$/, '')}${path.startsWith('/') ? path : `/${path}`}`;
        const res = await fetch(url, {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: '*/*',
            },
        });
        if (!res.ok) {
            return;
        }
        const blob = await res.blob();
        const href = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = href;
        a.download = row.original_filename;
        a.click();
        URL.revokeObjectURL(href);
    }
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('projects.documents_title') }}
            </h1>
            <p class="mt-2 text-sm text-[#4d4d4d]">
                {{ $t('projects.documents_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="font-medium text-[#171717] dark:text-white">{{
                    $t('projects.documents_upload')
                }}</span>
            </template>
            <div class="space-y-4">
                <UFormGroup :label="$t('projects.documents_title_label')">
                    <UInput v-model="title" class="w-full" />
                </UFormGroup>
                <UFormGroup :label="$t('projects.documents_category_label')">
                    <USelect
                        v-model="category"
                        :options="
                            DOCUMENT_CATEGORIES.map((c) => ({
                                value: c.value,
                                label: t(c.labelKey),
                            }))
                        "
                        option-attribute="label"
                        value-attribute="value"
                        class="w-full"
                    />
                </UFormGroup>
                <div
                    class="rounded-lg bg-[#fafafa] p-6 text-center text-sm text-[#666666] shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop"
                >
                    <p>{{ $t('projects.documents_drop_hint') }}</p>
                    <input ref="fileInput" type="file" class="hidden" @change="onFileChange" />
                    <UButton class="mt-3" :loading="isUploading" @click="fileInput?.click()">
                        {{ $t('projects.documents_choose_file') }}
                    </UButton>
                </div>
            </div>
        </UCard>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="font-medium text-[#171717] dark:text-white">{{
                    $t('projects.documents_list')
                }}</span>
            </template>
            <div v-if="isLoading" class="text-sm text-[#666666]">
                {{ $t('shell.loading') }}
            </div>
            <p v-else-if="items.length === 0" class="text-sm text-[#666666]">
                {{ $t('projects.documents_empty') }}
            </p>
            <ul v-else class="divide-y divide-[#ebebeb] text-sm text-[#4d4d4d]">
                <li
                    v-for="row in items"
                    :key="row.id"
                    class="flex flex-wrap items-center gap-2 py-3"
                >
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-[#171717] dark:text-white">
                            {{ row.title }}
                        </div>
                        <div class="text-xs text-[#666666]">
                            {{ row.category }} · v{{ row.version }} · {{ row.original_filename }}
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <UButton size="xs" variant="soft" color="gray" @click="openVersions(row)">
                            {{ $t('projects.documents_versions') }}
                        </UButton>
                        <UButton
                            size="xs"
                            variant="soft"
                            color="gray"
                            @click="downloadDocument(row)"
                        >
                            {{ $t('projects.documents_download') }}
                        </UButton>
                        <UButton size="xs" color="red" variant="soft" @click="removeDocument(row)">
                            {{ $t('projects.documents_delete') }}
                        </UButton>
                    </div>
                </li>
            </ul>
        </UCard>

        <UModal v-model="versionsOpen">
            <UCard>
                <template #header>
                    <span class="font-medium text-[#171717] dark:text-white">
                        {{ $t('projects.documents_versions_title') }}
                        <span v-if="versionFor" class="ms-1 text-sm font-normal text-[#666666]">
                            — {{ versionFor.title }}
                        </span>
                    </span>
                </template>
                <div v-if="versionsLoading" class="text-sm text-[#666666]">
                    {{ $t('shell.loading') }}
                </div>
                <ul v-else class="space-y-2 text-sm text-[#4d4d4d]">
                    <li v-for="v in versions" :key="v.id" class="flex justify-between gap-2">
                        <span>v{{ v.version }}</span>
                        <span class="text-xs text-[#666666]">{{ v.size_bytes }} B</span>
                    </li>
                </ul>
            </UCard>
        </UModal>
    </div>
</template>
