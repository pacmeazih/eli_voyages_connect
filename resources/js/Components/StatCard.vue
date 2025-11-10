<template>
    <div 
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all hover:shadow-md"
        :class="{ 'cursor-pointer': clickable }"
        @click="handleClick"
    >
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <!-- Icon -->
                <div 
                    v-if="icon || $slots.icon"
                    class="inline-flex p-3 rounded-lg mb-3"
                    :class="iconBgClass"
                >
                    <slot name="icon">
                        <component 
                            :is="icon" 
                            class="h-6 w-6"
                            :class="iconColorClass"
                        />
                    </slot>
                </div>

                <!-- Label -->
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    {{ label }}
                </p>

                <!-- Value -->
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <slot name="value">
                        {{ formattedValue }}
                    </slot>
                </p>

                <!-- Change indicator -->
                <div 
                    v-if="change !== null && change !== undefined" 
                    class="flex items-center mt-2 text-sm"
                    :class="changeColorClass"
                >
                    <svg 
                        v-if="change > 0" 
                        class="h-4 w-4 mr-1" 
                        fill="currentColor" 
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <svg 
                        v-else-if="change < 0" 
                        class="h-4 w-4 mr-1" 
                        fill="currentColor" 
                        viewBox="0 0 20 20"
                    >
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ Math.abs(change) }}%</span>
                    <span class="ml-1 text-gray-600 dark:text-gray-400">vs dernier mois</span>
                </div>
            </div>

            <!-- Action button slot -->
            <div v-if="$slots.action">
                <slot name="action" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    icon: {
        type: [Object, String],
        default: null,
    },
    iconColor: {
        type: String,
        default: 'blue', // 'blue', 'green', 'yellow', 'red', 'indigo', 'purple', 'orange'
    },
    change: {
        type: Number,
        default: null,
    },
    format: {
        type: String,
        default: 'number', // 'number', 'currency', 'percentage'
    },
    clickable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const iconBgClass = computed(() => {
    const classes = {
        blue: 'bg-blue-100 dark:bg-blue-900',
        green: 'bg-green-100 dark:bg-green-900',
        yellow: 'bg-yellow-100 dark:bg-yellow-900',
        red: 'bg-red-100 dark:bg-red-900',
        indigo: 'bg-indigo-100 dark:bg-indigo-900',
        purple: 'bg-purple-100 dark:bg-purple-900',
        orange: 'bg-orange-100 dark:bg-orange-900',
    };
    return classes[props.iconColor] || classes.blue;
});

const iconColorClass = computed(() => {
    const classes = {
        blue: 'text-blue-600 dark:text-blue-400',
        green: 'text-green-600 dark:text-green-400',
        yellow: 'text-yellow-600 dark:text-yellow-400',
        red: 'text-red-600 dark:text-red-400',
        indigo: 'text-indigo-600 dark:text-indigo-400',
        purple: 'text-purple-600 dark:text-purple-400',
        orange: 'text-orange-600 dark:text-orange-400',
    };
    return classes[props.iconColor] || classes.blue;
});

const formattedValue = computed(() => {
    if (props.format === 'currency') {
        return new Intl.NumberFormat('fr-FR', { 
            style: 'currency', 
            currency: 'EUR' 
        }).format(props.value);
    } else if (props.format === 'percentage') {
        return `${props.value}%`;
    }
    return props.value.toLocaleString('fr-FR');
});

const changeColorClass = computed(() => {
    if (props.change === null || props.change === undefined) return '';
    return props.change > 0 
        ? 'text-green-600 dark:text-green-400' 
        : 'text-red-600 dark:text-red-400';
});

function handleClick() {
    if (props.clickable) {
        emit('click');
    }
}
</script>
