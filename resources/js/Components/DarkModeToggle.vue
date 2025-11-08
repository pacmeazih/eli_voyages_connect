<template>
    <button
        @click="toggleDarkMode"
        class="p-2 rounded-lg transition-colors duration-200"
        :class="isDark ? 'text-yellow-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-100'"
        :title="isDark ? 'Mode clair' : 'Mode sombre'"
    >
        <!-- Sun icon (light mode) -->
        <svg
            v-if="!isDark"
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
        </svg>

        <!-- Moon icon (dark mode) -->
        <svg
            v-else
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
        </svg>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isDark = ref(false);

onMounted(() => {
    // Check user preference from server
    isDark.value = window.Laravel?.darkMode || false;
    
    // Apply dark mode class
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    
    // Toggle class on html element
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    
    // Save preference to server
    router.post('/preferences/dark-mode', {
        dark_mode: isDark.value,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>
