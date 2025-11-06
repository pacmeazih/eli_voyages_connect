<template>
    <Link
        :href="href"
        :class="[
            'inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200',
            active
                ? 'bg-amber-400 text-blue-900 shadow-md transform scale-105'
                : 'text-white hover:bg-blue-700 hover:text-amber-300'
        ]"
    >
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    active: Boolean,
});
</script>
