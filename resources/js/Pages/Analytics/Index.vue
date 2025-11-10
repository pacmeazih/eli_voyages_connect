<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Analytics & Statistiques</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Tableau de bord analytique avancé</p>
                </div>
                
                <!-- Period Selector -->
                <select
                    v-model="selectedPeriod"
                    @change="loadAnalytics"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 transition"
                >
                    <option value="7days">7 derniers jours</option>
                    <option value="30days">30 derniers jours</option>
                    <option value="12months">12 derniers mois</option>
                </select>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div v-else>
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Conversion Rate -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Taux de conversion</p>
                                <p class="text-3xl font-bold mt-2">{{ analytics.conversion_metrics?.conversion_rate }}%</p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Rate -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">Taux d'approbation</p>
                                <p class="text-3xl font-bold mt-2">{{ analytics.conversion_metrics?.approval_rate }}%</p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Avg Completion Time -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Temps moyen</p>
                                <p class="text-3xl font-bold mt-2">{{ analytics.performance_metrics?.avg_completion_time_days }} <span class="text-lg">jours</span></p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Growth -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Croissance mensuelle</p>
                                <p class="text-3xl font-bold mt-2">
                                    {{ analytics.performance_metrics?.monthly_growth_rate > 0 ? '+' : '' }}{{ analytics.performance_metrics?.monthly_growth_rate }}%
                                </p>
                            </div>
                            <div class="bg-white/20 rounded-full p-3">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Dossiers Over Time (Line Chart) -->
                    <ChartCard
                        title="Évolution des dossiers"
                        type="line"
                        :data="dossiersOverTimeData"
                        :height="80"
                    />

                    <!-- Dossiers by Status (Bar Chart) -->
                    <ChartCard
                        title="Dossiers par statut"
                        type="bar"
                        :data="dossiersByStatusData"
                        :height="80"
                    />
                </div>

                <!-- Packages Distribution (Pie Chart) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <ChartCard
                        title="Distribution des packages"
                        type="doughnut"
                        :data="packagesDistributionData"
                        :height="80"
                    />

                    <!-- Performance Metrics -->
                    <Card title="Métriques de performance">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-indigo-100 dark:bg-indigo-900 p-2 rounded-lg">
                                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Documents/Dossier</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ analytics.performance_metrics?.avg_documents_per_dossier }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-green-100 dark:bg-green-900 p-2 rounded-lg">
                                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Clients actifs (3 mois)</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ analytics.performance_metrics?.active_clients }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-yellow-100 dark:bg-yellow-900 p-2 rounded-lg">
                                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">En cours de traitement</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ analytics.conversion_metrics?.in_progress_count }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-red-100 dark:bg-red-900 p-2 rounded-lg">
                                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Taux de rejet</p>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ analytics.conversion_metrics?.rejection_rate }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import ChartCard from '@/Components/ChartCard.vue';
import axios from 'axios';

const analytics = ref({});
const loading = ref(true);
const selectedPeriod = ref('12months');

const loadAnalytics = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('analytics.data'), {
            params: { period: selectedPeriod.value }
        });
        analytics.value = response.data;
    } catch (error) {
        console.error('Error loading analytics:', error);
    } finally {
        loading.value = false;
    }
};

// Dossiers Over Time Chart Data
const dossiersOverTimeData = computed(() => {
    if (!analytics.value.dossiers_over_time) return null;
    
    return {
        labels: analytics.value.dossiers_over_time.map(item => item.label),
        datasets: [{
            label: 'Nombre de dossiers',
            data: analytics.value.dossiers_over_time.map(item => item.value),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    };
});

// Dossiers by Status Chart Data
const dossiersByStatusData = computed(() => {
    if (!analytics.value.dossiers_by_status) return null;
    
    const statusColors = {
        'draft': '#9ca3af',
        'pending': '#f59e0b',
        'in_progress': '#3b82f6',
        'approved': '#10b981',
        'rejected': '#ef4444',
        'completed': '#8b5cf6',
    };

    return {
        labels: analytics.value.dossiers_by_status.map(item => item.label),
        datasets: [{
            label: 'Nombre de dossiers',
            data: analytics.value.dossiers_by_status.map(item => item.value),
            backgroundColor: analytics.value.dossiers_by_status.map(item => statusColors[item.status] || '#9ca3af'),
            borderWidth: 0
        }]
    };
});

// Packages Distribution Chart Data
const packagesDistributionData = computed(() => {
    if (!analytics.value.packages_distribution) return null;
    
    const colors = [
        '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', 
        '#f59e0b', '#10b981', '#3b82f6', '#06b6d4'
    ];

    return {
        labels: analytics.value.packages_distribution.map(item => item.label),
        datasets: [{
            data: analytics.value.packages_distribution.map(item => item.value),
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    };
});

onMounted(() => {
    loadAnalytics();
});
</script>
