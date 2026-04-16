<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const { t } = useI18n();
    const toast = useToast();
    const { hasRole } = useAuth();
    const { start } = useProjectWorkflow();

    const projectShell = inject(PROJECT_SHELL_KEY)!;
    const projectId = computed(() => String(route.params.id));
    const submitting = ref(false);
    const lastMessage = ref<string | null>(null);

    const canStartWorkflow = computed(() =>
        hasRole('customer', 'contractor', 'supervising_architect', 'admin')
    );

    async function onStart() {
        submitting.value = true;
        lastMessage.value = null;
        try {
            const res = (await start(projectId.value)) as {
                message?: string | null;
            } | null;
            const msg = res?.message?.trim() ? res.message : t('projects.workflow_started');
            lastMessage.value = msg ?? t('projects.workflow_started');
            toast.add({ title: lastMessage.value, color: 'success' });
        } catch {
            lastMessage.value = t('projects.workflow_start_error');
            toast.add({ title: lastMessage.value, color: 'error' });
        } finally {
            submitting.value = false;
        }
    }
</script>

<template>
    <div class="space-y-4">
        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="font-medium text-[#171717] dark:text-white">{{
                    $t('projects.workflow_title')
                }}</span>
            </template>
            <p v-if="projectShell" class="text-sm text-[#4d4d4d]">
                {{ $t('projects.workflow_status_label') }}
                <UBadge class="ms-1" color="neutral" variant="soft">{{
                    projectShell.status
                }}</UBadge>
            </p>
            <p class="mt-3 text-sm text-[#666666]">
                {{ $t('projects.workflow_body') }}
            </p>
            <UButton
                v-if="canStartWorkflow"
                class="mt-4 font-medium"
                :loading="submitting"
                @click="onStart"
            >
                {{ $t('projects.workflow_start') }}
            </UButton>
            <UAlert
                v-else
                class="mt-4"
                color="warning"
                variant="soft"
                :title="$t('projects.workflow_role_hint')"
            />
            <p v-if="lastMessage" class="mt-3 text-xs text-[#666666]">
                {{ lastMessage }}
            </p>
        </UCard>
    </div>
</template>
