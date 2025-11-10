<template>
    <div class="form-field" :class="{ 'form-field--error': hasError }">
        <label 
            v-if="label" 
            :for="inputId" 
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
            :class="{ 'text-red-600 dark:text-red-400': hasError }"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="relative">
            <slot :id="inputId" :hasError="hasError" />
        </div>

        <!-- Help Text -->
        <p 
            v-if="helpText && !hasError" 
            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
        >
            {{ helpText }}
        </p>

        <!-- Error Message -->
        <p 
            v-if="hasError" 
            class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center"
        >
            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    helpText: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    modelValue: {
        type: [String, Number, Boolean, Array, Object],
        default: null,
    },
});

const hasError = computed(() => !!props.error);
const inputId = computed(() => `field-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
.form-field--error input,
.form-field--error select,
.form-field--error textarea {
    @apply border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500;
}
</style>
