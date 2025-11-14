<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dossiers</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Gérez tous les dossiers clients
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <Link
                        v-if="canCreate"
                        :href="route('dossiers.create')"
                        class="inline-flex items-center px-4 py-2 bg-brand-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-primary/90 transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouveau Dossier
                    </Link>
                </div>
            </div>

            <!-- Search and Filter -->
            <Card class="mb-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher par référence, titre ou client..."
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"
                            @input="searchDossiers"
                        />
                    </div>
                    <div>
                        <select
                            v-model="statusFilter"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"
                            @change="filterDossiers"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="new">Nouveau</option>
                            <option value="in_progress">En cours</option>
                            <option value="completed">Complété</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>
                </div>
            </Card>

            <!-- Dossiers Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="dossier in dossiers.data"
                    :key="dossier.id"
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm hover:shadow-md transition rounded-lg cursor-pointer"
                    @click="viewDossier(dossier.id)"
                >
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ dossier.reference }}
                                </h3>
                                <span
                                    class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="statusClass(dossier.status)"
                                >
                                    {{ statusLabel(dossier.status) }}
                                </span>
                            </div>
                            <div class="ml-2">
                                <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Title -->
                        <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">
                            {{ dossier.title }}
                        </p>

                        <!-- Client -->
                        <div class="mt-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            {{ dossier.client?.name || 'Client non assigné' }}
                        </div>

                        <!-- Documents count -->
                        <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ dossier.documents_count || 0 }} document(s)
                        </div>

                        <!-- Date -->
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Créé le {{ formatDate(dossier.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="dossiers.data.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun dossier</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Commencez par créer un nouveau dossier.</p>
                <div class="mt-6">
                    <Link
                        v-if="canCreate"
                        :href="route('dossiers.create')"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary/90 transition"
                    >
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Nouveau Dossier
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="dossiers.data.length > 0" class="mt-6">
                <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                    <div class="-mt-px flex w-0 flex-1">
                        <Link
                            v-if="dossiers.prev_page_url"
                            :href="dossiers.prev_page_url"
                            class="inline-flex items-center border-t-2 border-transparent pt-4 pr-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                        >
                            <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Précédent
                        </Link>
                    </div>
                    <div class="hidden md:-mt-px md:flex">
                        <span class="text-sm text-gray-700">
                            Page {{ dossiers.current_page }} sur {{ dossiers.last_page }}
                        </span>
                    </div>
                    <div class="-mt-px flex w-0 flex-1 justify-end">
                        <Link
                            v-if="dossiers.next_page_url"
                            :href="dossiers.next_page_url"
                            class="inline-flex items-center border-t-2 border-transparent pt-4 pl-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                        >
                            Suivant
                            <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                    </div>
                </nav>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    dossiers: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

let searchTimeout = null;

const searchDossiers = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('dossiers.index'), {
            search: search.value,
            status: statusFilter.value,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

const filterDossiers = () => {
    router.get(route('dossiers.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const viewDossier = (id) => {
    router.visit(route('dossiers.show', id));
};

const statusClass = (status) => {
    const classes = {
        'new': 'bg-blue-100 text-blue-800',
        'in_progress': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-green-100 text-green-800',
        'archived': 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const statusLabel = (status) => {
    const labels = {
        'new': 'Nouveau',
        'in_progress': 'En cours',
        'completed': 'Complété',
        'archived': 'Archivé',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>
