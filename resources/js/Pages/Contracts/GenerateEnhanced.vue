<template>
    <VerticalLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Link
                            :href="route('dossiers.show', dossier.id)"
                            class="mr-4 text-gray-500 hover:text-brand-primary dark:text-gray-400 dark:hover:text-brand-accent transition-colors"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Générer un Contrat</h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Dossier: <span class="font-medium text-brand-primary">{{ dossier.reference }}</span> - {{ client.nom }} {{ client.prenom }}
                            </p>
                        </div>
                    </div>
                    <StatusBadge :status="dossier.status" type="dossier" />
                </div>
            </div>

            <!-- Progress Stepper -->
            <div class="mb-8">
                <nav aria-label="Progress">
                    <ol class="flex items-center">
                        <li 
                            v-for="(step, index) in steps" 
                            :key="index"
                            class="relative"
                            :class="{ 'pr-8 sm:pr-20 flex-1': index !== steps.length - 1 }"
                        >
                            <!-- Connector Line -->
                            <div 
                                v-if="index !== steps.length - 1"
                                class="absolute top-4 left-0 right-0 -translate-x-1/2 ml-8 w-full"
                            >
                                <div 
                                    class="h-0.5 w-full transition-colors"
                                    :class="currentStep > index ? 'bg-brand-primary' : 'bg-gray-200 dark:bg-gray-700'"
                                ></div>
                            </div>

                            <!-- Step Circle -->
                            <div
                                class="relative flex flex-col items-center group"
                            >
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-colors relative z-10"
                                    :class="{
                                        'bg-brand-primary text-white': currentStep === index,
                                        'bg-brand-primary/20 text-brand-primary': currentStep > index,
                                        'bg-gray-200 dark:bg-gray-700 text-gray-500': currentStep < index,
                                    }"
                                >
                                    <svg v-if="currentStep > index" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <span 
                                    class="mt-2 text-xs font-medium whitespace-nowrap transition-colors"
                                    :class="{
                                        'text-brand-primary': currentStep >= index,
                                        'text-gray-500 dark:text-gray-400': currentStep < index
                                    }"
                                >
                                    {{ step }}
                                </span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <!-- Step 1: Contract Type & Package -->
                        <div v-show="currentStep === 0" class="space-y-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    Type de contrat et forfait
                                </h2>

                                <FormField
                                    label="Type de contrat"
                                    :error="form.errors.contract_type"
                                    required
                                >
                                    <select
                                        v-model="form.contract_type"
                                        class="input-field"
                                        :class="{ 'border-red-500': form.errors.contract_type }"
                                        @change="onContractTypeChange"
                                    >
                                        <option value="">Sélectionnez un type</option>
                                        <optgroup label="Études">
                                            <option value="etude">Études (1er cycle)</option>
                                            <option value="etude_2e_3e_cycle">Études (2e/3e cycle)</option>
                                            <option value="etude_garant">Études (avec garant)</option>
                                        </optgroup>
                                        <optgroup label="Immigration">
                                            <option value="permis_travail">Permis de travail</option>
                                            <option value="entree_express">Entrée Express</option>
                                            <option value="lmia">LMIA</option>
                                            <option value="csq_quebec">CSQ Québec</option>
                                            <option value="citoyennete">Citoyenneté canadienne</option>
                                        </optgroup>
                                        <optgroup label="Visas">
                                            <option value="visa_visiteur">Visa visiteur</option>
                                            <option value="super_visa">Super Visa</option>
                                            <option value="ave">AVE (eTA)</option>
                                        </optgroup>
                                        <optgroup label="Famille">
                                            <option value="parrainage_familial">Parrainage familial</option>
                                        </optgroup>
                                        <optgroup label="Autres Services">
                                            <option value="restauration_prolongation">Restauration/Prolongation</option>
                                            <option value="demande_asile">Demande d'asile</option>
                                            <option value="traduction_documents">Traduction de documents</option>
                                        </optgroup>
                                    </select>
                                </FormField>

                                <FormField
                                    label="Forfait"
                                    :error="form.errors.package_id"
                                    help="Sélectionnez le forfait applicable"
                                >
                                    <select
                                        v-model="form.package_id"
                                        class="input-field"
                                        :class="{ 'border-red-500': form.errors.package_id }"
                                    >
                                        <option value="">Sans forfait</option>
                                        <option 
                                            v-for="pkg in availablePackages" 
                                            :key="pkg.id"
                                            :value="pkg.id"
                                        >
                                            {{ pkg.name }} - {{ formatCurrency(pkg.price) }}
                                        </option>
                                    </select>
                                </FormField>

                                <FormField
                                    label="Langue du contrat"
                                    :error="form.errors.language"
                                >
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input
                                                v-model="form.language"
                                                type="radio"
                                                value="fr"
                                                class="form-radio text-brand-primary"
                                            />
                                            <span class="ml-2">🇫🇷 Français</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input
                                                v-model="form.language"
                                                type="radio"
                                                value="en"
                                                class="form-radio text-brand-primary"
                                            />
                                            <span class="ml-2">🇬🇧 English</span>
                                        </label>
                                    </div>
                                </FormField>
                            </div>
                        </div>

                        <!-- Step 2: Contract Details -->
                        <div v-show="currentStep === 1" class="space-y-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    Informations du contrat
                                </h2>

                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        <p class="text-sm text-blue-800 dark:text-blue-200">
                                            Les informations sont pré-remplies depuis le dossier. Modifiez-les si nécessaire.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <FormField
                                        v-for="(value, key) in form.variables"
                                        :key="key"
                                        :label="formatVariableName(key)"
                                        :error="form.errors[`variables.${key}`]"
                                    >
                                        <input
                                            v-model="form.variables[key]"
                                            type="text"
                                            class="input-field"
                                        />
                                    </FormField>
                                </div>

                                <div class="mt-6">
                                    <FormField
                                        label="Notes additionnelles"
                                        help="Ajoutez des notes spécifiques pour ce contrat"
                                    >
                                        <textarea
                                            v-model="form.notes"
                                            rows="4"
                                            class="input-field"
                                        ></textarea>
                                    </FormField>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Preview & Confirm -->
                        <div v-show="currentStep === 2" class="space-y-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    Prévisualisation et confirmation
                                </h2>

                                <div class="mb-6">
                                    <button
                                        @click="loadPreview"
                                        type="button"
                                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center"
                                        :disabled="isLoadingPreview"
                                    >
                                        <svg 
                                            class="h-4 w-4 mr-2"
                                            :class="{ 'animate-spin': isLoadingPreview }"
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        {{ isLoadingPreview ? 'Chargement...' : 'Actualiser la prévisualisation' }}
                                    </button>
                                </div>

                                <div 
                                    v-if="previewHtml"
                                    class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg p-8 max-h-[600px] overflow-y-auto shadow-inner"
                                >
                                    <div class="prose prose-sm dark:prose-invert max-w-none" v-html="previewHtml"></div>
                                </div>

                                <div v-else class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Cliquez sur "Actualiser" pour charger la prévisualisation
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button
                                v-if="currentStep > 0"
                                type="button"
                                @click="previousStep"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                ← Précédent
                            </button>
                            <div v-else></div>

                            <div class="flex space-x-3">
                                <Link
                                    :href="route('dossiers.show', dossier.id)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    Annuler
                                </Link>

                                <button
                                    v-if="currentStep < steps.length - 1"
                                    type="button"
                                    @click="nextStep"
                                    class="px-4 py-2 text-sm font-medium text-white bg-brand-primary rounded-lg hover:bg-brand-primary/90 transition-colors"
                                >
                                    Suivant →
                                </button>

                                <button
                                    v-else
                                    type="button"
                                    @click="generateContract"
                                    :disabled="form.processing"
                                    class="px-6 py-2 text-sm font-medium text-white bg-brand-primary rounded-lg hover:bg-brand-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
                                >
                                    <svg 
                                        v-if="form.processing"
                                        class="animate-spin h-4 w-4 mr-2" 
                                        fill="none" 
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Génération...' : '📄 Générer le contrat' }}
                                </button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Sidebar: Summary & Help -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Summary Card -->
                    <Card title="Récapitulatif">
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Client</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ client.nom }} {{ client.prenom }}
                                </p>
                            </div>

                            <div v-if="form.contract_type">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Type de contrat</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ getContractTypeLabel(form.contract_type) }}
                                </p>
                            </div>

                            <div v-if="form.package_id && selectedPackage">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Forfait</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ selectedPackage.name }}
                                </p>
                                <p class="text-lg font-bold text-brand-primary">
                                    {{ formatCurrency(selectedPackage.price) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Langue</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ form.language === 'fr' ? '🇫🇷 Français' : '🇬🇧 English' }}
                                </p>
                            </div>
                        </div>
                    </Card>

                    <!-- Recent Contracts -->
                    <Card 
                        v-if="recentContracts.length > 0"
                        title="Contrats récents"
                    >
                        <div class="space-y-3">
                            <div
                                v-for="contract in recentContracts.slice(0, 3)"
                                :key="contract.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ contract.type }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(contract.created_at) }}
                                    </p>
                                </div>
                                <StatusBadge :status="contract.status" type="contract" size="sm" />
                            </div>
                        </div>
                    </Card>

                    <!-- Help Card -->
                    <Card>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-brand-accent flex items-center justify-center">
                                    <svg class="h-5 w-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Besoin d'aide ?</h3>
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    Consultez la documentation sur les types de contrats et les forfaits disponibles.
                                </p>
                                <button class="mt-2 text-xs text-brand-primary hover:underline">
                                    Voir la documentation →
                                </button>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import { useUIStore } from '@/stores/ui';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const uiStore = useUIStore();

const props = defineProps({
    dossier: { type: Object, required: true },
    client: { type: Object, required: true },
    availablePackages: { type: Array, default: () => [] },
    recentContracts: { type: Array, default: () => [] },
    contractTypes: { type: Object, default: () => ({}) },
});

const steps = ['Type & Forfait', 'Détails', 'Prévisualisation'];
const currentStep = ref(0);

const form = useForm({
    type: '', // Changed from contract_type to match backend
    language: 'fr',
    template_id: null, // DocuSeal template ID (get from env or config)
    variables: {},
    signers: [], // Array of signers (client, guarantor)
    notes: '',
});

const isLoadingPreview = ref(false);
const previewHtml = ref('');

const selectedPackage = computed(() => {
    return props.availablePackages.find(pkg => pkg.id === form.package_id);
});

function onContractTypeChange() {
    // Load default variables for this contract type
    form.variables = {
        client_nom: props.client.nom,
        client_prenom: props.client.prenom,
        client_email: props.client.email,
        client_telephone: props.client.telephone,
        dossier_reference: props.dossier.reference,
    };
}

function formatVariableName(key) {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'CAD',
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function getContractTypeLabel(type) {
    const labels = {
        etude: 'Études (1er cycle)',
        etude_2e_3e_cycle: 'Études (2e/3e cycle)',
        etude_garant: 'Études (avec garant)',
        permis_travail: 'Permis de travail',
        entree_express: 'Entrée Express',
        lmia: 'LMIA',
        csq_quebec: 'CSQ Québec',
        citoyennete: 'Citoyenneté canadienne',
        visa_visiteur: 'Visa visiteur',
        super_visa: 'Super Visa',
        ave: 'AVE (eTA)',
        parrainage_familial: 'Parrainage familial',
        restauration_prolongation: 'Restauration/Prolongation',
        demande_asile: "Demande d'asile",
        traduction_documents: 'Traduction de documents',
    };
    return labels[type] || type;
}

function nextStep() {
    if (currentStep.value === 0 && !form.type) {
        uiStore.showError('Veuillez sélectionner un type de contrat');
        return;
    }
    
    // Step 1: Initialize signers if not already done
    if (currentStep.value === 0 && form.signers.length === 0) {
        form.signers = [
            {
                type: 'client',
                name: `${props.client.prenom} ${props.client.nom}`,
                email: props.client.email,
                phone: props.client.telephone || '',
            }
        ];
        
        // Add guarantor if available
        if (props.dossier.guarantor) {
            form.signers.push({
                type: 'guarantor',
                name: props.dossier.guarantor.name,
                email: props.dossier.guarantor.email,
                phone: props.dossier.guarantor.phone || '',
            });
        }
    }
    
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
        if (currentStep.value === 2 && !previewHtml.value) {
            loadPreview();
        }
    }
}

function previousStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}

async function loadPreview() {
    isLoadingPreview.value = true;
    try {
        const response = await axios.post(route('contracts.preview'), {
            type: form.type,
            language: form.language,
            variables: form.variables,
            dossier_id: props.dossier.id,
        });
        previewHtml.value = response.data.html || '';
    } catch (error) {
        uiStore.showError('Erreur lors du chargement de l\'aperçu');
        console.error(error);
    } finally {
        isLoadingPreview.value = false;
    }
}

function submitContract() {
    // Set template_id from environment or config
    // TODO: Get this from backend config
    form.template_id = 123456; // Replace with actual template ID
    
    form.post(route('contracts.store', props.dossier.id), {
        onSuccess: () => {
            uiStore.showSuccess('Contrat envoyé pour signature avec succès');
        },
        onError: (errors) => {
            uiStore.showError('Erreur lors de l\'envoi du contrat');
            console.error(errors);
        },
    });
}
</script>

<style scoped>
.input-field {
    @apply w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-brand-primary focus:border-brand-primary bg-white dark:bg-gray-800 text-gray-900 dark:text-white;
}
</style>
