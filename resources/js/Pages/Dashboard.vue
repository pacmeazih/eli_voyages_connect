<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Tableau de bord</h1>
                <p class="mt-1 text-sm text-gray-600">Bienvenue sur votre espace ELI Voyages</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Total Dossiers -->
                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-indigo-500 rounded-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Dossiers</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ stats.totalDossiers }}</dd>
                            </dl>
                        </div>
                    </div>
                </Card>

                <!-- Active Dossiers -->
                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-green-500 rounded-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">En cours</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ stats.activeDossiers }}</dd>
                            </dl>
                        </div>
                    </div>
                </Card>

                <!-- Documents -->
                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-blue-500 rounded-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Documents</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ stats.totalDocuments }}</dd>
                            </dl>
                        </div>
                    </div>
                </Card>

                <!-- Pending Signatures -->
                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-yellow-500 rounded-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Signatures en attente</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ stats.pendingSignatures }}</dd>
                            </dl>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Dossiers -->
                <Card title="Dossiers Récents">
                    <div class="space-y-4">
                        <div
                            v-for="dossier in recentDossiers"
                            :key="dossier.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                            @click="visitDossier(dossier.id)"
                        >
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h4 class="text-sm font-semibold text-gray-900">{{ dossier.reference }}</h4>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full" 
                                        :class="statusClass(dossier.status)">
                                        {{ dossier.status }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ dossier.title }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ dossier.client?.name }}</p>
                            </div>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                        <div v-if="recentDossiers.length === 0" class="text-center py-8 text-gray-500">
                            Aucun dossier récent
                        </div>
                    </div>

                    <div class="mt-4">
                        <Link :href="route('dossiers.index')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Voir tous les dossiers →
                        </Link>
                    </div>
                </Card>

                <!-- Recent Activity -->
                <Card title="Activité Récente">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <li v-for="(activity, activityIdx) in recentActivities" :key="activity.id">
                                <div class="relative pb-8">
                                    <span v-if="activityIdx !== recentActivities.length - 1" 
                                        class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" 
                                        aria-hidden="true" />
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    {{ activity.description }}
                                                </p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                {{ activity.created_at }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div v-if="recentActivities.length === 0" class="text-center py-8 text-gray-500">
                        Aucune activité récente
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalDossiers: 0,
            activeDossiers: 0,
            totalDocuments: 0,
            pendingSignatures: 0,
        }),
    },
    recentDossiers: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
});

const visitDossier = (id) => {
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
</script>
