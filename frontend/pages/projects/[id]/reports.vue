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
    const attachmentFileInput = ref<HTMLInputElement | null>(null);
    const isAttachmentDragging = ref(false);

    function isReportMediaFile(file: File): boolean {
        return file.type.startsWith('image/') || file.type.startsWith('video/');
    }

    function onAttachmentFileChange(e: Event) {
        const input = e.target as HTMLInputElement;
        const list = input.files ? Array.from(input.files) : [];
        files.value = list.filter(isReportMediaFile);
        input.value = '';
    }

    function onAttachmentDrop(e: DragEvent) {
        isAttachmentDragging.value = false;
        const list = e.dataTransfer?.files;
        if (!list?.length) {
            return;
        }
        const next = Array.from(list).filter(isReportMediaFile);
        if (!next.length) {
            return;
        }
        files.value = [...files.value, ...next];
    }

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
        const title = form.title.trim();
        const content = form.content.trim();
        if (!title || !content) {
            return;
        }
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
                    title,
                    content,
                    attachments: attachmentUrls.length ? attachmentUrls : undefined,
                },
            });
            form.title = '';
            form.content = '';
            files.value = [];
            isCreateModalOpen.value = false;
            toast.add({ title: t('reports.create_success'), color: 'success' });
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
        <div>
            <h2
                class="text-xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.04em"
            >
                {{ $t('reports.title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4d4d4d]">
                {{ $t('reports.page_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-[#171717] dark:text-white">
                            {{ $t('reports.hub_title') }}
                        </span>
                        <UBadge color="neutral" variant="soft">{{ reports.length }}</UBadge>
                    </div>
                    <UButton
                        v-if="canCreateReport"
                        size="sm"
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
                color="error"
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
                                <UBadge color="neutral" variant="soft">{{ r.status }}</UBadge>
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
            color="warning"
            variant="soft"
            :title="$t('reports.role_hint')"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        />

        <UModal v-model:open="isCreateModalOpen" :close="false">
            <template #content>
                <UCard class="w-full min-w-0 max-w-lg shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <template #header>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <span class="font-medium text-[#171717] dark:text-white">
                                    {{ $t('reports.create_title') }}
                                </span>
                                <p class="text-sm text-[#666666]">
                                    {{ $t('reports.create_subtitle') }}
                                </p>
                            </div>
                            <UButton
                                color="neutral"
                                variant="ghost"
                                icon="i-heroicons-x-mark"
                                class="shrink-0"
                                :aria-label="$t('common.cancel')"
                                @click="isCreateModalOpen = false"
                            />
                        </div>
                    </template>

                    <form
                        id="report-create-form"
                        class="max-h-[min(65vh,36rem)] space-y-6 overflow-y-auto overscroll-contain pe-1 -me-1"
                        @submit.prevent="submit"
                    >
                        <UFormField
                            :label="$t('reports.fields.title')"
                            :description="$t('reports.fields.title_desc')"
                            name="title"
                            required
                            class="min-w-0"
                        >
                            <UInput
                                v-model="form.title"
                                class="w-full"
                                :placeholder="$t('reports.fields.title_placeholder')"
                                autocomplete="off"
                            />
                        </UFormField>
                        <UFormField
                            :label="$t('reports.fields.content')"
                            :description="$t('reports.fields.content_desc')"
                            name="content"
                            required
                            class="min-w-0"
                        >
                            <UTextarea
                                v-model="form.content"
                                :rows="5"
                                autoresize
                                :placeholder="$t('reports.fields.content_placeholder')"
                                class="w-full min-h-32"
                            />
                        </UFormField>
                        <UFormField
                            :label="$t('reports.attach_file_section')"
                            :description="$t('reports.attach_file_hint')"
                            name="attachments"
                            class="min-w-0 border-t border-default pt-6"
                        >
                            <div
                                class="rounded-xl border-2 border-dashed p-6 text-center transition-colors"
                                :class="
                                    isAttachmentDragging
                                        ? 'border-primary bg-primary/5 ring-2 ring-primary/20'
                                        : 'border-default bg-elevated/30 dark:bg-elevated/15'
                                "
                                @dragover.prevent="isAttachmentDragging = true"
                                @dragleave.prevent="isAttachmentDragging = false"
                                @drop.prevent="onAttachmentDrop"
                            >
                                <UIcon
                                    name="i-heroicons-cloud-arrow-up"
                                    class="mx-auto h-10 w-10 text-[#666666]"
                                    aria-hidden="true"
                                />
                                <p class="mt-3 text-sm text-[#4d4d4d]">
                                    {{ $t('reports.attach_drop_hint') }}
                                </p>
                                <input
                                    id="report-attachment-input"
                                    ref="attachmentFileInput"
                                    type="file"
                                    multiple
                                    accept="image/*,video/*"
                                    class="hidden"
                                    :aria-label="$t('reports.attach_file_section')"
                                    @change="onAttachmentFileChange"
                                />
                                <UButton
                                    type="button"
                                    class="mt-4 w-full justify-center font-medium sm:w-auto"
                                    aria-controls="report-attachment-input"
                                    @click="attachmentFileInput?.click()"
                                >
                                    {{ $t('projects.documents_choose_file') }}
                                </UButton>
                                <p v-if="files.length" class="mt-2 text-xs text-[#666666]">
                                    {{ files.length }} {{ $t('reports.files_selected') }}
                                </p>
                            </div>
                        </UFormField>
                    </form>

                    <template #footer>
                        <div class="flex flex-wrap justify-end gap-2">
                            <UButton
                                type="button"
                                color="neutral"
                                variant="outline"
                                :disabled="isSubmitting"
                                @click="isCreateModalOpen = false"
                            >
                                {{ $t('common.cancel') }}
                            </UButton>
                            <UButton
                                type="submit"
                                form="report-create-form"
                                color="primary"
                                :loading="isSubmitting"
                                :disabled="!form.title?.trim() || !form.content?.trim()"
                            >
                                {{ $t('common.save') }}
                            </UButton>
                        </div>
                    </template>
                </UCard>
            </template>
        </UModal>
    </div>
</template>
