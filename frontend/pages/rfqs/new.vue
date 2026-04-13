<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer'],
    });

    const localePath = useLocalePath();
    const toast = useToast();
    const { createRfq } = useRfqs();

    const title = ref('');
    const responseDeadline = ref<string | undefined>(undefined);
    const items = ref([{ description: '', quantity: 1, unit: 'قطعة' }]);
    const saving = ref(false);

    function addItem() {
        items.value.push({ description: '', quantity: 1, unit: 'قطعة' });
    }

    async function submit() {
        saving.value = true;
        try {
            const created = await createRfq({
                title: title.value,
                response_deadline: responseDeadline.value,
                items: items.value.map((i) => ({
                    description: i.description,
                    quantity: Number(i.quantity),
                    unit: i.unit,
                })),
            });
            toast.add({ title: created.title, color: 'green' });
            await navigateTo(localePath(`/rfqs/${created.id}`));
        } finally {
            saving.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('rfq.new_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('rfq.new_subtitle') }}
            </p>
        </div>

        <UCard
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <form class="space-y-4" @submit.prevent="submit">
                <UFormGroup :label="$t('rfq.field_title')">
                    <UInput v-model="title" required />
                </UFormGroup>
                <UFormGroup :label="$t('rfq.field_deadline')">
                    <UInput v-model="responseDeadline" type="datetime-local" />
                </UFormGroup>

                <div>
                    <p class="mb-2 text-sm font-medium text-[#171717] dark:text-white">
                        {{ $t('rfq.items_heading') }}
                    </p>
                    <div
                        v-for="(it, idx) in items"
                        :key="idx"
                        class="mb-3 grid gap-2 sm:grid-cols-3"
                    >
                        <UFormGroup :label="$t('rfq.item_description')">
                            <UInput v-model="it.description" required />
                        </UFormGroup>
                        <UFormGroup :label="$t('rfq.item_qty')">
                            <UInput
                                v-model.number="it.quantity"
                                type="number"
                                min="0.0001"
                                step="any"
                                required
                            />
                        </UFormGroup>
                        <UFormGroup :label="$t('rfq.item_unit')">
                            <UInput v-model="it.unit" required />
                        </UFormGroup>
                    </div>
                    <UButton type="button" variant="soft" color="gray" @click="addItem">
                        {{ $t('rfq.add_item') }}
                    </UButton>
                </div>

                <div class="flex flex-wrap gap-2">
                    <UButton type="submit" color="primary" :loading="saving">
                        {{ $t('rfq.create_submit') }}
                    </UButton>
                    <UButton type="button" variant="soft" color="gray" :to="localePath('/rfqs')">
                        {{ $t('rfq.back_list') }}
                    </UButton>
                </div>
            </form>
        </UCard>
    </div>
</template>
