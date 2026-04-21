<script setup lang="ts">
    import { use } from 'echarts';
    import { BarChart } from 'echarts/charts';
    import { GridComponent, TitleComponent, TooltipComponent } from 'echarts/components';
    import { graphic } from 'echarts/core';
    import { CanvasRenderer } from 'echarts/renderers';

    // @ts-expect-error - echarts type incompatibility with vue-volar plugin

    use([BarChart, TitleComponent, TooltipComponent, GridComponent, CanvasRenderer]);

    const chartRef = ref();

    const option = {
        title: {
            text: 'تقدم المراحل',
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
            formatter: '{b}: {c}%',
        },
        grid: {
            left: '15%',
            right: '4%',
            bottom: '10%',
            top: '20%',
            containLabel: true,
        },
        xAxis: {
            type: 'value',
            max: 100,
            splitLine: {
                lineStyle: {
                    color: '#f0f0f0',
                },
            },
            axisLabel: {
                color: '#666666',
                fontSize: 12,
                formatter: '{value}%',
            },
        },
        yAxis: {
            type: 'category',
            data: ['الأساسات', 'الهياكل الخرسانية', 'التشطيبات', 'التركيبات الكهربائية', 'السباكة'],
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
        series: [
            {
                name: 'النسبة المئوية',
                type: 'bar',
                data: [100, 75, 45, 30, 15],
                itemStyle: {
                    color: new graphic.LinearGradient(0, 0, 1, 0, [
                        { offset: 0, color: '#0a72ef' },
                        { offset: 1, color: '#0a72ef' },
                    ]),
                    borderRadius: [0, 4, 4, 0],
                },
                label: {
                    show: true,
                    position: 'right',
                    color: '#171717',
                    fontSize: 12,
                    fontWeight: 600,
                    formatter: '{c}%',
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
