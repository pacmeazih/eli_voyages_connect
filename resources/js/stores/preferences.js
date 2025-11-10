import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export const usePreferencesStore = defineStore('preferences', () => {
    // State
    const theme = ref('light'); // 'light' or 'dark'
    const language = ref('fr'); // 'fr' or 'en'
    const isInitialized = ref(false);

    // Actions
    function initializePreferences(userPreferences = {}) {
        // Load from user data or localStorage
        const savedTheme = userPreferences.dark_mode 
            ? 'dark' 
            : localStorage.getItem('eli_theme') || 'light';
        
        const savedLanguage = userPreferences.language 
            || localStorage.getItem('eli_language') 
            || 'fr';

        theme.value = savedTheme;
        language.value = savedLanguage;

        // Apply theme immediately
        applyTheme(savedTheme);

        isInitialized.value = true;
    }

    function setTheme(newTheme) {
        theme.value = newTheme;
        localStorage.setItem('eli_theme', newTheme);
        applyTheme(newTheme);

        // Persist to backend
        router.post('/preferences/dark-mode', {
            dark_mode: newTheme === 'dark',
        }, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function toggleTheme() {
        const newTheme = theme.value === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
    }

    function applyTheme(themeValue) {
        if (themeValue === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function setLanguage(newLanguage) {
        language.value = newLanguage;
        localStorage.setItem('eli_language', newLanguage);

        // Persist to backend and reload
        router.post('/preferences/language', {
            language: newLanguage,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Reload to get new translations from backend
                router.reload();
            },
        });
    }

    function detectSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    return {
        // State
        theme,
        language,
        isInitialized,
        // Actions
        initializePreferences,
        setTheme,
        toggleTheme,
        setLanguage,
        detectSystemTheme,
    };
});
