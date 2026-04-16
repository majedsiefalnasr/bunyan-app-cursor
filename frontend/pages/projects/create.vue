<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const { t } = useI18n();
    const localePath = useLocalePath();
    const router = useRouter();
    const { apiFetch } = useApi();

    const step = ref(0);
    const name = ref('');
    const budget = ref('');
    const location = ref('');
    const teamNote = ref('');
    const phasesNote = ref('');
    const submitting = ref(false);

    const steps = computed(() => [
        t('projects.wizard_step_info'),
        t('projects.wizard_step_team'),
        t('projects.wizard_step_phases'),
        t('projects.wizard_step_confirm'),
    ]);

    const currentStepTitle = computed(() => steps.value[step.value] ?? '');

    function nextStep() {
        if (step.value < steps.value.length - 1) {
            step.value += 1;
        }
    }

    function prevStep() {
        if (step.value > 0) {
            step.value -= 1;
        }
    }

    async function submitProject() {
        submitting.value = true;
        try {
            const res = await apiFetch<{ data: { id: number } }>('/v1/projects', {
                method: 'POST',
                body: {
                    name: name.value,
                    budget: Number(budget.value),
                    location: location.value,
                },
            });
            const id = res.data?.id;
            if (id) {
                await router.push(localePath(`/projects/${id}`));
            }
        } finally {
            submitting.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-lg space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('projects.wizard_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('projects.wizard_subtitle') }}
            </p>
            <p class="mt-2 text-xs text-[#666666]">
                {{ currentStepTitle }} ({{ step + 1 }}/{{ steps.length }})
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div v-if="step === 0" class="space-y-4">
                <UFormGroup :label="$t('projects.name_label')" name="name">
                    <UInput
                        v-model="name"
                        data-testid="project-name"
                        class="w-full"
                        autocomplete="organization"
                    />
                </UFormGroup>
                <UFormGroup :label="$t('projects.budget_label')" name="budget">
                    <UInput v-model="budget" type="number" min="0" step="0.01" class="w-full" />
                </UFormGroup>
                <UFormGroup :label="$t('projects.location_label')" name="location">
                    <UInput v-model="location" class="w-full" autocomplete="street-address" />
                </UFormGroup>
            </div>

            <div v-else-if="step === 1" class="space-y-3">
                <p class="text-sm text-[#4d4d4d]">{{ $t('projects.wizard_team_hint') }}</p>
                <UFormGroup :label="$t('projects.wizard_team_notes')" name="team_note">
                    <UTextarea v-model="teamNote" class="w-full" :rows="4" />
                </UFormGroup>
            </div>

            <div v-else-if="step === 2" class="space-y-3">
                <p class="text-sm text-[#4d4d4d]">{{ $t('projects.wizard_phases_hint') }}</p>
                <UFormGroup :label="$t('projects.wizard_phases_notes')" name="phases_note">
                    <UTextarea v-model="phasesNote" class="w-full" :rows="4" />
                </UFormGroup>
            </div>

            <div v-else class="space-y-3 text-sm text-[#4d4d4d]">
                <p>
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        $t('projects.name_label')
                    }}</span
                    >: {{ name }}
                </p>
                <p>
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        $t('projects.budget_label')
                    }}</span
                    >: {{ budget }}
                </p>
                <p>
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        $t('projects.location_label')
                    }}</span
                    >: {{ location }}
                </p>
            </div>
        </UCard>

        <div class="flex flex-wrap justify-between gap-2">
            <UButton
                v-if="step > 0"
                variant="soft"
                color="neutral"
                data-testid="wizard-back"
                @click="prevStep"
            >
                {{ $t('projects.wizard_back') }}
            </UButton>
            <div v-else />
            <UButton
                v-if="step < steps.length - 1"
                data-testid="wizard-next"
                class="font-medium"
                :disabled="step === 0 && (!name || !budget || !location)"
                @click="nextStep"
            >
                {{ $t('projects.wizard_next') }}
            </UButton>
            <UButton
                v-else
                data-testid="wizard-submit"
                color="primary"
                class="font-medium"
                :loading="submitting"
                :disabled="!name || !budget || !location"
                @click="submitProject"
            >
                {{ $t('projects.wizard_submit') }}
            </UButton>
        </div>
    </div>
</template>
