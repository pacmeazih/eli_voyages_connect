<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ t('dashboard.title') }} - Agent
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Suivez vos clients et gérez leurs dossiers
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <StatCard
                    label="Mes dossiers"
                    :value="stats.myDossiers || 0"
                    icon-color="indigo"
                    clickable
                    @click="$inertia.visit(route('dossiers.index', { assigned_to: 'me' }))"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Mes clients"
                    :value="stats.myClients || 0"
                    icon-color="green"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Documents manquants"
                    :value="stats.missingDocuments || 0"
                    icon-color="red"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="RDV cette semaine"
                    :value="stats.weekAppointments || 0"
                    icon-color="blue"
                    clickable
                    @click="$inertia.visit(route('appointments.index'))"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </template>
                </StatCard>
            </div>

            <!-- Quick Actions -->
            <div class="mb-6">
                <Card title="Actions rapides">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <button
                            @click="$inertia.visit(route('clients.create'))"
                            class="flex flex-col items-center p-4 bg-brand-primary/10 hover:bg-brand-primary/20 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-brand-primary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Nouveau client</span>
                        </button>

                        <button
                            @click="$inertia.visit(route('dossiers.create'))"
                            class="flex flex-col items-center p-4 bg-eli-turquoise-100 hover:bg-eli-turquoise-200 dark:bg-eli-turquoise-900/20 dark:hover:bg-eli-turquoise-900/30 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-eli-turquoise-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Nouveau dossier</span>
                        </button>

                        <button
                            @click="$inertia.visit(route('appointments.create'))"
                            class="flex flex-col items-center p-4 bg-brand-accent/20 hover:bg-brand-accent/30 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-brand-orange mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">RDV</span>
                        </button>
                    </div>
                </Card>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- My Recent Dossiers -->
                <Card title="Mes dossiers récents">
                    <div class="space-y-3">
                        <div
                            v-for="dossier in recentDossiers.slice(0, 6)"
                            :key="dossier.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer"
                            @click="$inertia.visit(route('dossiers.show', dossier.id))"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ dossier.reference }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ dossier.client?.name }}</p>
                            </div>
                            <StatusBadge :status="dossier.status" type="dossier" />
                        </div>
                    </div>
                </Card>

                <!-- Tasks / Alerts -->
                <Card title="Actions requises">
                    <div class="space-y-3">
                        <div
                            v-for="action in pendingActions.slice(0, 6)"
                            :key="action.id"
                            class="flex items-start space-x-3 p-3 border-l-4 rounded-r-lg transition-colors"
                            :class="{
                                'border-red-500 bg-red-50 dark:bg-red-900/20': action.priority === 'high',
                                'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20': action.priority === 'medium',
                                'border-blue-500 bg-blue-50 dark:bg-blue-900/20': action.priority === 'low',
                            }"
                        >
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ action.title }}
                                </p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    {{ action.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useTranslation } from '@/composables/useTranslation';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentDossiers: {
        type: Array,
        default: () => [],
    },
    pendingActions: {
        type: Array,
        default: () => [],
    },
});

const { t } = useTranslation();
</script>
