<template>
    <VerticalLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Suivi de mon dossier
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Suivez l'évolution de votre dossier en temps réel
                    </p>
                </div>

                <!-- Dossier Info Card -->
                <Card v-if="dossier" class="mb-8">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Dossier {{ dossier.reference }}
                                </h2>
                                <p class="text-gray-600 dark:text-gray-400 mt-1">
                                    {{ dossier.package?.name || 'Package non défini' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span :class="[
                                    'inline-flex px-3 py-1 rounded-full text-sm font-medium',
                                    getStatusColor(dossier.status)
                                ]">
                                    {{ getStatusLabel(dossier.status) }}
                                </span>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    Créé le {{ formatDate(dossier.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Avancement global
                                </span>
                                <span class="text-sm font-medium text-amber-600 dark:text-amber-400">
                                    {{ progressPercentage }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div 
                                    class="bg-gradient-to-r from-amber-400 to-orange-500 h-3 rounded-full transition-all duration-500"
                                    :style="{ width: progressPercentage + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Timeline -->
                <Card>
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                            Chronologie des activités
                        </h2>

                        <!-- Empty State -->
                        <div v-if="!activities || activities.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune activité</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Les activités de votre dossier apparaîtront ici
                            </p>
                        </div>

                        <!-- Timeline Items -->
                        <div v-else class="relative">
                            <div class="absolute left-6 top-0 h-full w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                            <div v-for="(activity, index) in activities" :key="activity.id" class="relative flex gap-4 pb-8 last:pb-0">
                                <!-- Icon -->
                                <div class="relative z-10 flex-shrink-0">
                                    <div :class="[
                                        'w-12 h-12 rounded-full flex items-center justify-center shadow-lg',
                                        getActivityIconColor(activity.properties?.status || 'default')
                                    ]">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.properties?.status || 'default')"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 pt-2">
                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                                {{ getActivityTitle(activity.properties?.status || 'default') }}
                                            </h3>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDateTime(activity.created_at) }}
                                            </span>
                                        </div>
                                        <p class="text-gray-700 dark:text-gray-300 text-sm">
                                            {{ activity.description }}
                                        </p>
                                        <div v-if="activity.causer" class="mt-2 flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-xs font-bold">
                                                {{ activity.causer.name.charAt(0) }}
                                            </div>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ activity.causer.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { computed } from 'vue';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    dossier: {
        type: Object,
        required: false
    },
    activities: {
        type: Array,
        default: () => []
    }
});

const progressPercentage = computed(() => {
    if (!props.dossier) return 0;
    
    const statusProgress = {
        'draft': 10,
        'submitted': 30,
        'in_progress': 50,
        'pending_documents': 60,
        'under_review': 70,
        'approved': 90,
        'completed': 100,
        'rejected': 0,
        'cancelled': 0
    };
    
    return statusProgress[props.dossier.status] || 0;
});

const getStatusColor = (status) => {
    const colors = {
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'submitted': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'in_progress': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'pending_documents': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
        'under_review': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
        'approved': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'completed': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'cancelled': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    };
    return colors[status] || colors.draft;
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Brouillon',
        'submitted': 'Soumis',
        'in_progress': 'En cours',
        'pending_documents': 'Documents requis',
        'under_review': 'En révision',
        'approved': 'Approuvé',
        'completed': 'Terminé',
        'rejected': 'Rejeté',
        'cancelled': 'Annulé'
    };
    return labels[status] || status;
};

const getActivityIconColor = (type) => {
    const colors = {
        'created': 'bg-gradient-to-br from-blue-500 to-blue-600',
        'document_uploaded': 'bg-gradient-to-br from-green-500 to-green-600',
        'verification': 'bg-gradient-to-br from-orange-500 to-orange-600',
        'approved': 'bg-gradient-to-br from-emerald-500 to-emerald-600',
        'contract_generated': 'bg-gradient-to-br from-amber-500 to-amber-600',
        'processing': 'bg-gradient-to-br from-purple-500 to-purple-600',
        'completed': 'bg-gradient-to-br from-green-600 to-green-700',
        'default': 'bg-gradient-to-br from-gray-500 to-gray-600'
    };
    return colors[type] || colors.default;
};

const getActivityIcon = (type) => {
    const icons = {
        'created': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'document_uploaded': 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
        'verification': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'approved': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'contract_generated': 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'processing': 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'completed': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'default': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    };
    return icons[type] || icons.default;
};

const getActivityTitle = (type) => {
    const titles = {
        'created': 'Dossier créé',
        'document_uploaded': 'Document ajouté',
        'verification': 'Vérification en cours',
        'approved': 'Approuvé',
        'contract_generated': 'Contrat généré',
        'processing': 'Traitement en cours',
        'completed': 'Finalisé',
        'default': 'Activité'
    };
    return titles[type] || titles.default;
};

const formatDate = (date) => {
    if (!date) return '';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('fr-FR', options);
};

const formatDateTime = (date) => {
    if (!date) return '';
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(date).toLocaleDateString('fr-FR', options);
};
</script>
