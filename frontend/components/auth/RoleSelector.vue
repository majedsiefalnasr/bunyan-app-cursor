<script setup lang="ts">
    import type { UserRole } from '~/types/auth';

    /** UI-only role choice for future registration flows; API currently assigns role server-side. */
    const model = defineModel<UserRole | null>({ default: null });

    function selectRole(role: Extract<UserRole, 'customer' | 'contractor'>) {
        model.value = role;
    }
</script>

<template>
    <UFormField :label="$t('auth.account_type')" name="account_type">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="flex flex-col gap-1">
                <UButton
                    type="button"
                    block
                    :color="model === 'customer' ? 'primary' : 'gray'"
                    :variant="model === 'customer' ? 'solid' : 'outline'"
                    data-testid="role-customer"
                    @click="selectRole('customer')"
                >
                    {{ $t('auth.role_customer') }}
                </UButton>
                <p class="text-xs leading-relaxed text-[#666666]">
                    {{ $t('auth.role_customer_description') }}
                </p>
            </div>
            <div class="flex flex-col gap-1">
                <UButton
                    type="button"
                    block
                    :color="model === 'contractor' ? 'primary' : 'gray'"
                    :variant="model === 'contractor' ? 'solid' : 'outline'"
                    data-testid="role-contractor"
                    @click="selectRole('contractor')"
                >
                    {{ $t('auth.role_contractor') }}
                </UButton>
                <p class="text-xs leading-relaxed text-[#666666]">
                    {{ $t('auth.role_contractor_description') }}
                </p>
            </div>
        </div>
    </UFormField>
</template>
