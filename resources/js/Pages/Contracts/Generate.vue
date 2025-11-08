<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <Link
                        :href="route('dossiers.show', dossier.id)"
                        class="mr-4 text-gray-500 hover:text-gray-700"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Générer un Contrat</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Dossier: {{ dossier.reference }} - {{ client.nom }} {{ client.prenom }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contract Generation Form -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <!-- Step 1: Contract Type & Language -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            1. Type de contrat et langue
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contract Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Type de contrat
                                </label>
                                <select
                                    v-model="form.contract_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="loadPreview"
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
                                        <option value="restauration_prolongation">Restauration/Prolongation statut</option>
                                        <option value="demande_asile">Demande d'asile</option>
                                        <option value="traduction_documents">Traduction de documents</option>
                                    </optgroup>
                                </select>
                                <p v-if="form.errors.contract_type" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.contract_type }}
                                </p>
                            </div>

                            <!-- Language -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Langue du contrat
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.language"
                                            type="radio"
                                            value="fr"
                                            class="text-blue-600 focus:ring-blue-500"
                                            @change="loadPreview"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">🇫🇷 Français</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.language"
                                            type="radio"
                                            value="en"
                                            class="text-blue-600 focus:ring-blue-500"
                                            @change="loadPreview"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">🇬🇧 English</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Variables (Auto-filled + Editable) -->
                    <div v-if="form.contract_type" class="mb-8 border-t pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            2. Informations du contrat
                        </h2>
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                            <p class="text-sm text-blue-800">
                                ℹ️ Les informations sont pré-remplies automatiquement depuis le dossier. 
                                Vous pouvez les modifier si nécessaire.
                            </p>
                        </div>

                        <!-- Variables Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Client Variables -->
                            <div v-for="(value, key) in form.variables" :key="key">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ formatVariableName(key) }}
                                </label>
                                <input
                                    v-model="form.variables[key]"
                                    type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Preview (Optional) -->
                    <div v-if="previewText" class="mb-8 border-t pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                3. Prévisualisation
                            </h2>
                            <button
                                @click="showPreview = !showPreview"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                {{ showPreview ? 'Masquer' : 'Afficher' }}
                            </button>
                        </div>
                        <div
                            v-if="showPreview"
                            class="bg-gray-50 border border-gray-200 rounded-lg p-6 max-h-96 overflow-y-auto"
                        >
                            <div class="prose prose-sm max-w-none" v-html="formatPreview(previewText)"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t pt-6">
                        <Link
                            :href="route('dossiers.show', dossier.id)"
                            class="text-sm text-gray-600 hover:text-gray-900"
                        >
                            ← Retour au dossier
                        </Link>
                        <div class="flex space-x-3">
                            <button
                                v-if="form.contract_type"
                                @click="loadPreview"
                                type="button"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition"
                                :disabled="form.processing"
                            >
                                🔄 Actualiser la prévisualisation
                            </button>
                            <button
                                @click="generateContract"
                                type="button"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50"
                                :disabled="!form.contract_type || form.processing"
                            >
                                <span v-if="form.processing">
                                    <svg class="inline-block animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Génération...
                                </span>
                                <span v-else>
                                    📄 Générer le contrat
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generated Contracts List -->
            <div v-if="generatedContracts.length > 0" class="mt-8 bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Contrats générés récemment
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="contract in generatedContracts"
                            :key="contract.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ contract.type }}</p>
                                    <p class="text-xs text-gray-500">Généré le {{ formatDate(contract.created_at) }}</p>
                                </div>
                            </div>
                            <a
                                :href="route('dossiers.contracts.download', [dossier.id, contract.id])"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition"
                            >
                                Télécharger
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    dossier: Object,
    client: Object,
    package: Object,
    generatedContracts: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    contract_type: '',
    language: 'fr',
    variables: {}
});

const previewText = ref('');
const showPreview = ref(false);

// Load contract variables from dossier data
const loadVariables = () => {
    form.variables = {
        client_nom_complet: `${props.client.nom} ${props.client.prenom}`,
        client_nom: props.client.nom || '',
        client_prenom: props.client.prenom || '',
        client_adresse: props.client.adresse || '',
        client_telephone: props.client.telephone || '',
        client_email: props.client.email || '',
        numero_dossier: props.dossier.reference || '',
        date_signature: new Date().toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' }),
        montant_total: props.package?.price ? `${props.package.price} F CFA` : '',
        type_service: props.dossier.title || '',
    };
};

// Load preview from API
const loadPreview = async () => {
    if (!form.contract_type) return;

    try {
        const response = await axios.post(route('contracts.preview'), {
            contract_type: form.contract_type,
            language: form.language,
            dossier_id: props.dossier.id
        });
        previewText.value = response.data.preview;
        form.variables = { ...form.variables, ...response.data.variables };
        showPreview.value = true;
    } catch (error) {
        console.error('Preview error:', error);
    }
};

// Generate contract
const generateContract = () => {
    form.post(route('dossiers.contracts.generate', props.dossier.id), {
        onSuccess: () => {
            alert('✅ Contrat généré avec succès !');
        },
        onError: (errors) => {
            console.error('Generation errors:', errors);
            alert('❌ Erreur lors de la génération du contrat');
        }
    });
};

// Format variable name for display
const formatVariableName = (key) => {
    const labels = {
        client_nom_complet: 'Nom complet du client',
        client_nom: 'Nom',
        client_prenom: 'Prénom',
        client_adresse: 'Adresse',
        client_telephone: 'Téléphone',
        client_email: 'Email',
        numero_dossier: 'Numéro de dossier',
        date_signature: 'Date de signature',
        montant_total: 'Montant total',
        type_service: 'Type de service',
    };
    return labels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Format preview text to HTML
const formatPreview = (text) => {
    return text
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/ARTICLE \d+/g, '<strong>$&</strong>')
        .replace(/^(.+):$/gm, '<strong>$1:</strong>');
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    loadVariables();
});
</script>
