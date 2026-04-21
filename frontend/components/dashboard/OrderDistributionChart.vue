<script setup lang="ts">
     
    import { use } from 'echarts';
    import { BarChart } from 'echarts/charts';
    import {
        GridComponent,
        LegendComponent,
        TitleComponent,
        TooltipComponent,
    } from 'echarts/components';
    import { CanvasRenderer } from 'echarts/renderers';

    use([
        BarChart,
        TitleComponent,
        TooltipComponent,
        GridComponent,
        LegendComponent,
        CanvasRenderer,
    ]);

    const chartRef = ref();

    const option = {
        title: {
            text: 'توزيع الطلبات حسب فئة المواد',
            left: 'center',
            textStyle: {
                color: '#171717',
                fontSize: 14,
                fontWeight: 600,
            },
        },
        tooltip: {
            trigger: 'axis',
            backgroundColor: '#171717',
            borderColor: '#ebebeb',
            textStyle: {
                color: '#fff',
            },
        },
        legend: {
            data: ['الطلبات المكتملة', 'الطلبات قيد الشحن', 'الطلبات المعلقة'],
            top: 35,
            textStyle: {
                color: '#666666',
                fontSize: 12,
            },
        },
        grid: {
            left: '12%',
            right: '4%',
            bottom: '15%',
            top: '20%',
            containLabel: true,
        },
        xAxis: {
            type: 'category',
            data: ['الأسمنت', 'الرمل', 'الحديد', 'الطوب', 'الدهانات', 'المعدات'],
            axisLine: {
                lineStyle: {
                    color: '#ebebeb',
                },
            },
            axisLabel: {
                color: '#666666',
                fontSize: 11,
            },
        },
        yAxis: {
            type: 'value',
            splitLine: {
                lineStyle: {
                    color: '#f0f0f0',
                },
            },
            axisLabel: {
                color: '#666666',
                fontSize: 12,
            },
        },
        series: [
            {
                name: 'الطلبات المكتملة',
                data: [120, 200, 150, 180, 90, 110],
                type: 'bar',
                itemStyle: {
                    color: '#22c55e',
                },
            },
            {
                name: 'الطلبات قيد الشحن',
                data: [50, 80, 60, 70, 40, 50],
                type: 'bar',
                itemStyle: {
                    color: '#0a72ef',
                },
            },
            {
                name: 'الطلبات المعلقة',
                data: [30, 40, 35, 45, 25, 30],
                type: 'bar',
                itemStyle: {
                    color: '#f59e0b',
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
