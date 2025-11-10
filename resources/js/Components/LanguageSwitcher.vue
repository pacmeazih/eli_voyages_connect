<template>
    <div class="relative">
        <button
            @click="toggleDropdown"
            class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
            :class="buttonClasses"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
            </svg>
            <span>{{ currentLanguageLabel }}</span>
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                v-click-outside="closeDropdown"
                class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50"
            >
                <div class="py-1">
                    <button
                        v-for="lang in languages"
                        :key="lang.code"
                        @click="selectLanguage(lang.code)"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        :class="{ 'bg-brand-primary/10 text-brand-primary dark:bg-brand-primary/20': currentLanguage === lang.code }"
                    >
                        <span class="text-2xl mr-3">{{ lang.flag }}</span>
                        <span class="flex-1 text-left">{{ lang.name }}</span>
                        <svg 
                            v-if="currentLanguage === lang.code"
                            class="h-5 w-5 text-brand-primary"
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePreferencesStore } from '@/stores/preferences';

const preferencesStore = usePreferencesStore();
const isOpen = ref(false);

const languages = [
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'en', name: 'English', flag: '🇬🇧' },
];

const currentLanguage = computed(() => preferencesStore.language);
const currentLanguageLabel = computed(() => {
    const lang = languages.find(l => l.code === currentLanguage.value);
    return lang ? `${lang.flag} ${lang.code.toUpperCase()}` : 'FR';
});

const buttonClasses = computed(() => {
    return 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700';
});

function toggleDropdown() {
    isOpen.value = !isOpen.value;
}

function closeDropdown() {
    isOpen.value = false;
}

function selectLanguage(code) {
    if (code !== currentLanguage.value) {
        preferencesStore.setLanguage(code);
    }
    closeDropdown();
}

// Click outside directive implementation
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = function(event) {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    },
};
</script>
