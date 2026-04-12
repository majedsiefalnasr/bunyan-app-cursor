<script setup lang="ts">
    import type { ProductCatalogFilters } from '~/composables/useProductCatalogQuery';
    import { defaultProductCatalogFilters } from '~/composables/useProductCatalogQuery';

    const model = defineModel<ProductCatalogFilters>({ required: true });

    defineProps<{
        categoryOptions: { id: number; name_ar: string }[];
    }>();

    const emit = defineEmits<{
        apply: [];
    }>();

    function reset() {
        Object.assign(model.value, defaultProductCatalogFilters());
        emit('apply');
    }
</script>

<template>
    <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
        <template #header>
            <span class="font-medium text-[#171717] dark:text-white">{{
                $t('catalog.filters_title')
            }}</span>
        </template>

        <div class="space-y-4">
            <UFormGroup :label="$t('catalog.filter_category')">
                <div class="max-h-48 space-y-2 overflow-y-auto">
                    <label
                        v-for="c in categoryOptions"
                        :key="c.id"
                        class="flex cursor-pointer items-center gap-2 text-sm text-[#171717] dark:text-white"
                    >
                        <UCheckbox
                            :model-value="model.categoryId === c.id"
                            @update:model-value="
                                (checked: boolean) => {
                                    model.categoryId = checked ? c.id : null;
                                }
                            "
                        />
                        <span>{{ c.name_ar }}</span>
                    </label>
                </div>
            </UFormGroup>

            <div class="grid grid-cols-2 gap-2">
                <UFormGroup :label="$t('catalog.filter_min_price')">
                    <UInput v-model="model.minPrice" type="number" />
                </UFormGroup>
                <UFormGroup :label="$t('catalog.filter_max_price')">
                    <UInput v-model="model.maxPrice" type="number" />
                </UFormGroup>
            </div>

            <UCheckbox v-model="model.inStock" :label="$t('catalog.filter_in_stock')" />

            <div class="flex flex-wrap gap-2">
                <UButton color="primary" @click="emit('apply')">{{
                    $t('catalog.filter_apply')
                }}</UButton>
                <UButton color="gray" variant="soft" @click="reset">{{
                    $t('catalog.filter_reset')
                }}</UButton>
            </div>
        </div>
    </UCard>
</template>
