<script setup lang="ts">
    import { watch } from 'vue';

    const model = defineModel<string>({ default: '' });

    const emit = defineEmits<{
        complete: [value: string];
    }>();

    watch(
        () => model.value,
        (v) => {
            const clean = (v || '').replace(/\D/g, '').slice(0, 6);
            if (clean !== v) {
                model.value = clean;
            }
            if (clean.length === 6) {
                emit('complete', clean);
            }
        }
    );
</script>

<template>
    <UFormGroup :label="$t('auth.otp_label')" name="otp">
        <UInput
            v-model="model"
            maxlength="6"
            inputmode="numeric"
            autocomplete="one-time-code"
            icon="i-heroicons-key"
            size="lg"
            class="text-center tracking-widest"
        />
    </UFormGroup>
</template>
