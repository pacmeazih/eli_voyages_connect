<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ t('dashboard.title') }} - Super Admin
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Vue d'ensemble globale de la plateforme
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <StatCard
                    :label="t('dashboard.stats.totalDossiers')"
                    :value="stats.totalDossiers || 0"
                    icon-color="indigo"
                    :change="stats.dossiersChange"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    :label="t('dashboard.stats.totalClients')"
                    :value="stats.totalClients || 0"
                    icon-color="green"
                    :change="stats.clientsChange"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Consultants / Agents"
                    :value="stats.totalStaff || 0"
                    icon-color="blue"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    :label="t('dashboard.stats.documents')"
                    :value="stats.totalDocuments || 0"
                    icon-color="orange"
                    :change="stats.documentsChange"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </template>
                </StatCard>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- System Overview -->
                <Card title="Aperçu du système">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dossiers actifs</span>
                            <span class="text-lg font-bold text-brand-primary">{{ stats.activeDossiers || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">En attente de révision</span>
                            <span class="text-lg font-bold text-yellow-600">{{ stats.pendingReview || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Contrats en attente</span>
                            <span class="text-lg font-bold text-orange-600">{{ stats.pendingContracts || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Taux de complétion</span>
                            <span class="text-lg font-bold text-green-600">{{ stats.completionRate || 0 }}%</span>
                        </div>
                    </div>
                </Card>

                <!-- Recent Activity -->
                <Card :title="t('dashboard.recentActivity')">
                    <div class="space-y-3">
                        <div
                            v-for="activity in recentActivity.slice(0, 5)"
                            :key="activity.id"
                            class="flex items-start space-x-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-brand-primary/10 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-brand-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ activity.created_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Recent Dossiers -->
            <Card :title="t('dashboard.recentDossiers')">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Référence
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Client
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Assigné à
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Créé le
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="dossier in recentDossiers"
                                :key="dossier.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                                @click="$inertia.visit(route('dossiers.show', dossier.id))"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-brand-primary">
                                    {{ dossier.reference }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ dossier.client?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <StatusBadge :status="dossier.status" type="dossier" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ dossier.assigned_to?.name || 'Non assigné' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ dossier.created_at }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
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
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const { t } = useTranslation();
</script>
