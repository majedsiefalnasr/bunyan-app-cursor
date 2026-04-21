<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth'],
    });

    const localePath = useLocalePath();
    const { listOrders } = useOrders();

    interface OrderRow {
        id: number;
        order_number: string | null;
        status: string;
        total_price?: string;
        total_amount?: number;
        created_at: string | null;
        customer_id?: number;
        items?: Array<{ product_id: number; quantity: number; unit_price: number }>;
    }

    const rows = ref<OrderRow[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);
    const selectedStatus = ref<string>('');
    const searchQuery = ref<string>('');

    const statusColors: Record<string, { label: string; color: string; icon: string }> = {
        pending: { label: 'معلق', color: 'bg-yellow-50', icon: 'i-heroicons-clock' },
        processing: { label: 'قيد المعالجة', color: 'bg-blue-50', icon: 'i-heroicons-arrow-path' },
        shipped: { label: 'تم الشحن', color: 'bg-indigo-50', icon: 'i-heroicons-truck' },
        completed: { label: 'مكتمل', color: 'bg-green-50', icon: 'i-heroicons-check-circle' },
        cancelled: { label: 'ملغي', color: 'bg-red-50', icon: 'i-heroicons-x-circle' },
    };

    const filteredRows = computed(() => {
        let filtered = rows.value;

        if (selectedStatus.value) {
            filtered = filtered.filter((o) => o.status === selectedStatus.value);
        }

        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filtered = filtered.filter((o) => (o.order_number || '').toLowerCase().includes(q));
        }

        return filtered.sort((a, b) => {
            const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
            const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
            return dateB - dateA;
        });
    });

    const formatCurrency = (amount: number | undefined) => {
        if (!amount) return '0 ريال';
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR',
            maximumFractionDigits: 0,
        }).format(amount);
    };

    const getStatusColor = (status: string) => {
        return statusColors[status] || statusColors['pending'];
    };

    onMounted(async () => {
        try {
            const page = await listOrders({ per_page: 100 });
            rows.value = page.data;
        } catch (e: unknown) {
            rows.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#171717] dark:text-white">
                الطلبات
            </h1>
            <p class="mt-2 text-sm text-[#666666]">
                إدارة وتتبع جميع طلبات المواد والمنتجات الخاصة بك
            </p>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <UInput
                v-model="searchQuery"
                placeholder="ابحث برقم الطلب..."
                icon="i-heroicons-magnifying-glass"
                type="search"
            />
            <USelect
                v-model="selectedStatus"
                :options="[
                    { label: 'جميع الحالات', value: '' },
                    { label: 'معلق', value: 'pending' },
                    { label: 'قيد المعالجة', value: 'processing' },
                    { label: 'تم الشحن', value: 'shipped' },
                    { label: 'مكتمل', value: 'completed' },
                    { label: 'ملغي', value: 'cancelled' },
                ]"
                placeholder="فلتر حسب الحالة"
            />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 6" :key="i">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <USkeleton class="h-5 w-32" />
                            <USkeleton class="h-5 w-20" />
                        </div>
                        <USkeleton class="h-4 w-3/4" />
                        <USkeleton class="h-4 w-1/2" />
                    </div>
                </UCard>
            </div>
        </div>

        <!-- Error State -->
        <UAlert
            v-else-if="loadError"
            color="error"
            variant="soft"
            title="خطأ"
            :description="loadError"
        />

        <!-- Empty State -->
        <UCard v-else-if="rows.length === 0" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="text-center py-12">
                <UIcon name="i-heroicons-inbox" class="mx-auto text-4xl text-[#d1d5db] mb-4" />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد طلبات</p>
                <p class="text-sm text-[#666666]">ابدأ بطلب مواد جديد الآن</p>
            </div>
        </UCard>

        <!-- No Results State -->
        <UCard
            v-else-if="filteredRows.length === 0"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        >
            <div class="text-center py-12">
                <UIcon
                    name="i-heroicons-magnifying-glass"
                    class="mx-auto text-4xl text-[#d1d5db] mb-4"
                />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد نتائج</p>
                <p class="text-sm text-[#666666]">حاول تغيير معايير البحث</p>
            </div>
        </UCard>

        <!-- Orders List -->
        <div v-else class="space-y-4">
            <div v-for="order in filteredRows" :key="order.id" class="overflow-hidden">
                <UCard
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.12),0px_8px_8px_rgba(0,0,0,0.08)] transition-shadow cursor-pointer"
                    @click="$router.push(localePath(`/orders/${order.id}`))"
                >
                    <div
                        class="flex items-start justify-between gap-4 pb-4 border-b border-[#ebebeb]"
                    >
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-[#171717] dark:text-white">
                                    {{ order.order_number || `الطلب #${order.id}` }}
                                </h3>
                                <UBadge
                                    :class="getStatusColor(order.status)?.color"
                                    variant="soft"
                                    size="md"
                                >
                                    {{ getStatusColor(order.status)?.label }}
                                </UBadge>
                            </div>
                            <p class="text-sm text-[#666666]">
                                {{
                                    order.created_at
                                        ? `تاريخ الطلب: ${new Date(order.created_at).toLocaleDateString('ar-SA')}`
                                        : ''
                                }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-[#171717]">
                                {{ formatCurrency(order.total_amount) }}
                            </p>
                            <p class="text-xs text-[#666666] mt-1">
                                {{ order.items?.length || 0 }} منتج{{
                                    (order.items?.length || 0) !== 1 ? 'ات' : ''
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Order Details Row -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4">
                        <div>
                            <p class="text-xs text-[#666666] mb-1">حالة الطلب</p>
                            <p class="font-semibold text-[#171717]">
                                {{ getStatusColor(order.status)?.label }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-[#666666] mb-1">الإجمالي</p>
                            <p class="font-semibold text-[#171717]">
                                {{ formatCurrency(order.total_amount) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-[#666666] mb-1">عدد المنتجات</p>
                            <p class="font-semibold text-[#171717]">
                                {{ order.items?.length || 0 }}
                            </p>
                        </div>
                        <div class="text-left">
                            <UButton
                                :to="localePath(`/orders/${order.id}`)"
                                variant="ghost"
                                color="neutral"
                                size="sm"
                                @click.stop
                            >
                                عرض التفاصيل
                                <template #trailing>
                                    <UIcon name="i-heroicons-arrow-left" />
                                </template>
                            </UButton>
                        </div>
                    </div>
                </UCard>
            </div>
        </div>

        <!-- Summary Stats -->
        <div
            v-if="filteredRows.length > 0"
            class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-[#ebebeb]"
        >
            <div class="text-center">
                <p class="text-2xl font-bold text-[#171717]">{{ filteredRows.length }}</p>
                <p class="text-sm text-[#666666]">عدد الطلبات</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ filteredRows.filter((o) => o.status === 'completed').length }}
                </p>
                <p class="text-sm text-[#666666]">مكتملة</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{
                        filteredRows.filter(
                            (o) => o.status !== 'completed' && o.status !== 'cancelled'
                        ).length
                    }}
                </p>
                <p class="text-sm text-[#666666]">قيد المعالجة</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-[#171717]">
                    {{
                        formatCurrency(
                            filteredRows.reduce((sum, o) => sum + (o.total_amount || 0), 0)
                        )
                    }}
                </p>
                <p class="text-sm text-[#666666]">إجمالي القيمة</p>
            </div>
        </div>
    </div>
</template>
