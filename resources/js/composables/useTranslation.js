import { ref, computed } from 'vue';
import { usePreferencesStore } from '@/stores/preferences';
import fr from '@/lang/fr';
import en from '@/lang/en';

const translations = {
    fr,
    en,
};

export function useTranslation() {
    const preferencesStore = usePreferencesStore();
    
    const currentLocale = computed(() => preferencesStore.language);
    
    /**
     * Translate a key
     * @param {string} key - Translation key (e.g., 'nav.dashboard')
     * @param {Object} replacements - Object with replacements (e.g., { item: 'Dossier' })
     * @returns {string} Translated string
     */
    function t(key, replacements = {}) {
        const locale = currentLocale.value || 'fr';
        const keys = key.split('.');
        let translation = translations[locale];
        
        // Navigate through nested keys
        for (const k of keys) {
            if (translation && typeof translation === 'object') {
                translation = translation[k];
            } else {
                translation = undefined;
                break;
            }
        }
        
        // If translation not found, return key
        if (translation === undefined) {
            console.warn(`Translation not found for key: ${key} in locale: ${locale}`);
            return key;
        }
        
        // Handle replacements
        if (typeof translation === 'string' && Object.keys(replacements).length > 0) {
            return translation.replace(/\{(\w+)\}/g, (match, key) => {
                return replacements[key] !== undefined ? replacements[key] : match;
            });
        }
        
        return translation;
    }
    
    /**
     * Check if a translation key exists
     * @param {string} key - Translation key
     * @returns {boolean}
     */
    function has(key) {
        const locale = currentLocale.value || 'fr';
        const keys = key.split('.');
        let translation = translations[locale];
        
        for (const k of keys) {
            if (translation && typeof translation === 'object') {
                translation = translation[k];
            } else {
                return false;
            }
        }
        
        return translation !== undefined;
    }
    
    /**
     * Get current locale
     * @returns {string}
     */
    function getLocale() {
        return currentLocale.value || 'fr';
    }
    
    /**
     * Set locale
     * @param {string} locale
     */
    function setLocale(locale) {
        if (['fr', 'en'].includes(locale)) {
            preferencesStore.setLanguage(locale);
        }
    }
    
    return {
        t,
        has,
        getLocale,
        setLocale,
        currentLocale,
    };
}

// Global install function for use in app.js if needed
export function install(app) {
    const { t, has, getLocale, setLocale } = useTranslation();
    
    app.config.globalProperties.$t = t;
    app.config.globalProperties.$has = has;
    app.config.globalProperties.$locale = getLocale;
    app.config.globalProperties.$setLocale = setLocale;
}
