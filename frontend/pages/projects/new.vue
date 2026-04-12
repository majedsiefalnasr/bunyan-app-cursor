<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const localePath = useLocalePath();
    const router = useRouter();
    const { apiFetch } = useApi();

    const name = ref('');
    const budget = ref('');
    const location = ref('');
    const submitting = ref(false);

    async function onSubmit() {
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
                {{ $t('projects.new_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('projects.new_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <form class="space-y-4" @submit.prevent="onSubmit">
                <UFormField :label="$t('projects.name_label')">
                    <UInput v-model="name" required />
                </UFormField>
                <UFormField :label="$t('projects.budget_label')">
                    <UInput v-model="budget" type="number" min="0" step="0.01" required />
                </UFormField>
                <UFormField :label="$t('projects.location_label')">
                    <UInput v-model="location" required />
                </UFormField>
                <div class="flex gap-2">
                    <UButton type="submit" color="primary" :loading="submitting">
                        {{ $t('projects.submit') }}
                    </UButton>
                    <UButton :to="localePath('/projects')" variant="soft" color="gray">
                        {{ $t('projects.back_to_list') }}
                    </UButton>
                </div>
            </form>
        </UCard>
    </div>
</template>
