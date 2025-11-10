<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Header -->
            <div class="mb-8 bg-gradient-to-r from-brand-primary to-eli-turquoise-600 rounded-lg p-8 text-white shadow-lg">
                <h1 class="text-3xl font-bold mb-2">
                    Bienvenue, {{ userStore.user?.name }} 👋
                </h1>
                <p class="text-brand-accent">
                    Suivez l'évolution de votre dossier d'immigration
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
                <!-- Timeline / Recent Activity -->
                <Card title="Activité récente">
                    <div class="space-y-4">
                        <div
                            v-for="activity in recentActivity.slice(0, 5)"
                            :key="activity.id"
                            class="flex items-start space-x-3"
                        >
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                    :class="{
                                        'bg-green-100 dark:bg-green-900': activity.type === 'success',
                                        'bg-blue-100 dark:bg-blue-900': activity.type === 'info',
                                        'bg-yellow-100 dark:bg-yellow-900': activity.type === 'warning',
                                    }"
                                >
                                    <svg class="h-4 w-4" 
                                        :class="{
                                            'text-green-600': activity.type === 'success',
                                            'text-blue-600': activity.type === 'info',
                                            'text-yellow-600': activity.type === 'warning',
                                        }"
                                        fill="currentColor" viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ activity.title }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ activity.date }}
                                </p>
                            </div>
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
                                @click="$inertia.visit(route('documents.index', { dossier: stats.currentDossier?.id }))"
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
    </AppLayout>
</template>

<script setup>
import { useUserStore } from '@/stores/user';
import AppLayout from '@/Layouts/AppLayout.vue';
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
