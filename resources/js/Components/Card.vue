<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <slot name="header">
                <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
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
