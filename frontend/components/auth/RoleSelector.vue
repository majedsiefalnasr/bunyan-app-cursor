<script setup lang="ts">
    import type { UserRole } from '~/types/auth';

    /** UI-only role choice for future registration flows; API currently assigns role server-side. */
    const props = defineProps<{
        modelValue: UserRole | null;
    }>();

    const emit = defineEmits<{
        'update:modelValue': [value: UserRole | null];
    }>();

    function selectRole(role: Extract<UserRole, 'customer' | 'contractor'>) {
        emit('update:modelValue', role);
    }

    const options = computed(() => [
        {
            key: 'customer' as const,
            icon: 'i-heroicons-user',
            title: 'auth.role_customer',
            description: 'auth.role_customer_description',
        },
        {
            key: 'contractor' as const,
            icon: 'i-heroicons-wrench-screwdriver',
            title: 'auth.role_contractor',
            description: 'auth.role_contractor_description',
        },
    ]);
</script>

<template>
    <UFormGroup :label="$t('auth.account_type')" name="account_type">
        <div role="radiogroup" class="grid grid-cols-1 gap-3">
            <button
                v-for="opt in options"
                :key="opt.key"
                type="button"
                class="group relative flex w-full flex-col gap-2 rounded-lg bg-white p-4 text-start shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] transition hover:bg-[#fafafa] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0a72ef] focus-visible:ring-offset-2 focus-visible:ring-offset-[#fafafa] dark:bg-[#171717] dark:hover:bg-[#1f1f1f] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)] dark:focus-visible:ring-offset-[#0a0a0a]"
                :class="
                    props.modelValue === opt.key
                        ? 'ring-2 ring-[#0a72ef] ring-offset-2 ring-offset-[#fafafa] dark:ring-offset-[#0a0a0a]'
                        : ''
                "
                role="radio"
                :aria-checked="props.modelValue === opt.key"
                :data-testid="`role-${opt.key}`"
                @click="selectRole(opt.key)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-md bg-[#fafafa] text-[#171717] shadow-[rgb(235,235,235)_0px_0px_0px_1px] dark:bg-[#0a0a0a] dark:text-white dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.08)]"
                        >
                            <UIcon :name="opt.icon" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-[#171717] dark:text-white">
                                {{ $t(opt.title) }}
                            </div>
                            <div class="mt-0.5 truncate text-xs text-[#666666]">
                                {{ $t(opt.description) }}
                            </div>
                        </div>
                    </div>
                </div>
            </button>
        </div>
    </UFormGroup>
</template>
