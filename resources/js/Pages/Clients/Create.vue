<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $page.props.client ? 'Modifier le client' : 'Nouveau client' }}
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ $page.props.client ? 'Modifiez les informations du client' : 'Créez un nouveau client en complétant les informations ci-dessous' }}
                </p>
            </div>

            <!-- Progress Stepper -->
            <div class="mb-8">
                <nav aria-label="Progress">
                    <ol class="flex items-center justify-between">
                        <li 
                            v-for="(step, index) in steps" 
                            :key="index"
                            class="relative flex-1"
                            :class="{ 'pr-8 sm:pr-20': index !== steps.length - 1 }"
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

                            <!-- Step Button -->
                            <button
                                @click="goToStep(index)"
                                :disabled="index > maxStepReached"
                                class="relative flex flex-col items-center group"
                            >
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-colors relative z-10"
                                    :class="{
                                        'bg-brand-primary text-white': currentStep === index,
                                        'bg-brand-primary/20 text-brand-primary': currentStep > index,
                                        'bg-gray-200 dark:bg-gray-700 text-gray-500': currentStep < index,
                                        'cursor-pointer hover:bg-brand-primary/80': index <= maxStepReached && currentStep !== index,
                                        'cursor-not-allowed': index > maxStepReached
                                    }"
                                >
                                    <svg v-if="currentStep > index" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <span 
                                    class="mt-2 text-xs font-medium transition-colors"
                                    :class="{
                                        'text-brand-primary': currentStep >= index,
                                        'text-gray-500 dark:text-gray-400': currentStep < index
                                    }"
                                >
                                    {{ step }}
                                </span>
                            </button>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Form Card -->
            <Card>
                <form @submit.prevent="submitForm">
                    <!-- Step 1: Informations personnelles -->
                    <div v-show="currentStep === 0" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <FormField
                                label="Prénom"
                                :error="form.errors.first_name"
                                required
                            >
                                <input
                                    v-model="form.first_name"
                                    type="text"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.first_name }"
                                />
                            </FormField>

                            <FormField
                                label="Nom"
                                :error="form.errors.last_name"
                                required
                            >
                                <input
                                    v-model="form.last_name"
                                    type="text"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.last_name }"
                                />
                            </FormField>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <FormField
                                label="Date de naissance"
                                :error="form.errors.birth_date"
                                required
                            >
                                <input
                                    v-model="form.birth_date"
                                    type="date"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.birth_date }"
                                />
                            </FormField>

                            <FormField
                                label="Lieu de naissance"
                                :error="form.errors.birth_place"
                            >
                                <input
                                    v-model="form.birth_place"
                                    type="text"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.birth_place }"
                                />
                            </FormField>
                        </div>

                        <FormField
                            label="Nationalité"
                            :error="form.errors.nationality"
                            required
                        >
                            <select
                                v-model="form.nationality"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.nationality }"
                            >
                                <option value="">Sélectionnez une nationalité</option>
                                <option value="FR">Française</option>
                                <option value="BE">Belge</option>
                                <option value="CH">Suisse</option>
                                <option value="CA">Canadienne</option>
                                <option value="other">Autre</option>
                            </select>
                        </FormField>

                        <FormField
                            label="Genre"
                            :error="form.errors.gender"
                        >
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input
                                        v-model="form.gender"
                                        type="radio"
                                        value="M"
                                        class="form-radio text-brand-primary"
                                    />
                                    <span class="ml-2">Masculin</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input
                                        v-model="form.gender"
                                        type="radio"
                                        value="F"
                                        class="form-radio text-brand-primary"
                                    />
                                    <span class="ml-2">Féminin</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input
                                        v-model="form.gender"
                                        type="radio"
                                        value="other"
                                        class="form-radio text-brand-primary"
                                    />
                                    <span class="ml-2">Autre</span>
                                </label>
                            </div>
                        </FormField>
                    </div>

                    <!-- Step 2: Coordonnées -->
                    <div v-show="currentStep === 1" class="space-y-6">
                        <FormField
                            label="Email"
                            :error="form.errors.email"
                            required
                            help="L'email servira d'identifiant de connexion"
                        >
                            <input
                                v-model="form.email"
                                type="email"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                        </FormField>

                        <FormField
                            label="Téléphone"
                            :error="form.errors.phone"
                            required
                        >
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.phone }"
                            />
                        </FormField>

                        <FormField
                            label="Adresse"
                            :error="form.errors.address"
                            required
                        >
                            <input
                                v-model="form.address"
                                type="text"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.address }"
                            />
                        </FormField>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <FormField
                                label="Code postal"
                                :error="form.errors.postal_code"
                                required
                            >
                                <input
                                    v-model="form.postal_code"
                                    type="text"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.postal_code }"
                                />
                            </FormField>

                            <FormField
                                label="Ville"
                                :error="form.errors.city"
                                required
                            >
                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.city }"
                                />
                            </FormField>

                            <FormField
                                label="Pays"
                                :error="form.errors.country"
                                required
                            >
                                <select
                                    v-model="form.country"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.country }"
                                >
                                    <option value="">Sélectionnez</option>
                                    <option value="FR">France</option>
                                    <option value="BE">Belgique</option>
                                    <option value="CH">Suisse</option>
                                    <option value="CA">Canada</option>
                                </select>
                            </FormField>
                        </div>
                    </div>

                    <!-- Step 3: Documents d'identité -->
                    <div v-show="currentStep === 2" class="space-y-6">
                        <FormField
                            label="Type de document"
                            :error="form.errors.id_type"
                            required
                        >
                            <select
                                v-model="form.id_type"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.id_type }"
                            >
                                <option value="">Sélectionnez un type</option>
                                <option value="passport">Passeport</option>
                                <option value="id_card">Carte d'identité</option>
                                <option value="residence_permit">Titre de séjour</option>
                            </select>
                        </FormField>

                        <FormField
                            label="Numéro du document"
                            :error="form.errors.id_number"
                            required
                        >
                            <input
                                v-model="form.id_number"
                                type="text"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.id_number }"
                            />
                        </FormField>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <FormField
                                label="Date de délivrance"
                                :error="form.errors.id_issue_date"
                            >
                                <input
                                    v-model="form.id_issue_date"
                                    type="date"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.id_issue_date }"
                                />
                            </FormField>

                            <FormField
                                label="Date d'expiration"
                                :error="form.errors.id_expiry_date"
                            >
                                <input
                                    v-model="form.id_expiry_date"
                                    type="date"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.id_expiry_date }"
                                />
                            </FormField>
                        </div>
                    </div>

                    <!-- Step 4: Affectation -->
                    <div v-show="currentStep === 3" class="space-y-6">
                        <FormField
                            label="Consultant assigné"
                            :error="form.errors.consultant_id"
                            help="Le consultant qui suivra ce client"
                            :required="userStore.isSuperAdmin"
                        >
                            <select
                                v-model="form.consultant_id"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.consultant_id }"
                                :disabled="!userStore.isSuperAdmin && !userStore.isConsultant"
                            >
                                <option value="">Sélectionnez un consultant</option>
                                <option 
                                    v-for="consultant in consultants" 
                                    :key="consultant.id"
                                    :value="consultant.id"
                                >
                                    {{ consultant.name }}
                                </option>
                            </select>
                        </FormField>

                        <FormField
                            label="Agent assigné"
                            :error="form.errors.agent_id"
                            help="L'agent qui traitera les dossiers de ce client"
                        >
                            <select
                                v-model="form.agent_id"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.agent_id }"
                            >
                                <option value="">Sélectionnez un agent</option>
                                <option 
                                    v-for="agent in agents" 
                                    :key="agent.id"
                                    :value="agent.id"
                                >
                                    {{ agent.name }}
                                </option>
                            </select>
                        </FormField>

                        <FormField
                            label="Source"
                            :error="form.errors.source"
                            help="Comment le client a-t-il connu nos services ?"
                        >
                            <select
                                v-model="form.source"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.source }"
                            >
                                <option value="">Sélectionnez une source</option>
                                <option value="website">Site web</option>
                                <option value="referral">Recommandation</option>
                                <option value="partner">Partenaire</option>
                                <option value="social_media">Réseaux sociaux</option>
                                <option value="other">Autre</option>
                            </select>
                        </FormField>

                        <FormField
                            label="Notes"
                            :error="form.errors.notes"
                            help="Informations supplémentaires sur le client"
                        >
                            <textarea
                                v-model="form.notes"
                                rows="4"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.notes }"
                            ></textarea>
                        </FormField>
                    </div>

                    <!-- Form Actions -->
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
                                :href="route('clients.index')"
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
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-brand-primary rounded-lg hover:bg-brand-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                            </button>
                        </div>
                    </div>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useUserStore } from '@/stores/user';
import { useUiStore } from '@/stores/ui';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';

const userStore = useUserStore();
const uiStore = useUiStore();

const props = defineProps({
    client: {
        type: Object,
        default: null,
    },
    consultants: {
        type: Array,
        default: () => [],
    },
    agents: {
        type: Array,
        default: () => [],
    },
});

const steps = ['Identité', 'Coordonnées', 'Documents', 'Affectation'];
const currentStep = ref(0);
const maxStepReached = ref(0);

const form = useForm({
    first_name: props.client?.first_name || '',
    last_name: props.client?.last_name || '',
    birth_date: props.client?.birth_date || '',
    birth_place: props.client?.birth_place || '',
    nationality: props.client?.nationality || '',
    gender: props.client?.gender || '',
    email: props.client?.email || '',
    phone: props.client?.phone || '',
    address: props.client?.address || '',
    postal_code: props.client?.postal_code || '',
    city: props.client?.city || '',
    country: props.client?.country || '',
    id_type: props.client?.id_type || '',
    id_number: props.client?.id_number || '',
    id_issue_date: props.client?.id_issue_date || '',
    id_expiry_date: props.client?.id_expiry_date || '',
    consultant_id: props.client?.consultant_id || '',
    agent_id: props.client?.agent_id || '',
    source: props.client?.source || '',
    notes: props.client?.notes || '',
});

function validateCurrentStep() {
    const validations = {
        0: ['first_name', 'last_name', 'birth_date', 'nationality'],
        1: ['email', 'phone', 'address', 'postal_code', 'city', 'country'],
        2: ['id_type', 'id_number'],
        3: [], // No required fields on last step
    };

    const requiredFields = validations[currentStep.value] || [];
    
    for (const field of requiredFields) {
        if (!form[field] || form[field].trim() === '') {
            uiStore.showError(`Le champ "${field}" est requis`);
            return false;
        }
    }
    
    return true;
}

function nextStep() {
    if (validateCurrentStep()) {
        currentStep.value++;
        if (currentStep.value > maxStepReached.value) {
            maxStepReached.value = currentStep.value;
        }
    }
}

function previousStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}

function goToStep(stepIndex) {
    if (stepIndex <= maxStepReached.value) {
        currentStep.value = stepIndex;
    }
}

function submitForm() {
    if (props.client) {
        form.put(route('clients.update', props.client.id), {
            onSuccess: () => {
                uiStore.showSuccess('Client mis à jour avec succès');
            },
            onError: () => {
                uiStore.showError('Erreur lors de la mise à jour du client');
            },
        });
    } else {
        form.post(route('clients.store'), {
            onSuccess: () => {
                uiStore.showSuccess('Client créé avec succès');
            },
            onError: () => {
                uiStore.showError('Erreur lors de la création du client');
            },
        });
    }
}
</script>

<style scoped>
.input-field {
    @apply w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-brand-primary focus:border-brand-primary bg-white dark:bg-gray-800 text-gray-900 dark:text-white;
}
</style>
