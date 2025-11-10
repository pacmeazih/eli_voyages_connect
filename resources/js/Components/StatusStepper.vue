<template>
    <div class="w-full">
        <div class="relative">
            <!-- Progress Line -->
            <div class="absolute top-5 left-0 w-full h-1 bg-gray-200 dark:bg-gray-700 rounded-full">
                <div 
                    class="h-full bg-gradient-to-r from-brand-primary to-eli-turquoise-500 rounded-full transition-all duration-500"
                    :style="{ width: `${progressPercentage}%` }"
                ></div>
            </div>

            <!-- Steps -->
            <div class="relative flex justify-between">
                <div
                    v-for="(step, index) in statusSteps"
                    :key="step.value"
                    class="flex flex-col items-center"
                    :class="{ 'flex-1': index !== statusSteps.length - 1 }"
                >
                    <!-- Step Circle -->
                    <div 
                        class="relative z-10 w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all duration-300"
                        :class="getStepClasses(step.value)"
                    >
                        <!-- Completed Icon -->
                        <svg 
                            v-if="isCompleted(step.value)"
                            class="w-5 h-5 text-white" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path 
                                fill-rule="evenodd" 
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" 
                                clip-rule="evenodd" 
                            />
                        </svg>

                        <!-- Current/Pending Icon -->
                        <component 
                            v-else
                            :is="step.icon" 
                            class="w-5 h-5"
                            :class="isCurrent(step.value) ? 'text-white' : 'text-gray-400 dark:text-gray-500'"
                        />
                    </div>

                    <!-- Step Label -->
                    <div class="mt-3 text-center max-w-[120px]">
                        <p 
                            class="text-sm font-medium transition-colors"
                            :class="{
                                'text-brand-primary': isCurrent(step.value) || isCompleted(step.value),
                                'text-gray-500 dark:text-gray-400': !isCurrent(step.value) && !isCompleted(step.value)
                            }"
                        >
                            {{ step.label }}
                        </p>
                        <p 
                            v-if="step.date && (isCurrent(step.value) || isCompleted(step.value))"
                            class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                        >
                            {{ formatDate(step.date) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Status Description -->
        <div 
            v-if="showDescription"
            class="mt-6 p-4 rounded-lg border-l-4 transition-colors"
            :class="getCurrentStatusClasses()"
        >
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <component 
                        :is="currentStatusIcon" 
                        class="h-5 w-5"
                        :class="getCurrentIconColor()"
                    />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium" :class="getCurrentTextColor()">
                        {{ currentStatusLabel }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ currentStatusDescription }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentStatus: {
        type: String,
        required: true,
    },
    statusHistory: {
        type: Object,
        default: () => ({}),
    },
    showDescription: {
        type: Boolean,
        default: true,
    },
});

// Define status flow
const statusFlow = ['draft', 'awaiting_docs', 'under_review', 'waiting_payment', 'approved', 'closed'];

// Icon components as strings for dynamic rendering
const IconDocument = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    `
};

const IconUpload = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
    `
};

const IconSearch = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
    `
};

const IconCreditCard = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
    `
};

const IconCheck = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    `
};

const IconArchive = {
    template: `
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
        </svg>
    `
};

const statusSteps = computed(() => [
    {
        value: 'draft',
        label: 'Brouillon',
        icon: IconDocument,
        date: props.statusHistory.draft,
    },
    {
        value: 'awaiting_docs',
        label: 'En attente de documents',
        icon: IconUpload,
        date: props.statusHistory.awaiting_docs,
    },
    {
        value: 'under_review',
        label: 'En révision',
        icon: IconSearch,
        date: props.statusHistory.under_review,
    },
    {
        value: 'waiting_payment',
        label: 'En attente de paiement',
        icon: IconCreditCard,
        date: props.statusHistory.waiting_payment,
    },
    {
        value: 'approved',
        label: 'Approuvé',
        icon: IconCheck,
        date: props.statusHistory.approved,
    },
    {
        value: 'closed',
        label: 'Clôturé',
        icon: IconArchive,
        date: props.statusHistory.closed,
    },
]);

const currentStatusIndex = computed(() => {
    return statusFlow.indexOf(props.currentStatus);
});

const progressPercentage = computed(() => {
    if (currentStatusIndex.value === -1) return 0;
    return (currentStatusIndex.value / (statusFlow.length - 1)) * 100;
});

function isCurrent(status) {
    return status === props.currentStatus;
}

function isCompleted(status) {
    const stepIndex = statusFlow.indexOf(status);
    return stepIndex < currentStatusIndex.value;
}

function getStepClasses(status) {
    if (isCurrent(status)) {
        return 'bg-brand-primary border-brand-primary shadow-lg';
    } else if (isCompleted(status)) {
        return 'bg-brand-primary border-brand-primary';
    } else {
        return 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700';
    }
}

const currentStatusIcon = computed(() => {
    const step = statusSteps.value.find(s => s.value === props.currentStatus);
    return step?.icon || IconDocument;
});

const currentStatusLabel = computed(() => {
    const step = statusSteps.value.find(s => s.value === props.currentStatus);
    return step?.label || '';
});

const currentStatusDescription = computed(() => {
    const descriptions = {
        draft: 'Le dossier est en cours de création. Complétez toutes les informations nécessaires.',
        awaiting_docs: 'En attente des documents requis. Veuillez télécharger les documents manquants.',
        under_review: 'Le dossier est en cours d\'examen par notre équipe de consultants.',
        waiting_payment: 'Le dossier est approuvé. En attente du paiement pour finaliser le traitement.',
        approved: 'Le dossier a été approuvé. Les démarches sont en cours.',
        closed: 'Le dossier est clôturé. Toutes les démarches sont terminées.',
    };
    return descriptions[props.currentStatus] || '';
});

function getCurrentStatusClasses() {
    const classes = {
        draft: 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600',
        awaiting_docs: 'bg-orange-50 dark:bg-orange-900/20 border-orange-400',
        under_review: 'bg-blue-50 dark:bg-blue-900/20 border-blue-400',
        waiting_payment: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400',
        approved: 'bg-green-50 dark:bg-green-900/20 border-green-400',
        closed: 'bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-600',
    };
    return classes[props.currentStatus] || classes.draft;
}

function getCurrentIconColor() {
    const colors = {
        draft: 'text-gray-600',
        awaiting_docs: 'text-orange-600',
        under_review: 'text-blue-600',
        waiting_payment: 'text-yellow-600',
        approved: 'text-green-600',
        closed: 'text-gray-600',
    };
    return colors[props.currentStatus] || colors.draft;
}

function getCurrentTextColor() {
    const colors = {
        draft: 'text-gray-800 dark:text-gray-200',
        awaiting_docs: 'text-orange-800 dark:text-orange-200',
        under_review: 'text-blue-800 dark:text-blue-200',
        waiting_payment: 'text-yellow-800 dark:text-yellow-200',
        approved: 'text-green-800 dark:text-green-200',
        closed: 'text-gray-800 dark:text-gray-200',
    };
    return colors[props.currentStatus] || colors.draft;
}

function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>
