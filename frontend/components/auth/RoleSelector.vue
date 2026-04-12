<script setup lang="ts">
    import type { UserRole } from '~/types/auth';

    /** UI-only role choice for future registration flows; API currently assigns role server-side. */
    const model = defineModel<UserRole | null>({ default: null });
</script>

<template>
    <UFormField :label="$t('auth.account_type')" name="account_type">
        <div class="grid grid-cols-2 gap-2">
            <UButton
                type="button"
                block
                :color="model === 'customer' ? 'primary' : 'gray'"
                :variant="model === 'customer' ? 'solid' : 'outline'"
                @click="model = 'customer'"
            >
                {{ $t('auth.role_customer') }}
            </UButton>
            <UButton
                type="button"
                block
                :color="model === 'contractor' ? 'primary' : 'gray'"
                :variant="model === 'contractor' ? 'solid' : 'outline'"
                @click="model = 'contractor'"
            >
                {{ $t('auth.role_contractor') }}
            </UButton>
        </div>
    </UFormField>
</template>
