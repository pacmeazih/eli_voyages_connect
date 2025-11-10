<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ t('dashboard.title') }} - Consultant
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gérez vos dossiers et validez les documents
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <StatCard
                    label="Dossiers assignés"
                    :value="stats.assignedDossiers || 0"
                    icon-color="indigo"
                    clickable
                    @click="$inertia.visit(route('dossiers.index', { assigned_to: 'me' }))"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="À réviser"
                    :value="stats.toReview || 0"
                    icon-color="yellow"
                    clickable
                    @click="$inertia.visit(route('dossiers.index', { status: 'under_review' }))"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Documents en attente"
                    :value="stats.pendingDocuments || 0"
                    icon-color="orange"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    :label="t('dashboard.stats.appointments')"
                    :value="stats.upcomingAppointments || 0"
                    icon-color="green"
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
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <button
                            @click="$inertia.visit(route('dossiers.create'))"
                            class="flex flex-col items-center p-4 bg-brand-primary/10 hover:bg-brand-primary/20 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-brand-primary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Nouveau dossier</span>
                        </button>

                        <button
                            @click="$inertia.visit(route('dossiers.index', { status: 'under_review' }))"
                            class="flex flex-col items-center p-4 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/20 dark:hover:bg-yellow-900/30 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Réviser dossiers</span>
                        </button>

                        <button
                            @click="$inertia.visit(route('appointments.index'))"
                            class="flex flex-col items-center p-4 bg-green-100 hover:bg-green-200 dark:bg-green-900/20 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Rendez-vous</span>
                        </button>

                        <button
                            @click="$inertia.visit(route('analytics.page'))"
                            class="flex flex-col items-center p-4 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                        >
                            <svg class="h-8 w-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Analytics</span>
                        </button>
                    </div>
                </Card>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Dossiers to Review -->
                <Card title="Dossiers à réviser" class="h-full">
                    <div class="space-y-3">
                        <div
                            v-for="dossier in pendingActions.slice(0, 5)"
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

                <!-- Upcoming Appointments -->
                <Card :title="t('dashboard.upcomingAppointments')" class="h-full">
                    <div class="space-y-3">
                        <div
                            v-for="appointment in upcomingAppointments.slice(0, 5)"
                            :key="appointment.id"
                            class="flex items-start space-x-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer"
                            @click="$inertia.visit(route('appointments.show', appointment.id))"
                        >
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-brand-accent flex flex-col items-center justify-center">
                                    <span class="text-xs font-semibold text-brand-primary">{{ appointment.day }}</span>
                                    <span class="text-[10px] text-brand-primary">{{ appointment.month }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ appointment.title }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ appointment.time }} - {{ appointment.client_name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { useTranslation } from '@/composables/useTranslation';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
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
    upcomingAppointments: {
        type: Array,
        default: () => [],
    },
});

const { t } = useTranslation();
</script>
