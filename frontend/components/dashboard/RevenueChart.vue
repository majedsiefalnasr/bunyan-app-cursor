<script setup lang="ts">
    import { use } from 'echarts';
    import { LineChart } from 'echarts/charts';
    import {
        GridComponent,
        LegendComponent,
        TitleComponent,
        TooltipComponent,
    } from 'echarts/components';
    import { CanvasRenderer } from 'echarts/renderers';

    // @ts-expect-error - echarts type incompatibility with vue-volar plugin

    use([
        LineChart,
        TitleComponent,
        TooltipComponent,
        GridComponent,
        LegendComponent,
        CanvasRenderer,
    ]);

    const chartRef = ref();

    const option = {
        title: {
            text: 'إجمالي الإيرادات',
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
            data: ['المدفوعات', 'الانسحابات'],
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
            data: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            axisLine: {
                lineStyle: {
                    color: '#ebebeb',
                },
            },
            axisLabel: {
                color: '#666666',
                fontSize: 12,
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
                name: 'المدفوعات',
                data: [1200000, 1900000, 1500000, 2200000, 2290000, 2000000],
                type: 'line',
                smooth: true,
                itemStyle: {
                    color: '#0a72ef',
                },
                areaStyle: {
                    color: 'rgba(10, 114, 239, 0.1)',
                },
                lineStyle: {
                    color: '#0a72ef',
                    width: 2,
                },
            },
            {
                name: 'الانسحابات',
                data: [800000, 1200000, 900000, 1500000, 1600000, 1400000],
                type: 'line',
                smooth: true,
                itemStyle: {
                    color: '#ff5b4f',
                },
                areaStyle: {
                    color: 'rgba(255, 91, 79, 0.1)',
                },
                lineStyle: {
                    color: '#ff5b4f',
                    width: 2,
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
