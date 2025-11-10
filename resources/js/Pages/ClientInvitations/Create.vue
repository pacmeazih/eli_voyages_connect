<template>
    <VerticalLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('client-invitations.index')" class="text-amber-600 hover:text-amber-700 flex items-center mb-4">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour aux invitations
                </Link>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Inviter un client</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Un code client sera généré automatiquement</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informations personnelles
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nom <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.nom"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    placeholder="KOUASSI"
                                />
                                <p v-if="form.errors.nom" class="mt-1 text-sm text-red-600">{{ form.errors.nom }}</p>
                            </div>

                            <!-- Prénom -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Prénom <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.prenom"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    placeholder="Jean-Baptiste"
                                />
                                <p v-if="form.errors.prenom" class="mt-1 text-sm text-red-600">{{ form.errors.prenom }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Coordonnées
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    placeholder="client@example.com"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Téléphone <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.telephone"
                                    type="tel"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    placeholder="+225 07 08 09 10 11"
                                />
                                <p v-if="form.errors.telephone" class="mt-1 text-sm text-red-600">{{ form.errors.telephone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-amber-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-amber-800 dark:text-amber-200">
                                <p class="font-semibold mb-1">À propos de l'invitation :</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Un code client unique (format: EV-YYYY-XXXX) sera généré automatiquement</li>
                                    <li>Le client recevra un email d'invitation avec un lien pour créer son compte</li>
                                    <li>L'invitation expire après 30 jours</li>
                                    <li>Le client pourra suivre son dossier dès qu'il accepte l'invitation</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <Link
                            :href="route('client-invitations.index')"
                            class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-400 text-white rounded-lg font-medium transition-colors shadow-sm flex items-center"
                        >
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Envoi en cours...' : 'Envoyer l\'invitation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';

const form = useForm({
    nom: '',
    prenom: '',
    email: '',
    telephone: '',
});

const submit = () => {
    form.post(route('client-invitations.store'), {
        preserveScroll: true,
    });
};
</script>
