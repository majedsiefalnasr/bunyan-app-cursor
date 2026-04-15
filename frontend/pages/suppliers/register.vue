<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        requiresAuth: true,
        roles: ['contractor'],
    });

    const localePath = useLocalePath();
    const { apiFetch } = useApi();
    const toast = useToast();

    const companyNameAr = ref('');
    const city = ref('');
    const isSubmitting = ref(false);

    async function onSubmit() {
        isSubmitting.value = true;
        try {
            await apiFetch('/v1/suppliers', {
                method: 'POST',
                body: {
                    company_name_ar: companyNameAr.value,
                    city: city.value || undefined,
                },
            });
            toast.add({
                title: 'تم',
                description: 'تم إرسال طلب ملف المورد',
                color: 'green',
            });
            await navigateTo(localePath('/dashboard'));
        } finally {
            isSubmitting.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-lg space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-[#171717] dark:text-white">
                {{ $t('suppliers.register_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('suppliers.register_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <form class="space-y-4" @submit.prevent="onSubmit">
                <UFormGroup :label="$t('suppliers.company_name_ar')" required>
                    <UInput v-model="companyNameAr" />
                </UFormGroup>
                <UFormGroup :label="$t('suppliers.city')">
                    <UInput v-model="city" />
                </UFormGroup>
                <UButton
                    type="submit"
                    block
                    :loading="isSubmitting"
                    :disabled="!companyNameAr.trim()"
                >
                    {{ $t('suppliers.submit') }}
                </UButton>
            </form>
        </UCard>
    </div>
</template>
