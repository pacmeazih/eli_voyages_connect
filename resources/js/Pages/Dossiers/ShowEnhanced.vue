<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Link
                            :href="route('dossiers.index')"
                            class="mr-4 text-gray-500 hover:text-brand-primary dark:text-gray-400 dark:hover:text-brand-accent transition-colors"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ dossier.reference }}</h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ dossier.title }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <StatusBadge :status="dossier.status" type="dossier" />
                        <div v-if="userStore.can('dossiers.edit')" class="relative">
                            <Dropdown>
                                <template #trigger>
                                    <button class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('dossiers.edit', dossier.id)">
                                        ✏️ Modifier
                                    </DropdownLink>
                                    <DropdownLink
                                        v-if="userStore.can('dossiers.delete')"
                                        as="button"
                                        @click="deleteDossier"
                                    >
                                        🗑️ Supprimer
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Stepper -->
            <div class="mb-8">
                <Card>
                    <StatusStepper
                        :current-status="dossier.status"
                        :status-history="dossier.status_history || {}"
                        :show-description="true"
                    />
                </Card>
            </div>

            <!-- Tabs -->
            <div class="mb-6">
                <nav class="flex space-x-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="currentTab = tab.id"
                        class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors"
                        :class="currentTab === tab.id
                            ? 'bg-white dark:bg-gray-700 text-brand-primary shadow'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Overview Tab -->
            <div v-show="currentTab === 'overview'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Dossier Information -->
                    <Card title="Informations du dossier">
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Référence</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">
                                    {{ dossier.reference }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ dossier.type_label || dossier.type }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Client</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <Link 
                                        :href="route('clients.show', dossier.client_id)"
                                        class="text-brand-primary hover:underline"
                                    >
                                        {{ dossier.client?.nom }} {{ dossier.client?.prenom }}
                                    </Link>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date de création</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ formatDate(dossier.created_at) }}
                                </dd>
                            </div>
                            <div v-if="dossier.consultant">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Consultant</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ dossier.consultant.name }}
                                </dd>
                            </div>
                            <div v-if="dossier.agent">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Agent</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ dossier.agent.name }}
                                </dd>
                            </div>
                            <div v-if="dossier.package" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Forfait</dt>
                                <dd class="mt-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-900 dark:text-white">
                                            {{ dossier.package.name }}
                                        </span>
                                        <span class="text-lg font-bold text-brand-primary">
                                            {{ formatCurrency(dossier.package.price) }}
                                        </span>
                                    </div>
                                </dd>
                            </div>
                        </dl>

                        <div v-if="dossier.description" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Description</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ dossier.description }}
                            </dd>
                        </div>
                    </Card>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <StatCard
                            label="Documents"
                            :value="dossier.documents_count || 0"
                            icon-color="blue"
                            clickable
                            @click="currentTab = 'documents'"
                        >
                            <template #icon>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </template>
                        </StatCard>

                        <StatCard
                            label="Contrats"
                            :value="dossier.contracts_count || 0"
                            icon-color="green"
                            clickable
                            @click="currentTab = 'contracts'"
                        >
                            <template #icon>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </template>
                        </StatCard>

                        <StatCard
                            label="Paiements"
                            :value="formatCurrency(dossier.total_paid || 0)"
                            icon-color="green"
                        >
                            <template #icon>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template #value>
                                <span class="text-lg">{{ formatCurrency(dossier.total_paid || 0) }}</span>
                            </template>
                        </StatCard>
                    </div>

                    <!-- Activity Timeline -->
                    <Card title="Historique des activités">
                        <ActivityTimeline :activities="activities" />
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Quick Actions -->
                    <Card title="Actions rapides">
                        <div class="space-y-3">
                            <Link
                                v-if="userStore.can('documents.create')"
                                :href="route('dossiers.documents.index', dossier.id)"
                                class="block w-full px-4 py-3 bg-brand-primary text-white text-center rounded-lg hover:bg-brand-primary/90 transition-colors"
                            >
                                📄 Gérer les documents
                            </Link>
                            <Link
                                v-if="userStore.can('contracts.create')"
                                :href="route('contracts.generate', dossier.id)"
                                class="block w-full px-4 py-3 bg-eli-turquoise-500 text-white text-center rounded-lg hover:bg-eli-turquoise-600 transition-colors"
                            >
                                📝 Générer un contrat
                            </Link>
                            <button
                                v-if="userStore.can('dossiers.edit')"
                                @click="updateStatus"
                                class="block w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-center rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            >
                                🔄 Changer le statut
                            </button>
                        </div>
                    </Card>

                    <!-- Team Members -->
                    <Card title="Équipe assignée">
                        <div class="space-y-4">
                            <div v-if="dossier.consultant" class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-brand-accent flex items-center justify-center text-brand-primary font-bold">
                                    {{ dossier.consultant.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ dossier.consultant.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Consultant</p>
                                </div>
                            </div>
                            <div v-if="dossier.agent" class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-eli-turquoise-100 flex items-center justify-center text-eli-turquoise-600 font-bold">
                                    {{ dossier.agent.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ dossier.agent.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Agent</p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Important Dates -->
                    <Card title="Dates importantes">
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Création</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(dossier.created_at) }}
                                </p>
                            </div>
                            <div v-if="dossier.submitted_at">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Soumis</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ formatDate(dossier.submitted_at) }}
                                </p>
                            </div>
                            <div v-if="dossier.deadline">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Date limite</p>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                    {{ formatDate(dossier.deadline) }}
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Documents Tab -->
            <div v-show="currentTab === 'documents'">
                <Card title="Documents du dossier">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ dossier.documents_count || 0 }} document(s)
                        </p>
                        <Link
                            v-if="userStore.can('documents.create')"
                            :href="route('dossiers.documents.index', dossier.id)"
                            class="px-4 py-2 bg-brand-primary text-white text-sm rounded-lg hover:bg-brand-primary/90 transition-colors"
                        >
                            Gérer les documents →
                        </Link>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="doc in documents.slice(0, 5)"
                            :key="doc.id"
                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ doc.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ doc.type }}</p>
                                </div>
                            </div>
                            <StatusBadge :status="doc.status" type="document" size="sm" />
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Contracts Tab -->
            <div v-show="currentTab === 'contracts'">
                <Card title="Contrats">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ contracts.length }} contrat(s)
                        </p>
                        <Link
                            v-if="userStore.can('contracts.create')"
                            :href="route('contracts.generate', dossier.id)"
                            class="px-4 py-2 bg-brand-primary text-white text-sm rounded-lg hover:bg-brand-primary/90 transition-colors"
                        >
                            + Générer un contrat
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="contract in contracts"
                            :key="contract.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ contract.type_label }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(contract.created_at) }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <StatusBadge :status="contract.status" type="contract" size="sm" />
                                <Link
                                    :href="route('contracts.show', contract.id)"
                                    class="text-brand-primary hover:underline text-sm"
                                >
                                    Voir →
                                </Link>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Activity Tab -->
            <div v-show="currentTab === 'activity'">
                <Card title="Toutes les activités">
                    <ActivityTimeline :activities="activities" />
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useUserStore } from '@/stores/user';
import { useUIStore } from '@/stores/ui';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import StatusStepper from '@/Components/StatusStepper.vue';
import ActivityTimeline from '@/Components/ActivityTimeline.vue';
import StatCard from '@/Components/StatCard.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const userStore = useUserStore();
const uiStore = useUIStore();

const props = defineProps({
    dossier: { type: Object, required: true },
    documents: { type: Array, default: () => [] },
    contracts: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
});

const tabs = [
    { id: 'overview', name: 'Vue d\'ensemble' },
    { id: 'documents', name: 'Documents' },
    { id: 'contracts', name: 'Contrats' },
    { id: 'activity', name: 'Activité' },
];

const currentTab = ref('overview');

function formatDate(date) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'CAD',
    }).format(amount);
}

function deleteDossier() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?')) {
        router.delete(route('dossiers.destroy', props.dossier.id));
    }
}

function updateStatus() {
    // TODO: Open modal to update status
    uiStore.showWarning('Fonctionnalité à venir');
}
</script>
