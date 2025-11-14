<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Header -->
            <div class="mb-8 bg-gradient-to-r from-brand-primary to-eli-turquoise-600 rounded-lg p-8 text-white shadow-lg">
                <h1 class="text-3xl font-bold mb-2">
                    Bienvenue, {{ userStore.user?.name }} 👋
                </h1>
                <p class="text-brand-accent text-lg">
                    Suivez l'évolution de votre dossier d'immigration ELI-VOYAGES
                </p>
            </div>

            <!-- Dossier Status Overview -->
            <div v-if="stats.currentDossier" class="mb-8">
                <Card title="Mon dossier en cours">
                    <div class="space-y-6">
                        <!-- Dossier Header -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Référence</p>
                                <p class="text-2xl font-bold text-brand-primary">{{ stats.currentDossier.reference }}</p>
                            </div>
                            <StatusBadge :status="stats.currentDossier.status" type="dossier" />
                        </div>

                        <!-- Progress Bar -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progression</span>
                                <span class="text-sm font-medium text-brand-primary">{{ stats.currentDossier.progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div 
                                    class="bg-gradient-to-r from-brand-primary to-eli-turquoise-500 h-3 rounded-full transition-all duration-500"
                                    :style="{ width: `${stats.currentDossier.progress}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Assigned Agent -->
                        <div v-if="stats.currentDossier.agent" class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="w-12 h-12 rounded-full bg-brand-accent flex items-center justify-center text-brand-primary font-bold text-lg">
                                {{ stats.currentDossier.agent.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Votre conseiller</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ stats.currentDossier.agent.name }}</p>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                <StatCard
                    label="Documents téléchargés"
                    :value="stats.uploadedDocuments || 0"
                    icon-color="green"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Documents manquants"
                    :value="stats.missingDocuments || 0"
                    icon-color="red"
                    clickable
                    @click="scrollToDocuments"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Prochaine étape"
                    :value="stats.nextStep || 'En attente'"
                    icon-color="blue"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </template>
                    <template #value>
                        <span class="text-lg">{{ stats.nextStep || 'En attente' }}</span>
                    </template>
                </StatCard>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Timeline de suivi -->
                <Card title="Timeline de suivi du dossier">
                    <div class="relative space-y-4">
                        <!-- Vertical line -->
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        
                        <div
                            v-for="(activity, index) in recentActivity.slice(0, 6)"
                            :key="activity.id"
                            class="relative flex items-start space-x-4"
                        >
                            <!-- Timeline dot -->
                            <div class="relative flex-shrink-0 z-10">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800"
                                    :class="{
                                        'bg-green-500': activity.type === 'success' || index === 0,
                                        'bg-brand-primary': activity.type === 'info',
                                        'bg-yellow-500': activity.type === 'warning',
                                        'bg-gray-400': !activity.type,
                                    }"
                                >
                                    <!-- Success icon -->
                                    <svg v-if="activity.type === 'success' || index === 0" class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <!-- Info icon -->
                                    <svg v-else-if="activity.type === 'info'" class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <!-- Warning icon -->
                                    <svg v-else-if="activity.type === 'warning'" class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <!-- Default icon -->
                                    <svg v-else class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 pb-6">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ activity.title }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 flex items-center">
                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ activity.date }}
                                </p>
                            </div>
                        </div>

                        <!-- View all link -->
                        <div class="text-center pt-2">
                            <Link
                                :href="route('dossiers.show', stats.currentDossier?.id) + '#timeline'"
                                class="text-sm text-brand-primary hover:text-brand-primary/80 font-medium"
                            >
                                Voir l'historique complet →
                            </Link>
                        </div>
                    </div>
                </Card>

                <!-- Documents Required -->
                <Card title="Documents requis" id="documents-section">
                    <div class="space-y-3">
                        <div
                            v-for="doc in pendingActions"
                            :key="doc.id"
                            class="flex items-center justify-between p-4 border-2 border-dashed rounded-lg transition-colors"
                            :class="doc.uploaded ? 'border-green-300 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 hover:border-brand-primary'"
                        >
                            <div class="flex items-center space-x-3">
                                <svg 
                                    class="h-6 w-6"
                                    :class="doc.uploaded ? 'text-green-600' : 'text-gray-400'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                >
                                    <path v-if="doc.uploaded" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ doc.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ doc.description }}</p>
                                </div>
                            </div>
                            <button
                                v-if="!doc.uploaded"
                                @click="$inertia.visit(route('dossiers.show', stats.currentDossier?.id) + '#documents')"
                                class="px-3 py-1 text-sm bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 transition-colors"
                            >
                                Télécharger
                            </button>
                            <svg v-else class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Upcoming Appointments -->
            <div v-if="upcomingAppointments.length > 0" class="mt-6">
                <Card title="Mes rendez-vous">
                    <div class="space-y-3">
                        <div
                            v-for="appointment in upcomingAppointments"
                            :key="appointment.id"
                            class="flex items-center space-x-4 p-4 bg-brand-accent/10 rounded-lg"
                        >
                            <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-brand-accent flex flex-col items-center justify-center">
                                <span class="text-lg font-bold text-brand-primary">{{ appointment.day }}</span>
                                <span class="text-xs text-brand-primary">{{ appointment.month }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ appointment.title }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ appointment.time }}</p>
                            </div>
                            <button
                                class="px-4 py-2 text-sm bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 transition-colors"
                            >
                                Détails
                            </button>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { useUserStore } from '@/stores/user';
import { Link } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const userStore = useUserStore();

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
    pendingActions: {
        type: Array,
        default: () => [],
    },
    upcomingAppointments: {
        type: Array,
        default: () => [],
    },
});

function scrollToDocuments() {
    document.getElementById('documents-section')?.scrollIntoView({ behavior: 'smooth' });
}
</script>
