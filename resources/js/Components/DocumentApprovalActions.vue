<template>
    <div class="flex items-center gap-2">
        <!-- Approval Status Badge -->
        <span 
            v-if="document.approval_status"
            :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                statusClasses[document.approval_status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
            ]"
        >
            {{ statusLabels[document.approval_status] || document.approval_status }}
        </span>

        <!-- Action Buttons (for staff only, when document is pending) -->
        <template v-if="canApprove && document.approval_status === 'pending'">
            <button
                @click="openApproveConfirm"
                type="button"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm"
                title="Approuver le document"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Approuver
            </button>

            <button
                @click="openRejectModal"
                type="button"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm"
                title="Rejeter le document"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Rejeter
            </button>
        </template>

        <!-- Rejection Reason (if rejected) -->
        <div v-if="document.approval_status === 'rejected' && document.rejection_reason" class="ml-2">
            <button
                @click="showReasonTooltip = !showReasonTooltip"
                type="button"
                class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                title="Voir la raison du rejet"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </button>
            <div v-if="showReasonTooltip" class="absolute z-10 mt-2 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-w-sm">
                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mb-1">Raison du rejet:</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ document.rejection_reason }}</p>
            </div>
        </div>

        <!-- Approve Confirmation Dialog -->
        <TransitionRoot appear :show="showApproveConfirm" as="template">
            <Dialog as="div" @close="closeApproveConfirm" class="relative z-50">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/30" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Confirmer l'approbation
                                </DialogTitle>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                    Êtes-vous sûr de vouloir approuver ce document ? Le client sera notifié de cette validation.
                                </p>
                                <div class="flex justify-end gap-3">
                                    <button
                                        @click="closeApproveConfirm"
                                        type="button"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        @click="approveDocument"
                                        type="button"
                                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                                        :disabled="approveForm.processing"
                                    >
                                        <span v-if="approveForm.processing">Approbation...</span>
                                        <span v-else>Approuver</span>
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Reject Modal with Reason -->
        <TransitionRoot appear :show="showRejectModal" as="template">
            <Dialog as="div" @close="closeRejectModal" class="relative z-50">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black/30" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Rejeter le document
                                </DialogTitle>
                                <form @submit.prevent="rejectDocument">
                                    <div class="mb-4">
                                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Raison du rejet <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            id="rejection_reason"
                                            v-model="rejectForm.reason"
                                            rows="4"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                            :class="{ 'border-red-500': rejectForm.errors.reason }"
                                            placeholder="Expliquez pourquoi ce document est rejeté..."
                                            required
                                        ></textarea>
                                        <p v-if="rejectForm.errors.reason" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ rejectForm.errors.reason }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Le client recevra cette explication par email.
                                        </p>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button
                                            @click="closeRejectModal"
                                            type="button"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600"
                                        >
                                            Annuler
                                        </button>
                                        <button
                                            type="submit"
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                                            :disabled="rejectForm.processing || !rejectForm.reason"
                                        >
                                            <span v-if="rejectForm.processing">Rejet...</span>
                                            <span v-else>Rejeter</span>
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';

const props = defineProps({
    document: {
        type: Object,
        required: true,
    },
    canApprove: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['approved', 'rejected']);

const showApproveConfirm = ref(false);
const showRejectModal = ref(false);
const showReasonTooltip = ref(false);

const approveForm = useForm({});
const rejectForm = useForm({
    reason: '',
});

const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

const statusLabels = {
    pending: 'En attente',
    approved: 'Approuvé',
    rejected: 'Rejeté',
};

const openApproveConfirm = () => {
    showApproveConfirm.value = true;
};

const closeApproveConfirm = () => {
    showApproveConfirm.value = false;
};

const openRejectModal = () => {
    rejectForm.reset();
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
};

const approveDocument = () => {
    approveForm.post(route('documents.approve', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeApproveConfirm();
            emit('approved');
        },
    });
};

const rejectDocument = () => {
    rejectForm.post(route('documents.reject', props.document.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeRejectModal();
            emit('rejected');
        },
    });
};
</script>
