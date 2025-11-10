<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Header -->
            <div class="mb-8 bg-gradient-to-r from-eli-turquoise-600 to-brand-primary rounded-lg p-8 text-white shadow-lg">
                <h1 class="text-3xl font-bold mb-2">
                    Bienvenue, {{ userStore.user?.name }} 👋
                </h1>
                <p class="text-brand-accent">
                    Suivez le dossier pour lequel vous êtes garant
                </p>
            </div>

            <!-- Dossier Overview -->
            <div v-if="stats.guaranteedDossier" class="mb-8">
                <Card title="Dossier dont je suis garant">
                    <div class="space-y-4">
                        <!-- Dossier Info -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Référence</p>
                                <p class="text-xl font-bold text-brand-primary">{{ stats.guaranteedDossier.reference }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Client: <span class="font-medium text-gray-900 dark:text-white">{{ stats.guaranteedDossier.client_name }}</span>
                                </p>
                            </div>
                            <StatusBadge :status="stats.guaranteedDossier.status" type="dossier" />
                        </div>

                        <!-- Progress -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progression</span>
                                <span class="text-sm font-medium text-brand-primary">{{ stats.guaranteedDossier.progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div 
                                    class="bg-gradient-to-r from-eli-turquoise-500 to-brand-primary h-3 rounded-full transition-all duration-500"
                                    :style="{ width: `${stats.guaranteedDossier.progress}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                <StatCard
                    label="Documents signés"
                    :value="stats.signedDocuments || 0"
                    icon-color="green"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Documents en attente"
                    :value="stats.pendingDocuments || 0"
                    icon-color="orange"
                    clickable
                    @click="scrollToPendingDocuments"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </StatCard>

                <StatCard
                    label="Notifications"
                    :value="stats.unreadNotifications || 0"
                    icon-color="blue"
                    clickable
                    @click="$inertia.visit(route('notifications.page'))"
                >
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </template>
                </StatCard>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <Card title="Activité récente">
                    <div class="space-y-4">
                        <div
                            v-for="activity in recentActivity.slice(0, 5)"
                            :key="activity.id"
                            class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-8 h-8 rounded-full bg-brand-primary/10 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-brand-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ activity.title }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ activity.date }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Pending Documents -->
                <Card title="Documents en attente de signature" id="pending-documents">
                    <div class="space-y-3">
                        <div
                            v-for="doc in pendingActions"
                            :key="doc.id"
                            class="flex items-center justify-between p-4 border-2 border-dashed border-orange-300 bg-orange-50 dark:bg-orange-900/20 rounded-lg"
                        >
                            <div class="flex items-center space-x-3">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ doc.name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ doc.description }}</p>
                                </div>
                            </div>
                            <button
                                @click="$inertia.visit(doc.sign_url)"
                                class="px-4 py-2 text-sm bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 transition-colors"
                            >
                                Signer
                            </button>
                        </div>

                        <div v-if="pendingActions.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Aucun document en attente
                            </p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Help Section -->
            <div class="mt-6">
                <Card>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-brand-accent flex items-center justify-center">
                                <svg class="h-6 w-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Besoin d'aide ?</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Si vous avez des questions concernant votre rôle de garant ou les documents à signer, 
                                n'hésitez pas à contacter notre équipe.
                            </p>
                            <button class="mt-3 px-4 py-2 text-sm bg-brand-primary text-white rounded-lg hover:bg-brand-primary/90 transition-colors">
                                Contacter le support
                            </button>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { useUserStore } from '@/stores/user';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const userStore = useUserStore();

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
    pendingActions: {
        type: Array,
        default: () => [],
    },
});

function scrollToPendingDocuments() {
    document.getElementById('pending-documents')?.scrollIntoView({ behavior: 'smooth' });
}
</script>
