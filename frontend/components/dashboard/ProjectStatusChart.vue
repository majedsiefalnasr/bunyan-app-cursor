<script setup lang="ts">
    import { use } from 'echarts';
    import { PieChart } from 'echarts/charts';
    import { LegendComponent, TitleComponent, TooltipComponent } from 'echarts/components';
    import { CanvasRenderer } from 'echarts/renderers';
    // @ts-expect-error - echarts type incompatibility
     
    use([PieChart, TitleComponent, TooltipComponent, LegendComponent, CanvasRenderer]);

    const chartRef = ref();

    const option = {
        title: {
            text: 'حالة المشاريع',
            left: 'center',
            textStyle: {
                color: '#171717',
                fontSize: 14,
                fontWeight: 600,
            },
        },
        tooltip: {
            trigger: 'item',
            backgroundColor: '#171717',
            borderColor: '#ebebeb',
            textStyle: {
                color: '#fff',
            },
            formatter: '{b}: {c} ({d}%)',
        },
        legend: {
            data: ['قيد التخطيط', 'قيد الإنجاز', 'مكتملة', 'معلقة'],
            top: 35,
            left: 'center',
            textStyle: {
                color: '#666666',
                fontSize: 12,
            },
        },
        series: [
            {
                name: 'حالة المشاريع',
                type: 'pie',
                radius: ['35%', '65%'],
                center: ['50%', '55%'],
                data: [
                    {
                        value: 2,
                        name: 'قيد التخطيط',
                        itemStyle: {
                            color: '#0a72ef',
                        },
                    },
                    {
                        value: 3,
                        name: 'قيد الإنجاز',
                        itemStyle: {
                            color: '#ff5b4f',
                        },
                    },
                    {
                        value: 1,
                        name: 'مكتملة',
                        itemStyle: {
                            color: '#22c55e',
                        },
                    },
                    {
                        value: 1,
                        name: 'معلقة',
                        itemStyle: {
                            color: '#f59e0b',
                        },
                    },
                ],
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)',
                    },
                },
                label: {
                    color: '#666666',
                    fontSize: 11,
                },
            },
        ],
    };
</script>

<template>
    <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]">
        <VChart ref="chartRef" :option="option" autoresize class="h-80" />
    </UCard>
</template>
