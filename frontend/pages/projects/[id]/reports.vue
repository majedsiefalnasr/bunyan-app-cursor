<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const { apiFetch } = useApi();
    const toast = useToast();
    const { t } = useI18n();
    const { hasRole } = useAuth();

    const projectId = computed(() => Number(route.params.id));

    interface ReportItem {
        id: number;
        project_id: number;
        title: string;
        content: string;
        attachments?: string[] | null;
        status: string;
        created_by?: number;
        created_at?: string;
    }

    const canCreateReport = computed(() => hasRole('field_engineer', 'admin'));

    const isLoading = ref(false);
    const loadError = ref<string | null>(null);
    const reports = ref<ReportItem[]>([]);

    const isSubmitting = ref(false);
    const isCreateModalOpen = ref(false);
    const form = reactive({
        title: '',
        content: '',
    });
    const files = ref<File[]>([]);

    async function loadReports() {
        isLoading.value = true;
        loadError.value = null;
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 8000);
        try {
            const res = await apiFetch<{ data: { data?: ReportItem[] } | ReportItem[] }>(
                `/v1/reports?project_id=${encodeURIComponent(String(projectId.value))}`,
                { signal: controller.signal }
            );
            const data = res.data as unknown;
            if (Array.isArray(data)) {
                reports.value = data as ReportItem[];
            } else if (
                data &&
                typeof data === 'object' &&
                'data' in (data as Record<string, unknown>)
            ) {
                const inner = (data as { data?: unknown }).data;
                reports.value = Array.isArray(inner) ? (inner as ReportItem[]) : [];
            } else {
                reports.value = [];
            }
        } catch (e: unknown) {
            reports.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            clearTimeout(timer);
            isLoading.value = false;
        }
    }

    async function submit() {
        if (!form.title || !form.content) return;
        isSubmitting.value = true;
        try {
            const attachmentUrls: string[] = [];
            for (const file of files.value) {
                const fd = new FormData();
                fd.append('file', file);
                fd.append('collection', 'report');
                fd.append('mediable_type', 'App\\Models\\Project');
                fd.append('mediable_id', String(projectId.value));
                const upload = await apiFetch<{ data: { url?: string | null } }>(
                    '/v1/media/upload',
                    {
                        method: 'POST',
                        body: fd,
                    }
                );
                const url = (upload.data?.url ?? '').toString().trim();
                if (url) attachmentUrls.push(url);
            }

            await apiFetch('/v1/reports', {
                method: 'POST',
                body: {
                    project_id: projectId.value,
                    title: form.title,
                    content: form.content,
                    attachments: attachmentUrls.length ? attachmentUrls : undefined,
                },
            });
            form.title = '';
            form.content = '';
            files.value = [];
            isCreateModalOpen.value = false;
            toast.add({ title: t('reports.create_success'), color: 'green' });
            await loadReports();
        } finally {
            isSubmitting.value = false;
        }
    }

    function openCreateReport() {
        if (!canCreateReport.value) return;
        form.title = '';
        form.content = '';
        files.value = [];
        isCreateModalOpen.value = true;
    }

    onMounted(() => {
        void loadReports();
    });
</script>

<template>
    <div class="space-y-6">
        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="font-medium text-[#171717] dark:text-white">
                            {{ $t('reports.title') }}
                        </span>
                        <UBadge color="gray" variant="soft">{{ reports.length }}</UBadge>
                    </div>
                    <UButton
                        v-if="canCreateReport"
                        color="primary"
                        variant="solid"
                        icon="i-heroicons-plus"
                        @click="openCreateReport"
                    >
                        {{ $t('reports.create_title') }}
                    </UButton>
                </div>
            </template>

            <div v-if="isLoading" class="text-sm text-[#666666]">{{ $t('shell.loading') }}</div>
            <UAlert
                v-else-if="loadError"
                color="red"
                variant="soft"
                :title="$t('reports.load_error')"
                :description="loadError"
            />
            <p v-else-if="reports.length === 0" class="text-sm text-[#666666]">
                {{ $t('reports.empty') }}
            </p>
            <ul v-else class="space-y-3">
                <li v-for="r in reports" :key="r.id">
                    <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                        <template #header>
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-[#171717] dark:text-white">{{
                                    r.title
                                }}</span>
                                <UBadge color="gray" variant="soft">{{ r.status }}</UBadge>
                            </div>
                        </template>
                        <p class="whitespace-pre-wrap text-sm text-[#4d4d4d]">{{ r.content }}</p>
                        <div
                            v-if="r.attachments && r.attachments.length"
                            class="mt-3 grid gap-2 sm:grid-cols-2"
                        >
                            <a
                                v-for="(u, idx) in r.attachments"
                                :key="`${r.id}-${idx}`"
                                :href="u"
                                target="_blank"
                                rel="noreferrer"
                                class="group relative overflow-hidden rounded-md bg-[#fafafa] shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:bg-[#0a0a0a]"
                            >
                                <img
                                    v-if="u.match(/\\.(png|jpe?g|gif|webp)(\\?.*)?$/i)"
                                    :src="u"
                                    :alt="r.title"
                                    class="aspect-[16/9] w-full object-cover transition group-hover:scale-[1.01]"
                                    loading="lazy"
                                />
                                <div
                                    v-else
                                    class="flex aspect-[16/9] w-full items-center justify-center text-[#a3a3a3] dark:text-[#525252]"
                                >
                                    <UIcon name="i-heroicons-paper-clip" class="h-7 w-7" />
                                </div>
                            </a>
                        </div>
                    </UCard>
                </li>
            </ul>
        </UCard>

        <UAlert
            v-if="!canCreateReport"
            color="amber"
            variant="soft"
            :title="$t('reports.role_hint')"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        />

        <UModal v-model="isCreateModalOpen">
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <template #header>
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium text-[#171717] dark:text-white">
                            {{ $t('reports.create_title') }}
                        </span>
                        <UButton
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-x-mark"
                            @click="isCreateModalOpen = false"
                        />
                    </div>
                </template>

                <div class="space-y-4">
                    <UFormGroup :label="$t('reports.fields.title')" name="title">
                        <UInput v-model="form.title" />
                    </UFormGroup>
                    <UFormGroup :label="$t('reports.fields.content')" name="content">
                        <UTextarea v-model="form.content" :rows="5" />
                    </UFormGroup>
                    <UFormGroup :label="$t('reports.fields.attachments')" name="attachments">
                        <input
                            type="file"
                            multiple
                            accept="image/*,video/*"
                            class="block w-full text-sm text-[#666666] file:mr-3 file:rounded-md file:border-0 file:bg-[#f4f4f5] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[#171717] hover:file:bg-[#eaeaea] dark:file:bg-[#262626] dark:file:text-white"
                            @change="
                                (e) => {
                                    const input = e.target as HTMLInputElement;
                                    files = input.files ? Array.from(input.files) : [];
                                }
                            "
                        />
                        <p v-if="files.length" class="mt-2 text-xs text-[#666666]">
                            {{ files.length }} {{ $t('reports.files_selected') }}
                        </p>
                    </UFormGroup>
                    <div class="flex justify-end gap-2">
                        <UButton
                            color="gray"
                            variant="outline"
                            :disabled="isSubmitting"
                            @click="isCreateModalOpen = false"
                        >
                            {{ $t('common.cancel') }}
                        </UButton>
                        <UButton
                            color="primary"
                            :loading="isSubmitting"
                            :disabled="!form.title || !form.content"
                            @click="submit"
                        >
                            {{ $t('common.save') }}
                        </UButton>
                    </div>
                </div>
            </UCard>
        </UModal>
    </div>
</template>
