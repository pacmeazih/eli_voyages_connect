<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Mes Dossiers</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Sélectionnez un dossier pour voir son suivi détaillé
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="dossier in dossiers"
                    :key="dossier.id"
                    :href="route('client.tracking.show', dossier.id)"
                    class="block"
                >
                    <Card class="hover:shadow-xl transition-shadow duration-300 cursor-pointer border-2 border-transparent hover:border-indigo-500">
                        <div class="p-6">
                            <!-- Status Badge -->
                            <div class="flex justify-end mb-4">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                    :class="statusClass(dossier.status)"
                                >
                                    {{ statusLabel(dossier.status) }}
                                </span>
                            </div>

                            <!-- Dossier Info -->
                            <div class="text-center">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    {{ dossier.reference }}
                                </h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    {{ dossier.title }}
                                </p>

                                <!-- Package Info -->
                                <div v-if="dossier.package" class="mb-4">
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                        {{ dossier.package.name }}
                                    </span>
                                </div>

                                <!-- Dates -->
                                <div class="text-xs text-gray-500 space-y-1">
                                    <div>
                                        Créé le: {{ formatDate(dossier.created_at) }}
                                    </div>
                                    <div>
                                        Mis à jour: {{ formatDate(dossier.updated_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-700">Progression</span>
                                    <span class="text-xs font-medium text-gray-700">{{ getProgress(dossier.status) }}%</span>
                                </div>
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                    <div
                                        :style="{ width: getProgress(dossier.status) + '%' }"
                                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600"
                                    ></div>
                                </div>
                            </div>

                            <!-- View Button -->
                            <div class="mt-6">
                                <button
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                                >
                                    Voir le suivi →
                                </button>
                            </div>
                        </div>
                    </Card>
                </Link>
            </div>

            <div v-if="dossiers.length === 0" class="text-center py-12">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun dossier</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Vous n'avez pas encore de dossier actif.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

defineProps({
    dossiers: {
        type: Array,
        default: () => [],
    },
});

const statusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'completed': 'bg-indigo-100 text-indigo-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const statusLabel = (status) => {
    const labels = {
        'draft': 'Brouillon',
        'pending': 'En attente',
        'in_progress': 'En cours',
        'approved': 'Approuvé',
        'rejected': 'Rejeté',
        'completed': 'Terminé',
    };
    return labels[status] || status;
};

const getProgress = (status) => {
    const progressMap = {
        'draft': 15,
        'pending': 30,
        'in_progress': 60,
        'approved': 85,
        'rejected': 50,
        'completed': 100,
    };
    return progressMap[status] || 0;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>
