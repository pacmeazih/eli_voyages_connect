<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const deferredPrompt = ref(null);
const showInstallPrompt = ref(false);
const isInstalled = ref(false);
const isIOS = ref(false);
const showIOSInstructions = ref(false);

onMounted(() => {
    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
        isInstalled.value = true;
        return;
    }

    // Detect iOS
    const iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    isIOS.value = iOS;

    // Listen for install prompt (Android/Desktop)
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        showInstallPrompt.value = true;
    });

    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
        showInstallPrompt.value = false;
        isInstalled.value = true;
        console.log('PWA was installed');
    });
});

const installApp = async () => {
    if (!deferredPrompt.value) {
        if (isIOS.value) {
            showIOSInstructions.value = true;
        }
        return;
    }

    deferredPrompt.value.prompt();
    
    const { outcome } = await deferredPrompt.value.userChoice;
    
    if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
    } else {
        console.log('User dismissed the install prompt');
    }
    
    deferredPrompt.value = null;
};

const dismissPrompt = () => {
    showInstallPrompt.value = false;
    localStorage.setItem('pwa-prompt-dismissed', Date.now().toString());
};

const dismissIOSInstructions = () => {
    showIOSInstructions.value = false;
};

// Check if prompt was recently dismissed
onMounted(() => {
    const dismissed = localStorage.getItem('pwa-prompt-dismissed');
    if (dismissed) {
        const dismissedTime = parseInt(dismissed);
        const daysSinceDismissed = (Date.now() - dismissedTime) / (1000 * 60 * 60 * 24);
        
        // Show again after 7 days
        if (daysSinceDismissed < 7) {
            showInstallPrompt.value = false;
        }
    }
});
</script>

<template>
    <!-- Android/Desktop Install Prompt -->
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="showInstallPrompt && !isInstalled"
            class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white dark:bg-gray-800 shadow-2xl rounded-lg border border-gray-200 dark:border-gray-700 p-4 z-50"
        >
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                        Installer l'application
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                        Accédez plus rapidement à ELI Voyages Connect depuis votre écran d'accueil
                    </p>
                    
                    <div class="flex gap-2">
                        <button
                            @click="installApp"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all"
                        >
                            Installer
                        </button>
                        <button
                            @click="dismissPrompt"
                            class="px-3 py-2 text-xs font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            Plus tard
                        </button>
                    </div>
                </div>
                
                <button
                    @click="dismissPrompt"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </transition>

    <!-- iOS Instructions Modal -->
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="showIOSInstructions"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end md:items-center justify-center p-4"
            @click="dismissIOSInstructions"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-t-2xl md:rounded-2xl max-w-md w-full p-6"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Installer sur iOS
                    </h3>
                    <button
                        @click="dismissIOSInstructions"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">
                            1
                        </div>
                        <div>
                            <p>Appuyez sur le bouton <strong>Partager</strong> 
                            <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"></path>
                            </svg>
                            en bas de votre navigateur Safari</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">
                            2
                        </div>
                        <div>
                            <p>Sélectionnez <strong>"Sur l'écran d'accueil"</strong> 
                            <svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">
                            3
                        </div>
                        <div>
                            <p>Appuyez sur <strong>"Ajouter"</strong> pour installer l'application</p>
                        </div>
                    </div>
                </div>

                <button
                    @click="dismissIOSInstructions"
                    class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors"
                >
                    J'ai compris
                </button>
            </div>
        </div>
    </transition>
</template>
