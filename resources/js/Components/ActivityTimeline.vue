<template>
    <div class="flow-root">
        <ul role="list" class="-mb-8">
            <li 
                v-for="(activity, index) in activities" 
                :key="activity.id"
            >
                <div class="relative pb-8">
                    <!-- Connector Line -->
                    <span 
                        v-if="index !== activities.length - 1"
                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" 
                        aria-hidden="true"
                    ></span>

                    <div class="relative flex space-x-3">
                        <!-- Icon -->
                        <div>
                            <span 
                                class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-900 transition-colors"
                                :class="getActivityIconClasses(activity.type)"
                            >
                                <component 
                                    :is="getActivityIcon(activity.type)" 
                                    class="h-4 w-4 text-white" 
                                    aria-hidden="true"
                                />
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1">
                            <div>
                                <p class="text-sm text-gray-900 dark:text-white font-medium">
                                    {{ activity.title }}
                                    <span 
                                        v-if="activity.user"
                                        class="font-normal text-gray-600 dark:text-gray-400"
                                    >
                                        par {{ activity.user.name }}
                                    </span>
                                </p>
                                <p 
                                    v-if="activity.description"
                                    class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ activity.description }}
                                </p>

                                <!-- Activity Details -->
                                <div v-if="activity.metadata" class="mt-2">
                                    <!-- Document Activity -->
                                    <div 
                                        v-if="activity.type === 'document_uploaded' || activity.type === 'document_validated'"
                                        class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>{{ activity.metadata.document_name }}</span>
                                    </div>

                                    <!-- Status Change Activity -->
                                    <div 
                                        v-if="activity.type === 'status_changed'"
                                        class="flex items-center space-x-2 mt-2"
                                    >
                                        <StatusBadge 
                                            v-if="activity.metadata.old_status"
                                            :status="activity.metadata.old_status" 
                                            type="dossier"
                                            size="sm"
                                        />
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                        <StatusBadge 
                                            :status="activity.metadata.new_status" 
                                            type="dossier"
                                            size="sm"
                                        />
                                    </div>

                                    <!-- Comment Activity -->
                                    <div 
                                        v-if="activity.type === 'comment_added' && activity.metadata.comment"
                                        class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                                    >
                                        <p class="text-sm text-gray-700 dark:text-gray-300 italic">
                                            "{{ activity.metadata.comment }}"
                                        </p>
                                    </div>

                                    <!-- Assignment Activity -->
                                    <div 
                                        v-if="activity.type === 'assigned' && activity.metadata.assigned_to"
                                        class="flex items-center space-x-2 mt-2"
                                    >
                                        <div class="flex items-center space-x-2 px-3 py-1 bg-brand-accent rounded-full">
                                            <div class="w-6 h-6 rounded-full bg-brand-primary flex items-center justify-center text-xs text-white font-bold">
                                                {{ activity.metadata.assigned_to.name.charAt(0) }}
                                            </div>
                                            <span class="text-sm text-brand-primary font-medium">
                                                {{ activity.metadata.assigned_to.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Time -->
                            <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                <time :datetime="activity.created_at">
                                    {{ formatActivityTime(activity.created_at) }}
                                </time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Empty State -->
        <div 
            v-if="activities.length === 0"
            class="text-center py-12"
        >
            <svg 
                class="mx-auto h-12 w-12 text-gray-400" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" 
                />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune activité</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                L'historique des activités apparaîtra ici
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
    activities: {
        type: Array,
        required: true,
    },
});

// Icon components
const IconCreate = {
    template: `
        <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
    `
};

const IconUpload = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 20 20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
    `
};

const IconCheck = {
    template: `
        <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
    `
};

const IconRefresh = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 20 20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
    `
};

const IconUser = {
    template: `
        <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
        </svg>
    `
};

const IconChat = {
    template: `
        <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" />
        </svg>
    `
};

const IconMail = {
    template: `
        <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
        </svg>
    `
};

const IconExclamation = {
    template: `
        <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
    `
};

function getActivityIcon(type) {
    const icons = {
        created: IconCreate,
        document_uploaded: IconUpload,
        document_validated: IconCheck,
        status_changed: IconRefresh,
        assigned: IconUser,
        comment_added: IconChat,
        email_sent: IconMail,
        payment_received: IconCheck,
        rejected: IconExclamation,
    };
    return icons[type] || IconCreate;
}

function getActivityIconClasses(type) {
    const classes = {
        created: 'bg-brand-primary',
        document_uploaded: 'bg-blue-500',
        document_validated: 'bg-green-500',
        status_changed: 'bg-eli-turquoise-500',
        assigned: 'bg-brand-accent text-brand-primary',
        comment_added: 'bg-gray-500',
        email_sent: 'bg-blue-400',
        payment_received: 'bg-green-600',
        rejected: 'bg-red-500',
    };
    return classes[type] || 'bg-gray-400';
}

function formatActivityTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) {
        return 'À l\'instant';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `Il y a ${minutes} min`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `Il y a ${hours}h`;
    } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `Il y a ${days}j`;
    } else {
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short',
            year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
        });
    }
}
</script>
