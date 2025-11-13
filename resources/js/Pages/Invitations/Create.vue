<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <Link
                    :href="route('invitations.index')"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour aux invitations
                </Link>
            </div>

            <Card>
                <template #header>Nouvelle Invitation</template>

                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Adresse email *
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"
                            :class="{ 'border-red-300': errors.email }"
                        />
                        <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ errors.email }}
                        </p>
                    </div>

                    <!-- Role -->
                    <div class="mt-6">
                        <label for="role" class="block text-sm font-medium text-gray-700">
                            Rôle *
                        </label>
                        <select
                            id="role"
                            v-model="form.role"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"
                            :class="{ 'border-red-300': errors.role }"
                        >
                            <option value="">Sélectionner un rôle</option>
                            <option value="Consultant">Consultant</option>
                            <option value="Agent">Agent</option>
                            <option value="Client">Client</option>
                            <option value="Guarantor">Garant</option>
                        </select>
                        <p v-if="errors.role" class="mt-1 text-sm text-red-600">
                            {{ errors.role }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            Le rôle détermine les permissions de l'utilisateur dans le système.
                        </p>
                    </div>

                    <!-- Dossier (optional) -->
                    <div class="mt-6">
                        <label for="dossier_id" class="block text-sm font-medium text-gray-700">
                            Dossier associé (optionnel)
                        </label>
                        <select
                            id="dossier_id"
                            v-model="form.dossier_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"
                            :class="{ 'border-red-300': errors.dossier_id }"
                        >
                            <option value="">Aucun dossier</option>
                            <option
                                v-for="dossier in dossiers"
                                :key="dossier.id"
                                :value="dossier.id"
                            >
                                {{ dossier.reference }} - {{ dossier.title }}
                            </option>
                        </select>
                        <p v-if="errors.dossier_id" class="mt-1 text-sm text-red-600">
                            {{ errors.dossier_id }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            Associer l'utilisateur à un dossier spécifique (recommandé pour les clients et garants).
                        </p>
                    </div>

                    <!-- Custom Message -->
                    <div class="mt-6">
                        <label for="message" class="block text-sm font-medium text-gray-700">
                            Message personnalisé (optionnel)
                        </label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary sm:text-sm"
                            placeholder="Ajoutez un message personnel à l'invitation..."
                        />
                        <p class="mt-1 text-sm text-gray-500">
                            Ce message sera inclus dans l'email d'invitation.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <PrimaryButton
                            type="button"
                            @click="router.visit(route('invitations.index'))"
                            variant="secondary"
                            :disabled="processing"
                        >
                            Annuler
                        </PrimaryButton>
                        <PrimaryButton
                            type="submit"
                            variant="primary"
                            :disabled="processing"
                        >
                            <svg
                                v-if="processing"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            {{ processing ? 'Envoi en cours...' : 'Envoyer l\'invitation' }}
                        </PrimaryButton>
                    </div>
                </form>
            </Card>

            <!-- Info Box -->
            <div class="mt-6 rounded-md bg-blue-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-blue-700">
                            L'utilisateur recevra un email avec un lien d'inscription valable 7 jours. 
                            Il pourra créer son compte en utilisant ce lien.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    dossiers: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const processing = ref(false);

const form = reactive({
    email: '',
    role: '',
    dossier_id: '',
    message: '',
});

const submit = () => {
    processing.value = true;

    router.post(route('invitations.store'), form, {
        onFinish: () => {
            processing.value = false;
        },
        onSuccess: () => {
            form.email = '';
            form.role = '';
            form.dossier_id = '';
            form.message = '';
        },
    });
};
</script>
