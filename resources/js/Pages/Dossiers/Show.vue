<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Link
                            :href="route('dossiers.index')"
                            class="mr-4 text-gray-500 hover:text-gray-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ dossier.reference }}</h1>
                            <p class="mt-1 text-sm text-gray-600">{{ dossier.title }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            :class="statusClass(dossier.status)"
                        >
                            {{ statusLabel(dossier.status) }}
                        </span>
                        <div v-if="canEdit" class="relative">
                            <Dropdown>
                                <template #trigger>
                                    <button class="p-2 rounded-md hover:bg-gray-100">
                                        <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('dossiers.edit', dossier.id)">
                                        Modifier
                                    </DropdownLink>
                                    <DropdownLink
                                        v-if="canDelete"
                                        as="button"
                                        @click="deleteDossier"
                                    >
                                        Supprimer
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="currentTab = tab.id"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        :class="currentTab === tab.id
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Overview Tab -->
            <div v-if="currentTab === 'overview'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <template #header>Informations du dossier</template>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Référence</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ dossier.reference }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="statusClass(dossier.status)"
                                    >
                                        {{ statusLabel(dossier.status) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Titre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ dossier.title }}</dd>
                            </div>
                            <div class="sm:col-span-2" v-if="dossier.description">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ dossier.description }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(dossier.created_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dernière mise à jour</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(dossier.updated_at) }}</dd>
                            </div>
                        </dl>
                    </Card>

                    <Card v-if="dossier.description">
                        <template #header>Notes</template>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ dossier.notes }}</p>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <Card>
                        <template #header>Client</template>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ dossier.client?.name }}</p>
                                <p class="text-sm text-gray-500">{{ dossier.client?.email }}</p>
                            </div>
                        </div>
                    </Card>

                    <Card>
                        <template #header>Statistiques</template>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Documents</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ dossier.documents_count || 0 }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Activités</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ activities.length }}</dd>
                            </div>
                        </dl>
                    </Card>

                    <Card v-if="canChangeStatus">
                        <template #header>Actions</template>
                        <div class="space-y-2">
                            <!-- Validate and Approve buttons -->
                            <PrimaryButton
                                v-if="canValidate && dossier.status === 'in_progress'"
                                @click="validateDossier"
                                class="w-full justify-center bg-green-600 hover:bg-green-700"
                            >
                                Valider le dossier
                            </PrimaryButton>
                            <PrimaryButton
                                v-if="canApprove && dossier.status === 'in_progress'"
                                @click="approveDossier"
                                class="w-full justify-center bg-blue-600 hover:bg-blue-700"
                            >
                                Approuver le dossier
                            </PrimaryButton>
                            
                            <!-- Status change buttons -->
                            <PrimaryButton
                                v-if="dossier.status === 'new'"
                                @click="updateStatus('in_progress')"
                                variant="primary"
                                class="w-full justify-center"
                            >
                                Démarrer
                            </PrimaryButton>
                            <PrimaryButton
                                v-if="dossier.status === 'in_progress'"
                                @click="updateStatus('completed')"
                                variant="primary"
                                class="w-full justify-center"
                            >
                                Marquer comme complété
                            </PrimaryButton>
                            <PrimaryButton
                                v-if="dossier.status === 'completed'"
                                @click="updateStatus('archived')"
                                variant="secondary"
                                class="w-full justify-center"
                            >
                                Archiver
                            </PrimaryButton>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Documents Tab -->
            <div v-if="currentTab === 'documents'">
                <Card>
                    <template #header>
                        <div class="flex justify-between items-center">
                            <span>Documents</span>
                            <div class="flex space-x-3">
                                <Link
                                    v-if="canGenerateContract"
                                    :href="route('dossiers.contracts.create', dossier.id)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Générer un contrat
                                </Link>
                                <PrimaryButton
                                    v-if="canUploadDocuments"
                                    @click="showUploadModal = true"
                                    variant="primary"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Ajouter un document
                                </PrimaryButton>
                            </div>
                        </div>
                    </template>

                    <div v-if="documents.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun document</h3>
                        <p class="mt-1 text-sm text-gray-500">Commencez par ajouter un document.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="document in documents"
                            :key="document.id"
                            class="py-4 flex items-center justify-between hover:bg-gray-50 px-2 -mx-2 rounded"
                        >
                            <div class="flex items-center min-w-0 flex-1">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4 min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ document.name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ document.type }} • {{ formatFileSize(document.size) }} • {{ formatDate(document.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex space-x-2">
                                <!-- View Document -->
                                <button
                                    v-if="canViewDocument(document)"
                                    @click="viewDocument(document)"
                                    class="text-brand-primary hover:text-brand-primary/80 transition-colors"
                                    title="Voir le document"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <!-- Download Document -->
                                <a
                                    :href="route('documents.download', document.id)"
                                    class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                    title="Télécharger le document"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                                <!-- Delete Document -->
                                <button
                                    v-if="canDeleteDocuments"
                                    @click="deleteDocument(document.id)"
                                    class="text-red-600 hover:text-red-900 transition-colors"
                                    title="Supprimer le document"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 1 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Timeline Tab -->
            <div v-if="currentTab === 'timeline'">
                <Card>
                    <template #header>Historique des activités</template>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <li v-for="(activity, idx) in activities" :key="activity.id">
                                <div class="relative pb-8">
                                    <span
                                        v-if="idx !== activities.length - 1"
                                        class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                    />
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                                <p class="text-xs text-gray-500">{{ activity.causer?.name || 'Système' }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                {{ formatDate(activity.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </Card>
            </div>
        </div>
        
        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUploadModal = false"></div>
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">Ajouter des documents</h3>
                            <div class="mt-4">
                                <DocumentUpload :dossier-id="dossier.id" @uploaded="refreshDocuments" />
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-white shadow-sm hover:bg-indigo-700 sm:ml-3 sm:w-auto" @click="showUploadModal = false">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import DocumentUpload from '@/Components/DocumentUpload.vue';

const props = defineProps({
    dossier: Object,
    documents: {
        type: Array,
        default: () => [],
    },
    activities: {
        type: Array,
        default: () => [],
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    canDelete: {
        type: Boolean,
        default: false,
    },
    canChangeStatus: {
        type: Boolean,
        default: false,
    },
    canUploadDocuments: {
        type: Boolean,
        default: false,
    },
    canDeleteDocuments: {
        type: Boolean,
        default: false,
    },
    canGenerateContract: {
        type: Boolean,
        default: false,
    },
    canValidate: {
        type: Boolean,
        default: false,
    },
    canApprove: {
        type: Boolean,
        default: false,
    },
});

const currentTab = ref('overview');
const showUploadModal = ref(false);

// Check URL hash on mount to switch to the correct tab
onMounted(() => {
    const hash = window.location.hash.replace('#', '');
    if (hash && tabs.find(tab => tab.id === hash)) {
        currentTab.value = hash;
    }
});

const tabs = [
    { id: 'overview', name: 'Aperçu' },
    { id: 'documents', name: 'Documents' },
    { id: 'timeline', name: 'Historique' },
];

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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const updateStatus = (status) => {
    if (confirm('Êtes-vous sûr de vouloir changer le statut ?')) {
        router.patch(route('dossiers.update', props.dossier.id), {
            status: status,
        });
    }
};

const validateDossier = () => {
    if (confirm('Valider ce dossier ?')) {
        router.post(route('dossiers.validate', props.dossier.id));
    }
};

const approveDossier = () => {
    if (confirm('Approuver ce dossier ?')) {
        router.post(route('dossiers.approve', props.dossier.id));
    }
};

const deleteDossier = () => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce dossier ? Cette action est irréversible.')) {
        router.delete(route('dossiers.destroy', props.dossier.id));
    }
};

const deleteDocument = (documentId) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
        router.delete(route('documents.destroy', documentId), {
            preserveScroll: true,
        });
    }
};

const canViewDocument = (document) => {
    // Can view if document has a path and is a viewable type
    if (!document.path || !document.mime_type) return false;
    
    const viewableTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];
    
    return viewableTypes.includes(document.mime_type);
};

const viewDocument = (document) => {
    // Open document in new tab using view route
    window.open(route('documents.view', document.id), '_blank');
};

const refreshDocuments = () => {
    router.reload({ only: ['documents'] });
};
</script>
