<template>
    <div class="min-h-screen bg-brand-neutral dark:bg-gray-900 transition-colors">
        <!-- Navigation -->
        <nav class="bg-gradient-to-r from-brand-primary via-eli-teal-600 to-brand-primary shadow-lg border-b-4 border-brand-accent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex">
                        <!-- Logo ELI Voyages -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="flex items-center space-x-3 hover:opacity-90 transition-opacity">
                                <div class="bg-brand-accent p-2 rounded-lg shadow-md">
                                    <svg class="h-10 w-10 text-brand-primary" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-2xl font-black text-white tracking-tight">ELI VOYAGES</span>
                                    <span class="text-xs text-brand-accent font-semibold uppercase tracking-wider">Connect Platform</span>
                                </div>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-10 sm:flex sm:space-x-1 items-center">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                <svg class="h-5 w-5 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                {{ t('nav.dashboard') }}
                            </NavLink>
                            
                            <NavLink :href="route('dossiers.index')" :active="route().current('dossiers.*')">
                                <svg class="h-5 w-5 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ t('nav.dossiers') }}
                            </NavLink>
                            
                            <NavLink v-if="userStore.isStaff" :href="route('analytics.page')" :active="route().current('analytics.*')">
                                <svg class="h-5 w-5 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                {{ t('nav.analytics') }}
                            </NavLink>
                            
                            <NavLink :href="route('appointments.index')" :active="route().current('appointments.*')">
                                <svg class="h-5 w-5 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ t('nav.appointments') }}
                            </NavLink>
                            
                            <NavLink v-if="userStore.can('invite users')" :href="route('invitations.index')" :active="route().current('invitations.*')">
                                <svg class="h-5 w-5 mr-1.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                {{ t('nav.invitations') }}
                            </NavLink>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-3">
                        <!-- Language Switcher -->
                        <LanguageSwitcher />
                        
                        <!-- Theme Toggle -->
                        <DarkModeToggle />
                        
                        <!-- Notifications -->
                        <NotificationDropdown />

                        <!-- User Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center px-4 py-2 text-sm font-medium text-white bg-eli-turquoise-600 hover:bg-eli-turquoise-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-accent transition-all shadow-sm">
                                    <div class="w-8 h-8 rounded-full bg-brand-accent flex items-center justify-center text-brand-primary font-bold mr-2 shadow-inner">
                                        {{ userStore.user?.name.charAt(0) }}
                                    </div>
                                    <span class="hidden md:inline">{{ userStore.user?.name }}</span>
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ userStore.user?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ userStore.primaryRole ? t(`roles.${userStore.primaryRole}`) : 'Utilisateur' }}
                                    </p>
                                </div>
                                
                                <DropdownLink :href="route('profile.edit')">
                                    <svg class="h-4 w-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ t('nav.profile') }}
                                </DropdownLink>
                                
                                <DropdownLink :href="route('notifications.page')">
                                    <svg class="h-4 w-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    {{ t('nav.notifications') }}
                                </DropdownLink>
                                
                                <div class="border-t border-gray-200 dark:border-gray-700"></div>
                                
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    <svg class="h-4 w-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    {{ t('nav.logout') }}
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="min-h-[calc(100vh-5rem)]">
            <!-- Flash Messages (replaced by Toast system, but keep for compatibility) -->
            <div v-if="$page.props.flash.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 text-green-800 dark:text-green-300 px-6 py-4 rounded-r-lg shadow-md flex items-center">
                    <svg class="h-6 w-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div v-if="$page.props.flash.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-800 dark:text-red-300 px-6 py-4 rounded-r-lg shadow-md flex items-center">
                    <svg class="h-6 w-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>
            </div>

            <div class="py-6">
                <slot />
            </div>
        </main>

        <!-- Toast Notifications -->
        <ToastNotifications />
        
        <!-- Loading Spinner (controlled by UI store) -->
        <LoadingSpinner :show="uiStore.isLoading" />

        <!-- PWA Install Prompt -->
        <PWAInstallPrompt />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useUserStore } from '@/stores/user';
import { usePreferencesStore } from '@/stores/preferences';
import { useUIStore } from '@/stores/ui';
import { useTranslation } from '@/composables/useTranslation';
import NavLink from '@/Components/NavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
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

// Initialize stores on mount
onMounted(() => {
    userStore.initializeFromPage();
    preferencesStore.initializePreferences({
        dark_mode: window.Laravel?.darkMode,
        language: 'fr', // Could be fetched from user preferences
    });
});
</script>
