<template>
    <VerticalLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Invitations Clients</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Gérer les invitations et codes clients
                    </p>
                </div>
                <Link
                    :href="route('client-invitations.create')"
                    class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium shadow-sm flex items-center transition-colors"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle invitation
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Rechercher par nom, email ou code..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <select
                            v-model="filters.status"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 dark:bg-gray-700 dark:text-white"
                            @change="applyFilters"
                        >
                            <option value="">Tous les statuts</option>
                            <option value="pending">En attente</option>
                            <option value="sent">Envoyée</option>
                            <option value="accepted">Acceptée</option>
                            <option value="expired">Expirée</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div>
                        <button
                            @click="clearFilters"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </div>

            <!-- Invitations Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Client
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Code
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Contact
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="invitation in invitations.data"
                                :key="invitation.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                            >
                                <!-- Client Name -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold">
                                            {{ invitation.prenom.charAt(0) }}{{ invitation.nom.charAt(0) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ invitation.prenom }} {{ invitation.nom }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Invité par {{ invitation.inviter?.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Client Code -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                        {{ invitation.client_code }}
                                    </span>
                                </td>

                                <!-- Contact -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ invitation.email }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ invitation.telephone }}</div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span :class="getStatusClass(invitation.status)">
                                        {{ getStatusLabel(invitation.status) }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div v-if="invitation.sent_at">
                                        Envoyée: {{ formatDate(invitation.sent_at) }}
                                    </div>
                                    <div v-if="invitation.accepted_at" class="text-green-600 dark:text-green-400">
                                        Acceptée: {{ formatDate(invitation.accepted_at) }}
                                    </div>
                                    <div v-if="isExpired(invitation)" class="text-red-600 dark:text-red-400">
                                        Expire: {{ formatDate(invitation.expires_at) }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Resend Button -->
                                        <button
                                            v-if="invitation.status !== 'accepted'"
                                            @click="resendInvitation(invitation)"
                                            class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                            title="Renvoyer l'invitation"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </button>

                                        <!-- Copy Link -->
                                        <button
                                            v-if="invitation.status !== 'accepted'"
                                            @click="copyInvitationLink(invitation)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            title="Copier le lien"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            v-if="invitation.status !== 'accepted'"
                                            @click="deleteInvitation(invitation)"
                                            class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Supprimer"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="invitations.data.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune invitation</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Commencez par inviter un nouveau client.</p>
                </div>

                <!-- Pagination -->
                <div v-if="invitations.data.length > 0" class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Affichage <span class="font-medium">{{ invitations.from }}</span> à <span class="font-medium">{{ invitations.to }}</span>
                            sur <span class="font-medium">{{ invitations.total }}</span> résultats
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in invitations.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                    link.active
                                        ? 'bg-amber-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';

const props = defineProps({
    invitations: Object,
});

const filters = reactive({
    search: '',
    status: '',
});

let searchTimeout = null;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
};

const applyFilters = () => {
    router.get(route('client-invitations.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.search = '';
    filters.status = '';
    applyFilters();
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        sent: 'px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        accepted: 'px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        expired: 'px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || classes.pending;
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'En attente',
        sent: 'Envoyée',
        accepted: 'Acceptée',
        expired: 'Expirée',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const isExpired = (invitation) => {
    return invitation.expires_at && new Date(invitation.expires_at) < new Date();
};

const resendInvitation = (invitation) => {
    if (confirm(`Renvoyer l'invitation à ${invitation.prenom} ${invitation.nom} ?`)) {
        router.post(route('client-invitations.resend', invitation.id), {}, {
            preserveScroll: true,
        });
    }
};

const copyInvitationLink = async (invitation) => {
    const url = `${window.location.origin}/client-invitations/${invitation.invitation_token}`;
    try {
        await navigator.clipboard.writeText(url);
        alert('Lien copié dans le presse-papiers !');
    } catch (err) {
        console.error('Erreur copie:', err);
    }
};

const deleteInvitation = (invitation) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'invitation de ${invitation.prenom} ${invitation.nom} ?`)) {
        router.delete(route('client-invitations.destroy', invitation.id), {
            preserveScroll: true,
        });
    }
};
</script>
