<template>
    <span 
        :class="badgeClasses"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors"
    >
        <span 
            v-if="showDot" 
            class="w-1.5 h-1.5 rounded-full mr-1.5"
            :class="dotClasses"
        ></span>
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: 'dossier', // 'dossier', 'contract', 'document', 'payment', 'custom'
    },
    showDot: {
        type: Boolean,
        default: true,
    },
});

// Status configurations for different types
const statusConfig = {
    dossier: {
        draft: { label: 'Brouillon', color: 'gray' },
        awaiting_docs: { label: 'En attente documents', color: 'yellow' },
        under_review: { label: 'En révision', color: 'blue' },
        waiting_payment: { label: 'En attente paiement', color: 'orange' },
        approved: { label: 'Approuvé', color: 'green' },
        rejected: { label: 'Rejeté', color: 'red' },
        closed: { label: 'Clôturé', color: 'gray' },
        cancelled: { label: 'Annulé', color: 'red' },
    },
    contract: {
        draft: { label: 'Brouillon', color: 'gray' },
        generated: { label: 'Généré', color: 'blue' },
        sent: { label: 'Envoyé', color: 'indigo' },
        signed: { label: 'Signé', color: 'green' },
        expired: { label: 'Expiré', color: 'red' },
        cancelled: { label: 'Annulé', color: 'red' },
    },
    document: {
        pending: { label: 'En attente', color: 'yellow' },
        uploaded: { label: 'Téléchargé', color: 'blue' },
        verified: { label: 'Vérifié', color: 'green' },
        rejected: { label: 'Rejeté', color: 'red' },
    },
    payment: {
        pending: { label: 'En attente', color: 'yellow' },
        processing: { label: 'En traitement', color: 'blue' },
        completed: { label: 'Complété', color: 'green' },
        failed: { label: 'Échoué', color: 'red' },
        refunded: { label: 'Remboursé', color: 'orange' },
    },
};

const colorClasses = {
    gray: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    indigo: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    green: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    orange: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
};

const dotColorClasses = {
    gray: 'bg-gray-400 dark:bg-gray-500',
    yellow: 'bg-yellow-400 dark:bg-yellow-500',
    blue: 'bg-blue-400 dark:bg-blue-500',
    indigo: 'bg-indigo-400 dark:bg-indigo-500',
    green: 'bg-green-400 dark:bg-green-500',
    red: 'bg-red-400 dark:bg-red-500',
    orange: 'bg-orange-400 dark:bg-orange-500',
};

const currentConfig = computed(() => {
    const typeConfig = statusConfig[props.type] || {};
    return typeConfig[props.status] || { label: props.status, color: 'gray' };
});

const label = computed(() => currentConfig.value.label);
const color = computed(() => currentConfig.value.color);

const badgeClasses = computed(() => colorClasses[color.value] || colorClasses.gray);
const dotClasses = computed(() => dotColorClasses[color.value] || dotColorClasses.gray);
</script>
