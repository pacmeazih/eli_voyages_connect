<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200">
        <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <slot name="header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
            </slot>
        </div>
        <div class="p-6">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
});
</script>
