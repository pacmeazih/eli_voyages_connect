<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Suivi de mon Dossier</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Suivez l'avancement de votre dossier d'immigration en temps réel
                </p>
            </div>

            <!-- Dossier Info Card -->
            <Card class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ dossier.reference }}</h2>
                        <p class="text-sm text-gray-600 mt-1">{{ dossier.title }}</p>
                    </div>
                    <div>
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium"
                            :class="statusClass(dossier.status)"
                        >
                            {{ statusLabel(dossier.status) }}
                        </span>
                    </div>
                </div>
            </Card>

            <!-- Progress Timeline -->
            <Card class="mb-8">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Progression du Dossier</h3>
                        <span class="text-sm text-gray-500">{{ progressPercentage }}% complété</span>
                    </div>
                </template>

                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="relative">
                        <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                            <div
                                :style="{ width: progressPercentage + '%' }"
                                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-600 transition-all duration-500"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Steps -->
                <div class="space-y-6">
                    <div
                        v-for="(step, index) in timelineSteps"
                        :key="index"
                        class="relative flex items-start group"
                    >
                        <!-- Step Indicator -->
                        <div class="flex-shrink-0 relative">
                            <div
                                class="h-10 w-10 rounded-full flex items-center justify-center ring-8 ring-white"
                                :class="stepCircleClass(step.status)"
                            >
                                <svg
                                    v-if="step.status === 'completed'"
                                    class="h-5 w-5 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else-if="step.status === 'current'"
                                    class="h-5 w-5 text-white animate-pulse"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <circle cx="10" cy="10" r="3" />
                                </svg>
                                <svg
                                    v-else
                                    class="h-5 w-5 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                </svg>
                            </div>
                            
                            <!-- Vertical Line -->
                            <div
                                v-if="index < timelineSteps.length - 1"
                                class="absolute top-10 left-5 -ml-px h-full w-0.5"
                                :class="step.status === 'completed' ? 'bg-indigo-600' : 'bg-gray-300'"
                            ></div>
                        </div>

                        <!-- Step Content -->
                        <div class="ml-4 min-w-0 flex-1 pb-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ step.title }}</h4>
                                    <p class="mt-1 text-sm text-gray-500">{{ step.description }}</p>
                                </div>
                                <div v-if="step.date" class="ml-4 flex-shrink-0 text-sm text-gray-500">
                                    {{ formatDate(step.date) }}
                                </div>
                            </div>

                            <!-- Documents for this step -->
                            <div v-if="step.documents && step.documents.length > 0" class="mt-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h5 class="text-xs font-medium text-gray-700 mb-2">Documents requis:</h5>
                                    <ul class="space-y-2">
                                        <li
                                            v-for="doc in step.documents"
                                            :key="doc.id"
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <div class="flex items-center">
                                                <svg
                                                    class="h-4 w-4 mr-2"
                                                    :class="doc.uploaded ? 'text-green-500' : 'text-gray-400'"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        v-if="doc.uploaded"
                                                        fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd"
                                                    />
                                                    <path
                                                        v-else
                                                        fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 000 2h4a1 1 0 100-2H8z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <span :class="doc.uploaded ? 'text-gray-900' : 'text-gray-500'">
                                                    {{ doc.name }}
                                                </span>
                                            </div>
                                            <span
                                                v-if="doc.uploaded"
                                                class="text-xs text-green-600 font-medium"
                                            >
                                                ✓ Uploadé
                                            </span>
                                            <span v-else class="text-xs text-yellow-600 font-medium">
                                                En attente
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div v-if="step.actions && step.actions.length > 0" class="mt-4 flex gap-2">
                                <button
                                    v-for="action in step.actions"
                                    :key="action.label"
                                    @click="handleAction(action.type)"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    {{ action.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Documents</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ documentsUploaded }} / {{ totalDocuments }}
                            </p>
                        </div>
                    </div>
                </Card>

                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Étapes complétées</p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ stepsCompleted }} / {{ totalSteps }}
                            </p>
                        </div>
                    </div>
                </Card>

                <Card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Temps écoulé</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ daysElapsed }} jours</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card>
                <template #header>Activité Récente</template>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li v-for="(activity, activityIdx) in recentActivities" :key="activity.id">
                            <div class="relative pb-8">
                                <span
                                    v-if="activityIdx !== recentActivities.length - 1"
                                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                    aria-hidden="true"
                                />
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span
                                            class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white"
                                        >
                                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-gray-700">{{ activity.description }}</p>
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
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    dossier: {
        type: Object,
        required: true,
    },
    timelineSteps: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
});

// Computed properties
const progressPercentage = computed(() => {
    if (!props.timelineSteps || props.timelineSteps.length === 0) return 0;
    const completed = props.timelineSteps.filter(step => step.status === 'completed').length;
    return Math.round((completed / props.timelineSteps.length) * 100);
});

const stepsCompleted = computed(() => {
    return props.timelineSteps.filter(step => step.status === 'completed').length;
});

const totalSteps = computed(() => {
    return props.timelineSteps.length;
});

const totalDocuments = computed(() => {
    let total = 0;
    props.timelineSteps.forEach(step => {
        if (step.documents) {
            total += step.documents.length;
        }
    });
    return total;
});

const documentsUploaded = computed(() => {
    let uploaded = 0;
    props.timelineSteps.forEach(step => {
        if (step.documents) {
            uploaded += step.documents.filter(doc => doc.uploaded).length;
        }
    });
    return uploaded;
});

const daysElapsed = computed(() => {
    const created = new Date(props.dossier.created_at);
    const now = new Date();
    const diffTime = Math.abs(now - created);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});

// Methods
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

const stepCircleClass = (status) => {
    if (status === 'completed') {
        return 'bg-indigo-600';
    } else if (status === 'current') {
        return 'bg-blue-600';
    } else {
        return 'bg-gray-300';
    }
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

const handleAction = (actionType) => {
    if (actionType === 'upload') {
        router.visit(route('documents.create', { dossier: props.dossier.id }));
    } else if (actionType === 'view_documents') {
        // Scroll to documents section or navigate
    }
};
</script>
