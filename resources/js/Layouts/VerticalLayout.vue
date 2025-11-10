<template>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden">
        <!-- Vertical Sidebar -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg transition-transform duration-300',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo Section -->
            <div class="flex items-center justify-between h-20 px-4 border-b border-gray-200 dark:border-gray-700">
                <Link :href="route('dashboard')" class="flex items-center space-x-3 hover:opacity-90 transition-opacity">
                    <img 
                        src="/assets/img/branding/Eli-Voyages icon.png" 
                        alt="ELI VOYAGES" 
                        class="h-12 w-12 object-contain"
                    >
                    <div class="flex flex-col">
                        <span class="text-lg font-black text-amber-600 dark:text-amber-400 tracking-tight">ELI VOYAGES</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Connect Platform</span>
                    </div>
                </Link>
                <button 
                    @click="sidebarOpen = false"
                    class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- User Profile Section -->
            <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold shadow-md">
                        {{ userStore.user?.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                            {{ userStore.user?.name }}
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                            {{ t(`roles.${userStore.primaryRole}`) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <!-- Dashboard -->
                <SidebarLink 
                    :href="route('dashboard')" 
                    :active="route().current('dashboard')"
                    icon="home"
                >
                    {{ t('nav.dashboard') }}
                </SidebarLink>

                <!-- Mon Dossier (for Clients) / Dossiers (for Staff) -->
                                        <SidebarLink 
                            v-if="userStore.isClient && userStore.clientId" 
                            :href="route('dossiers.show', userStore.clientId)" 
                            :active="route().current('dossiers.show')" 
                            icon="folder"
                        >
                            Mon dossier
                        </SidebarLink>

                <SidebarLink 
                    v-else
                    :href="route('dossiers.index')" 
                    :active="route().current('dossiers.*')"
                    icon="folders"
                >
                    Dossiers
                </SidebarLink>

                <!-- Clients (Staff only) -->
                <SidebarLink 
                    v-if="userStore.isStaff"
                    :href="route('clients.index')" 
                    :active="route().current('clients.*')"
                    icon="users"
                >
                    Clients
                </SidebarLink>

                <!-- Contracts (Staff only) -->
                <SidebarLink 
                    v-if="userStore.isStaff"
                    :href="route('contracts.index')" 
                    :active="route().current('contracts.*')"
                    icon="document"
                >
                    Contrats
                </SidebarLink>

                <!-- Analytics (Staff only) -->
                <SidebarLink 
                    v-if="userStore.isStaff"
                    :href="route('analytics.page')" 
                    :active="route().current('analytics.*')"
                    icon="chart"
                >
                    {{ t('nav.analytics') }}
                </SidebarLink>

                <!-- Appointments -->
                <SidebarLink 
                    :href="route('appointments.index')" 
                    :active="route().current('appointments.*')"
                    icon="calendar"
                >
                    {{ t('nav.appointments') }}
                </SidebarLink>

                <!-- Invitations (Staff with permission) -->
                <SidebarLink 
                    v-if="userStore.can('invite users')"
                    :href="route('invitations.index')" 
                    :active="route().current('invitations.*')"
                    icon="invite"
                >
                    {{ t('nav.invitations') }}
                </SidebarLink>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

                <!-- Settings -->
                <SidebarLink 
                    :href="route('profile.edit')" 
                    :active="route().current('profile.*')"
                    icon="settings"
                >
                    {{ t('nav.profile') }}
                </SidebarLink>

                <!-- Notifications -->
                <SidebarLink 
                    :href="route('notifications.page')" 
                    :active="route().current('notifications.*')"
                    icon="bell"
                    :badge="unreadCount > 0 ? unreadCount : null"
                >
                    {{ t('nav.notifications') }}
                </SidebarLink>
            </nav>

            <!-- Bottom Actions -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2">
                <!-- Language Switcher -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Langue</span>
                    <LanguageSwitcher compact />
                </div>

                <!-- Theme Toggle -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Thème</span>
                    <DarkModeToggle />
                </div>

                <!-- Logout -->
                <button
                    @click="logout"
                    class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors shadow-sm"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    {{ t('nav.logout') }}
                </button>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div 
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden transition-opacity"
        ></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:ml-64 overflow-hidden">
            <!-- Top Header Bar (Mobile) -->
            <header class="lg:hidden h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between h-full px-4">
                    <button 
                        @click="sidebarOpen = true"
                        class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <img 
                        src="/assets/img/branding/Eli-Voyages LOGO.png" 
                        alt="ELI VOYAGES" 
                        class="h-8 object-contain"
                    >

                    <NotificationDropdown />
                </div>
            </header>

            <!-- Page Content with Scrolling -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash.success" class="mx-4 mt-4">
                    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-300 px-6 py-4 rounded-r-lg shadow-md flex items-center">
                        <svg class="h-6 w-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </div>

                <div v-if="$page.props.flash.error" class="mx-4 mt-4">
                    <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-300 px-6 py-4 rounded-r-lg shadow-md flex items-center">
                        <svg class="h-6 w-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ $page.props.flash.error }}</span>
                    </div>
                </div>

                <!-- Page Content Slot -->
                <div class="py-6">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Toast Notifications -->
        <ToastNotifications />
        
        <!-- Loading Spinner -->
        <LoadingSpinner :show="uiStore.isLoading" />

        <!-- PWA Install Prompt -->
        <PWAInstallPrompt />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useUserStore } from '@/stores/user';
import { usePreferencesStore } from '@/stores/preferences';
import { useUIStore } from '@/stores/ui';
import { useTranslation } from '@/composables/useTranslation';
import SidebarLink from '@/Components/SidebarLink.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import ToastNotifications from '@/Components/ToastNotifications.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import PWAInstallPrompt from '@/Components/PWAInstallPrompt.vue';

const userStore = useUserStore();
const preferencesStore = usePreferencesStore();
const uiStore = useUIStore();
const { t } = useTranslation();

const sidebarOpen = ref(false);
const unreadCount = ref(0);

const logout = () => {
    router.post(route('logout'));
};

// Initialize stores on mount
onMounted(() => {
    userStore.initializeFromPage();
    preferencesStore.initializePreferences({
        dark_mode: window.Laravel?.darkMode,
        language: 'fr',
    });
});
</script>
