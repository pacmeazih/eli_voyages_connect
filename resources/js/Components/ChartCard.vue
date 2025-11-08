<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-colors duration-200">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ title }}</h3>
        <canvas :id="canvasId" :height="height"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

// Register Chart.js components
Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'line', // line, bar, pie, doughnut
        validator: (value) => ['line', 'bar', 'pie', 'doughnut'].includes(value)
    },
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    },
    height: {
        type: Number,
        default: 100
    }
});

const canvasId = ref(`chart-${Math.random().toString(36).substr(2, 9)}`);
let chartInstance = null;

const createChart = () => {
    const ctx = document.getElementById(canvasId.value);
    if (!ctx) return;

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy();
    }

    // Dark mode detection
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e7eb' : '#374151';
    const gridColor = isDark ? '#374151' : '#e5e7eb';

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: props.type === 'pie' || props.type === 'doughnut',
                position: 'bottom',
                labels: {
                    color: textColor,
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: isDark ? '#1f2937' : '#ffffff',
                titleColor: textColor,
                bodyColor: textColor,
                borderColor: gridColor,
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8
            }
        },
        scales: props.type !== 'pie' && props.type !== 'doughnut' ? {
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColor
                },
                grid: {
                    color: gridColor
                }
            },
            x: {
                ticks: {
                    color: textColor
                },
                grid: {
                    color: gridColor
                }
            }
        } : undefined
    };

    // Merge options
    const mergedOptions = {
        ...defaultOptions,
        ...props.options,
        plugins: {
            ...defaultOptions.plugins,
            ...props.options.plugins
        }
    };

    chartInstance = new Chart(ctx, {
        type: props.type,
        data: props.data,
        options: mergedOptions
    });
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    createChart();
}, { deep: true });

// Recreate chart on theme change
const observer = new MutationObserver(() => {
    createChart();
});

onMounted(() => {
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
});
</script>
