<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ t('settings.title') }}
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gérez vos préférences et paramètres de compte
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-1">
                    <Card>
                        <nav class="space-y-1">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                                :class="activeTab === tab.id
                                    ? 'bg-brand-primary text-white'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            >
                                <component :is="tab.icon" class="h-5 w-5 mr-3" />
                                {{ tab.label }}
                            </button>
                        </nav>
                    </Card>
                </div>

                <!-- Content Area -->
                <div class="lg:col-span-2">
                    <!-- Profile Tab -->
                    <Card v-if="activeTab === 'profile'" title="Informations du profil">
                        <form @submit.prevent="updateProfile" class="space-y-4">
                            <FormField
                                :label="t('clients.name')"
                                :error="form.errors.name"
                                required
                            >
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white"
                                />
                            </FormField>

                            <FormField
                                :label="t('clients.email')"
                                :error="form.errors.email"
                                required
                            >
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white"
                                />
                            </FormField>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Rôle: <span class="font-medium">{{ userStore.primaryRole ? t(`roles.${userStore.primaryRole}`) : 'Utilisateur' }}</span>
                                </p>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 disabled:opacity-50 transition-colors"
                                >
                                    {{ t('save') }}
                                </button>
                            </div>
                        </form>
                    </Card>

                    <!-- Preferences Tab -->
                    <Card v-if="activeTab === 'preferences'" title="Préférences">
                        <div class="space-y-6">
                            <!-- Theme Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    {{ t('settings.theme') }}
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        @click="setTheme('light')"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg transition-all"
                                        :class="currentTheme === 'light'
                                            ? 'border-brand-primary bg-brand-primary/10'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-brand-primary'"
                                    >
                                        <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <span class="text-sm font-medium">{{ t('settings.lightMode') }}</span>
                                    </button>

                                    <button
                                        @click="setTheme('dark')"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg transition-all"
                                        :class="currentTheme === 'dark'
                                            ? 'border-brand-primary bg-brand-primary/10'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-brand-primary'"
                                    >
                                        <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                        </svg>
                                        <span class="text-sm font-medium">{{ t('settings.darkMode') }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Language Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    {{ t('settings.language') }}
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        @click="setLanguage('fr')"
                                        class="flex items-center justify-center p-4 border-2 rounded-lg transition-all"
                                        :class="currentLanguage === 'fr'
                                            ? 'border-brand-primary bg-brand-primary/10'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-brand-primary'"
                                    >
                                        <span class="text-3xl mr-3">🇫🇷</span>
                                        <span class="text-sm font-medium">Français</span>
                                    </button>

                                    <button
                                        @click="setLanguage('en')"
                                        class="flex items-center justify-center p-4 border-2 rounded-lg transition-all"
                                        :class="currentLanguage === 'en'
                                            ? 'border-brand-primary bg-brand-primary/10'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-brand-primary'"
                                    >
                                        <span class="text-3xl mr-3">🇬🇧</span>
                                        <span class="text-sm font-medium">English</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Security Tab -->
                    <Card v-if="activeTab === 'security'" title="Sécurité">
                        <form @submit.prevent="updatePassword" class="space-y-4">
                            <FormField
                                label="Mot de passe actuel"
                                :error="passwordForm.errors.current_password"
                                required
                            >
                                <input
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white"
                                />
                            </FormField>

                            <FormField
                                label="Nouveau mot de passe"
                                :error="passwordForm.errors.password"
                                required
                            >
                                <input
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white"
                                />
                            </FormField>

                            <FormField
                                label="Confirmer le mot de passe"
                                :error="passwordForm.errors.password_confirmation"
                                required
                            >
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white"
                                />
                            </FormField>

                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 disabled:opacity-50 transition-colors"
                                >
                                    Mettre à jour le mot de passe
                                </button>
                            </div>
                        </form>
                    </Card>

                    <!-- Notifications Tab -->
                    <Card v-if="activeTab === 'notifications'" title="Notifications">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Notifications par email
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Recevoir des notifications par email
                                    </p>
                                </div>
                                <input
                                    v-model="notificationPreferences.email"
                                    type="checkbox"
                                    class="h-5 w-5 text-brand-primary focus:ring-brand-primary border-gray-300 rounded"
                                />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Notifications WhatsApp
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Recevoir des notifications via WhatsApp
                                    </p>
                                </div>
                                <input
                                    v-model="notificationPreferences.whatsapp"
                                    type="checkbox"
                                    class="h-5 w-5 text-brand-primary focus:ring-brand-primary border-gray-300 rounded"
                                />
                            </div>

                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button
                                    @click="saveNotificationPreferences"
                                    class="px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 transition-colors"
                                >
                                    {{ t('save') }}
                                </button>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useUserStore } from '@/stores/user';
import { usePreferencesStore } from '@/stores/preferences';
import { useUIStore } from '@/stores/ui';
import { useTranslation } from '@/composables/useTranslation';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';

const props = defineProps({
    user: Object,
});

const userStore = useUserStore();
const preferencesStore = usePreferencesStore();
const uiStore = useUIStore();
const { t } = useTranslation();

const activeTab = ref('profile');

const currentTheme = computed(() => preferencesStore.theme);
const currentLanguage = computed(() => preferencesStore.language);

// Tab configuration
const tabs = [
    {
        id: 'profile',
        label: t('settings.profile'),
        icon: 'svg',
    },
    {
        id: 'preferences',
        label: t('settings.preferences'),
        icon: 'svg',
    },
    {
        id: 'security',
        label: t('settings.security'),
        icon: 'svg',
    },
    {
        id: 'notifications',
        label: t('settings.notifications'),
        icon: 'svg',
    },
];

// Profile form
const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
});

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Notification preferences
const notificationPreferences = ref({
    email: true,
    whatsapp: false,
});

function updateProfile() {
    form.put(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            uiStore.showSuccess('Profil mis à jour avec succès');
        },
        onError: () => {
            uiStore.showError('Erreur lors de la mise à jour du profil');
        },
    });
}

function updatePassword() {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            uiStore.showSuccess('Mot de passe mis à jour avec succès');
        },
        onError: () => {
            uiStore.showError('Erreur lors de la mise à jour du mot de passe');
        },
    });
}

function setTheme(theme) {
    preferencesStore.setTheme(theme);
    uiStore.showSuccess(`Thème changé: ${theme === 'dark' ? 'Sombre' : 'Clair'}`);
}

function setLanguage(lang) {
    preferencesStore.setLanguage(lang);
    // Note: This will reload the page to get new translations
}

function saveNotificationPreferences() {
    // TODO: Implement backend route for notification preferences
    uiStore.showSuccess('Préférences de notification enregistrées');
}
</script>
