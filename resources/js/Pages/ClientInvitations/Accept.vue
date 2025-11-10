<template>
    <div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            <!-- Logo -->
            <div class="text-center mb-8">
                <img
                    src="/assets/img/branding/Eli-Voyages LOGO.png"
                    alt="ELI VOYAGES"
                    class="h-20 mx-auto mb-4"
                />
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Bienvenue chez ELI VOYAGES
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Finalisez la création de votre compte
                </p>
            </div>

            <!-- Invitation Card -->
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-lg overflow-hidden">
                <!-- Header with Client Code -->
                <div class="bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-4">
                    <div class="flex items-center justify-between text-white">
                        <div>
                            <p class="text-sm opacity-90">Votre code client</p>
                            <p class="text-2xl font-bold">{{ invitation.client_code }}</p>
                        </div>
                        <svg class="h-12 w-12 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <!-- Personal Info Display -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Vos informations</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Nom complet:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ invitation.prenom }} {{ invitation.nom }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Email:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ invitation.email }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Téléphone:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ invitation.telephone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Civilité -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Civilité <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-3">
                            <label
                                v-for="option in ['M.', 'Mme', 'Mlle']"
                                :key="option"
                                :class="[
                                    'flex items-center justify-center px-4 py-3 border-2 rounded-lg cursor-pointer transition-all',
                                    form.civilite === option
                                        ? 'border-amber-600 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400'
                                        : 'border-gray-300 dark:border-gray-600 hover:border-amber-400 dark:hover:border-amber-500'
                                ]"
                            >
                                <input
                                    v-model="form.civilite"
                                    type="radio"
                                    :value="option"
                                    class="sr-only"
                                />
                                <span class="font-medium">{{ option }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.civilite" class="mt-1 text-sm text-red-600">{{ form.errors.civilite }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Minimum 8 caractères"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                            placeholder="Confirmez votre mot de passe"
                        />
                    </div>

                    <!-- Optional Fields -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">
                            Informations complémentaires (optionnel)
                        </h3>

                        <div class="space-y-4">
                            <!-- Adresse -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Adresse
                                </label>
                                <textarea
                                    v-model="form.adresse"
                                    rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    placeholder="Votre adresse complète"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Date de naissance -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Date de naissance
                                    </label>
                                    <input
                                        v-model="form.date_naissance"
                                        type="date"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <!-- Lieu de naissance -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Lieu de naissance
                                    </label>
                                    <input
                                        v-model="form.lieu_naissance"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Ville, Pays"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nationalité -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nationalité
                                    </label>
                                    <input
                                        v-model="form.nationalite"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Ex: Ivoirienne"
                                    />
                                </div>

                                <!-- Profession -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Profession
                                    </label>
                                    <input
                                        v-model="form.profession"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Votre profession"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input
                            id="terms"
                            v-model="acceptedTerms"
                            type="checkbox"
                            required
                            class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded"
                        />
                        <label for="terms" class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                            J'accepte les <a href="#" class="text-amber-600 hover:text-amber-700 font-medium">conditions d'utilisation</a>
                            et la <a href="#" class="text-amber-600 hover:text-amber-700 font-medium">politique de confidentialité</a>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing || !acceptedTerms"
                        class="w-full py-3 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg font-medium text-lg shadow-lg transition-all flex items-center justify-center"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Création en cours...' : 'Créer mon compte' }}
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-8">
                Vous avez des questions? Contactez-nous à
                <a href="mailto:info@eli-voyages.com" class="text-amber-600 hover:text-amber-700 font-medium">
                    info@eli-voyages.com
                </a>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: Object,
    token: String,
});

const acceptedTerms = ref(false);

const form = useForm({
    password: '',
    password_confirmation: '',
    civilite: 'M.',
    adresse: '',
    date_naissance: '',
    lieu_naissance: '',
    nationalite: '',
    profession: '',
});

const submit = () => {
    form.post(route('client-invitations.accept', props.token));
};
</script>
