<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Signature du contrat</h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Contrat: <span class="font-medium">{{ contract.reference }}</span>
                        </p>
                    </div>
                    <StatusBadge :status="contract.status" type="contract" />
                </div>
            </div>

            <!-- Contract Information -->
            <Card class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Client</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white">
                            {{ contract.client.nom }} {{ contract.client.prenom }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Type de contrat</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white">
                            {{ contract.type_label }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Dossier</p>
                        <Link 
                            :href="route('dossiers.show', contract.dossier_id)"
                            class="text-base font-medium text-brand-primary hover:underline"
                        >
                            {{ contract.dossier.reference }}
                        </Link>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Date de création</p>
                        <p class="text-base font-medium text-gray-900 dark:text-white">
                            {{ formatDate(contract.created_at) }}
                        </p>
                    </div>
                </div>
            </Card>

            <!-- Signature Status -->
            <div v-if="contract.status === 'pending_signature'" class="mb-6">
                <div class="bg-orange-50 dark:bg-orange-900/20 border-l-4 border-orange-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                En attente de signature
                            </h3>
                            <p class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                                Ce contrat nécessite votre signature électronique pour être validé.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else-if="contract.status === 'signed'" class="mb-6">
                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                Contrat signé
                            </h3>
                            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                                Ce contrat a été signé le {{ formatDate(contract.signed_at) }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DocuSeal Embedded Signature -->
            <Card v-if="contract.status === 'pending_signature' && contract.docuseal_submission_id">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Signer électroniquement
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Complétez les champs requis et signez le contrat ci-dessous
                    </p>
                </div>

                <!-- DocuSeal Embed -->
                <div class="relative bg-gray-50 dark:bg-gray-800 rounded-lg overflow-hidden" style="min-height: 600px;">
                    <iframe
                        v-if="docusealEmbedUrl"
                        :src="docusealEmbedUrl"
                        class="w-full h-full border-0"
                        style="min-height: 600px;"
                        @load="onDocuSealLoad"
                    ></iframe>
                    <div v-else class="flex items-center justify-center h-96">
                        <div class="text-center">
                            <svg class="animate-spin h-8 w-8 text-brand-primary mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Chargement du document...
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Contract Preview (Read-only for signed contracts) -->
            <Card v-else-if="contract.status === 'signed'">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Document signé
                    </h2>
                    <a
                        v-if="contract.signed_pdf_url"
                        :href="contract.signed_pdf_url"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-brand-primary text-white text-sm font-medium rounded-lg hover:bg-brand-primary/90 transition-colors"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Télécharger le PDF
                    </a>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                    <embed
                        v-if="contract.signed_pdf_url"
                        :src="contract.signed_pdf_url"
                        type="application/pdf"
                        class="w-full h-[800px] rounded-lg"
                    />
                </div>
            </Card>

            <!-- Signatures List -->
            <Card v-if="contract.signatures && contract.signatures.length > 0" class="mt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Signatures
                </h2>
                <div class="space-y-4">
                    <div
                        v-for="signature in contract.signatures"
                        :key="signature.id"
                        class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                    >
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center"
                                    :class="signature.signed_at ? 'bg-green-100 dark:bg-green-900' : 'bg-gray-200 dark:bg-gray-600'"
                                >
                                    <svg 
                                        v-if="signature.signed_at"
                                        class="h-5 w-5 text-green-600" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg 
                                        v-else
                                        class="h-5 w-5 text-gray-400" 
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ signature.signer_name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ signature.signer_email }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ signature.role_label }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p 
                                v-if="signature.signed_at"
                                class="text-sm font-medium text-green-600 dark:text-green-400"
                            >
                                Signé
                            </p>
                            <p 
                                v-else
                                class="text-sm font-medium text-orange-600 dark:text-orange-400"
                            >
                                En attente
                            </p>
                            <p 
                                v-if="signature.signed_at"
                                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                            >
                                {{ formatDate(signature.signed_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Actions -->
            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('dossiers.show', contract.dossier_id)"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-brand-primary"
                >
                    ← Retour au dossier
                </Link>

                <div class="flex space-x-3">
                    <button
                        v-if="contract.status === 'pending_signature' && !contract.docuseal_submission_id"
                        @click="sendForSignature"
                        :disabled="isSending"
                        class="px-4 py-2 bg-brand-primary text-white text-sm font-medium rounded-lg hover:bg-brand-primary/90 disabled:opacity-50 transition-colors"
                    >
                        {{ isSending ? 'Envoi...' : 'Envoyer pour signature' }}
                    </button>

                    <button
                        v-if="contract.status === 'pending_signature'"
                        @click="remindSigners"
                        :disabled="isReminding"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 transition-colors"
                    >
                        {{ isReminding ? 'Envoi...' : 'Relancer les signataires' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useUiStore } from '@/stores/ui';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const uiStore = useUiStore();

const props = defineProps({
    contract: { type: Object, required: true },
});

const docusealEmbedUrl = ref('');
const isSending = ref(false);
const isReminding = ref(false);

onMounted(() => {
    // Load DocuSeal embed URL if available
    if (props.contract.docuseal_submission_id) {
        loadDocuSealEmbed();
    }

    // Listen for signature completion events
    window.addEventListener('message', handleDocuSealMessage);
});

function loadDocuSealEmbed() {
    // Construct DocuSeal embed URL
    docusealEmbedUrl.value = route('contracts.docuseal-embed', {
        contract: props.contract.id,
        submission: props.contract.docuseal_submission_id,
    });
}

function handleDocuSealMessage(event) {
    // Handle messages from DocuSeal iframe
    if (event.data.type === 'docuseal:completed') {
        uiStore.showSuccess('Contrat signé avec succès !');
        // Refresh the page to show signed status
        setTimeout(() => {
            router.reload();
        }, 2000);
    }
}

function onDocuSealLoad() {
    console.log('DocuSeal iframe loaded');
}

async function sendForSignature() {
    isSending.value = true;
    try {
        router.post(
            route('contracts.send-for-signature', props.contract.id),
            {},
            {
                onSuccess: () => {
                    uiStore.showSuccess('Contrat envoyé pour signature');
                },
                onError: () => {
                    uiStore.showError('Erreur lors de l\'envoi');
                },
                onFinish: () => {
                    isSending.value = false;
                },
            }
        );
    } catch (error) {
        uiStore.showError('Erreur lors de l\'envoi');
        isSending.value = false;
    }
}

async function remindSigners() {
    isReminding.value = true;
    try {
        router.post(
            route('contracts.remind-signers', props.contract.id),
            {},
            {
                onSuccess: () => {
                    uiStore.showSuccess('Rappel envoyé aux signataires');
                },
                onError: () => {
                    uiStore.showError('Erreur lors de l\'envoi du rappel');
                },
                onFinish: () => {
                    isReminding.value = false;
                },
            }
        );
    } catch (error) {
        uiStore.showError('Erreur lors de l\'envoi du rappel');
        isReminding.value = false;
    }
}

function formatDate(date) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>
