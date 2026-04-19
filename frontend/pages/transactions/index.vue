<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const { listTransactions } = useTransactions();

    interface Transaction {
        id: number;
        reference: string;
        type: 'payment' | 'withdrawal';
        status: 'pending' | 'completed' | 'failed';
        amount: number;
        description?: string;
        payment_method?: string;
        user_id?: number;
        project_id?: number;
        created_at?: string;
    }

    const transactions = ref<Transaction[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);
    const selectedType = ref<string>('');
    const selectedStatus = ref<string>('');
    const searchQuery = ref<string>('');

    const typeColors: Record<string, { label: string; color: string; icon: string }> = {
        payment: { label: 'دفعة', color: 'bg-blue-50', icon: 'i-heroicons-arrow-down-circle' },
        withdrawal: { label: 'سحب', color: 'bg-green-50', icon: 'i-heroicons-arrow-up-circle' },
    };

    const statusColors: Record<string, { label: string; color: string; icon: string }> = {
        pending: { label: 'قيد الانتظار', color: 'bg-yellow-50', icon: 'i-heroicons-clock' },
        completed: { label: 'مكتمل', color: 'bg-green-50', icon: 'i-heroicons-check-circle' },
        failed: { label: 'فشل', color: 'bg-red-50', icon: 'i-heroicons-x-circle' },
    };

    const filteredTransactions = computed(() => {
        let filtered = transactions.value;

        if (selectedType.value) {
            filtered = filtered.filter((t) => t.type === selectedType.value);
        }

        if (selectedStatus.value) {
            filtered = filtered.filter((t) => t.status === selectedStatus.value);
        }

        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filtered = filtered.filter(
                (t) =>
                    (t.reference || '').toLowerCase().includes(q) ||
                    (t.description || '').toLowerCase().includes(q)
            );
        }

        return filtered.sort((a, b) => {
            const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
            const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
            return dateB - dateA;
        });
    });

    const transactionStats = computed(() => {
        return {
            totalPayments: transactions.value
                .filter((t) => t.type === 'payment')
                .reduce((sum, t) => sum + (t.amount || 0), 0),
            totalWithdrawals: transactions.value
                .filter((t) => t.type === 'withdrawal')
                .reduce((sum, t) => sum + (t.amount || 0), 0),
            completedCount: transactions.value.filter((t) => t.status === 'completed').length,
            pendingCount: transactions.value.filter((t) => t.status === 'pending').length,
        };
    });

    const formatCurrency = (amount: number | undefined) => {
        if (!amount) return '0 ريال';
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR',
            maximumFractionDigits: 0,
        }).format(amount);
    };

    onMounted(async () => {
        try {
            const response = await listTransactions({ per_page: 200 });
            transactions.value = response.data;
        } catch (e: unknown) {
            transactions.value = [];
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
                العمليات المالية
            </h1>
            <p class="mt-2 text-sm text-[#666666]">
                تتبع جميع عمليات الدفع والسحب والمعاملات المالية
            </p>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Total Payments -->
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">إجمالي الدفعات</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ formatCurrency(transactionStats.totalPayments) }}
                        </p>
                    </div>
                    <UIcon
                        name="i-heroicons-arrow-down-circle"
                        class="text-3xl text-blue-400 opacity-50"
                    />
                </div>
            </UCard>

            <!-- Total Withdrawals -->
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">إجمالي السحوبات</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ formatCurrency(transactionStats.totalWithdrawals) }}
                        </p>
                    </div>
                    <UIcon
                        name="i-heroicons-arrow-up-circle"
                        class="text-3xl text-green-400 opacity-50"
                    />
                </div>
            </UCard>

            <!-- Completed Transactions -->
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">عمليات مكتملة</p>
                        <p class="text-2xl font-bold text-[#171717]">
                            {{ transactionStats.completedCount }}
                        </p>
                    </div>
                    <UIcon
                        name="i-heroicons-check-circle"
                        class="text-3xl text-green-400 opacity-50"
                    />
                </div>
            </UCard>

            <!-- Pending Transactions -->
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">عمليات قيد الانتظار</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ transactionStats.pendingCount }}
                        </p>
                    </div>
                    <UIcon name="i-heroicons-clock" class="text-3xl text-yellow-400 opacity-50" />
                </div>
            </UCard>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <UInput
                v-model="searchQuery"
                placeholder="ابحث برقم العملية أو الوصف..."
                icon="i-heroicons-magnifying-glass"
                type="search"
            />
            <USelect
                v-model="selectedType"
                :options="[
                    { label: 'جميع الأنواع', value: '' },
                    { label: 'دفعات', value: 'payment' },
                    { label: 'سحوبات', value: 'withdrawal' },
                ]"
                placeholder="فلتر حسب النوع"
            />
            <USelect
                v-model="selectedStatus"
                :options="[
                    { label: 'جميع الحالات', value: '' },
                    { label: 'قيد الانتظار', value: 'pending' },
                    { label: 'مكتمل', value: 'completed' },
                    { label: 'فشل', value: 'failed' },
                ]"
                placeholder="فلتر حسب الحالة"
            />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 8" :key="i">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <USkeleton class="h-5 w-1/2" />
                            <USkeleton class="h-5 w-20" />
                        </div>
                        <USkeleton class="h-4 w-3/4" />
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
        <UCard
            v-else-if="transactions.length === 0"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        >
            <div class="text-center py-12">
                <UIcon name="i-heroicons-banknotes" class="mx-auto text-4xl text-[#d1d5db] mb-4" />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد عمليات</p>
                <p class="text-sm text-[#666666]">ستظهر العمليات المالية هنا</p>
            </div>
        </UCard>

        <!-- No Results State -->
        <UCard
            v-else-if="filteredTransactions.length === 0"
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

        <!-- Transactions List -->
        <div v-else class="space-y-3">
            <div v-for="transaction in filteredTransactions" :key="transaction.id">
                <UCard
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.12),0px_8px_8px_rgba(0,0,0,0.08)] transition-shadow"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-start gap-3 flex-1">
                            <div
                                :class="typeColors[transaction.type].color"
                                class="rounded-lg p-2 flex-shrink-0"
                            >
                                <UIcon
                                    :name="typeColors[transaction.type].icon"
                                    class="text-2xl text-[#171717]"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-[#171717]">
                                    {{ transaction.description || transaction.reference }}
                                </h3>
                                <p class="text-sm text-[#666666] mt-1">
                                    {{ transaction.reference }}
                                </p>
                                <div class="flex items-center gap-2 mt-2">
                                    <UBadge :class="typeColors[transaction.type].color">
                                        {{ typeColors[transaction.type].label }}
                                    </UBadge>
                                    <UBadge :class="statusColors[transaction.status].color">
                                        {{ statusColors[transaction.status].label }}
                                    </UBadge>
                                </div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <p
                                :class="{
                                    'text-green-600': transaction.type === 'withdrawal',
                                    'text-blue-600': transaction.type === 'payment',
                                }"
                                class="text-2xl font-bold"
                            >
                                {{ transaction.type === 'withdrawal' ? '+' : '-'
                                }}{{ formatCurrency(transaction.amount) }}
                            </p>
                            <p class="text-xs text-[#666666] mt-2">
                                {{
                                    transaction.created_at
                                        ? new Date(transaction.created_at).toLocaleDateString(
                                              'ar-SA'
                                          )
                                        : ''
                                }}
                            </p>
                        </div>
                    </div>
                </UCard>
            </div>
        </div>

        <!-- Summary Footer -->
        <div
            v-if="filteredTransactions.length > 0"
            class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-[#ebebeb]"
        >
            <div class="text-center">
                <p class="text-xs text-[#666666] mb-1">عدد العمليات</p>
                <p class="text-2xl font-bold text-[#171717]">{{ filteredTransactions.length }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-[#666666] mb-1">إجمالي الدفعات</p>
                <p class="text-2xl font-bold text-blue-600">
                    {{
                        formatCurrency(
                            filteredTransactions
                                .filter((t) => t.type === 'payment')
                                .reduce((sum, t) => sum + (t.amount || 0), 0)
                        )
                    }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-[#666666] mb-1">إجمالي السحوبات</p>
                <p class="text-2xl font-bold text-green-600">
                    {{
                        formatCurrency(
                            filteredTransactions
                                .filter((t) => t.type === 'withdrawal')
                                .reduce((sum, t) => sum + (t.amount || 0), 0)
                        )
                    }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-[#666666] mb-1">الرصيد الصافي</p>
                <p class="text-2xl font-bold text-[#171717]">
                    {{
                        formatCurrency(
                            filteredTransactions
                                .filter((t) => t.type === 'withdrawal')
                                .reduce((sum, t) => sum + (t.amount || 0), 0) -
                                filteredTransactions
                                    .filter((t) => t.type === 'payment')
                                    .reduce((sum, t) => sum + (t.amount || 0), 0)
                        )
                    }}
                </p>
            </div>
        </div>
    </div>
</template>
