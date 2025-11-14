<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Contrats</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Gérez et consultez vos contrats de prestation
                </p>
            </div>

            <!-- Contracts List -->
            <Card v-if="contracts.data && contracts.data.length > 0">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="contract in contracts.data"
                        :key="contract.id"
                        class="py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                    {{ contract.name || 'Contrat de prestation' }}
                                </h3>
                                <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span v-if="contract.dossier">
                                        📁 {{ contract.dossier.reference }}
                                    </span>
                                    <span v-if="contract.dossier?.client">
                                        👤 {{ contract.dossier.client.prenom }} {{ contract.dossier.client.nom }}
                                    </span>
                                    <span>
                                        📅 {{ formatDate(contract.created_at) }}
                                    </span>
                                    <span v-if="contract.size">
                                        📄 {{ formatFileSize(contract.size) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4 flex space-x-2">
                                <!-- View Button -->
                                <a
                                    v-if="contract.path"
                                    :href="route('documents.view', contract.id)"
                                    target="_blank"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    title="Voir le contrat"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Voir
                                </a>
                                <!-- Download Button -->
                                <a
                                    v-if="contract.path"
                                    :href="route('documents.download', contract.id)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary/90 transition-colors"
                                    title="Télécharger le contrat"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="contracts.last_page > 1" class="mt-6 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <Link
                            v-for="page in contracts.last_page"
                            :key="page"
                            :href="contracts.path + '?page=' + page"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                page === contracts.current_page
                                    ? 'z-10 bg-brand-primary border-brand-primary text-white'
                                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </nav>
                </div>
            </Card>

            <!-- Empty State -->
            <Card v-else>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun contrat</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Vos contrats apparaîtront ici une fois générés.
                    </p>
                </div>
            </Card>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';

defineProps({
    contracts: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
