<template>
    <div 
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        <!-- Backdrop -->
        <div 
            class="absolute inset-0 bg-gray-900 bg-opacity-50 dark:bg-opacity-70"
            @click="handleBackdropClick"
        ></div>

        <!-- Spinner -->
        <div class="relative">
            <div class="inline-flex flex-col items-center justify-center p-8 bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                <svg 
                    class="animate-spin h-12 w-12 mb-4"
                    :class="spinnerColorClass"
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                >
                    <circle 
                        class="opacity-25" 
                        cx="12" 
                        cy="12" 
                        r="10" 
                        stroke="currentColor" 
                        stroke-width="4"
                    ></circle>
                    <path 
                        class="opacity-75" 
                        fill="currentColor" 
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
                <p 
                    v-if="message"
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ message }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: 'Chargement...',
    },
    color: {
        type: String,
        default: 'indigo', // 'indigo', 'blue', 'green', etc.
    },
    dismissible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const spinnerColorClass = computed(() => {
    const colors = {
        indigo: 'text-indigo-600',
        blue: 'text-blue-600',
        green: 'text-green-600',
        red: 'text-red-600',
        yellow: 'text-yellow-600',
        purple: 'text-purple-600',
    };
    return colors[props.color] || colors.indigo;
});

function handleBackdropClick() {
    if (props.dismissible) {
        emit('close');
    }
}
</script>
