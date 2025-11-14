<template>
    <VerticalLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4">
            <div class="max-w-2xl mx-auto">
                <!-- Success Icon -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Rendez-vous confirmé !
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Votre rendez-vous a été enregistré avec succès
                    </p>
                </div>

                <!-- Appointment Details Card -->
                <Card class="mb-6">
                    <div class="p-6 space-y-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            Détails du rendez-vous
                        </h2>

                        <!-- Date & Time -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date et heure</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                    {{ formatDate(appointment.date) }}
                                </p>
                                <p class="text-gray-600 dark:text-gray-400">
                                    {{ appointment.start_time }} - {{ appointment.end_time }}
                                </p>
                            </div>
                        </div>

                        <!-- Agent -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Conseiller</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                    {{ appointment.agent?.name || 'Non assigné' }}
                                </p>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Type de rendez-vous</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                    {{ getAppointmentType(appointment.type) }}
                                </p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div v-if="appointment.notes" class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</h3>
                                <p class="text-gray-900 dark:text-white mt-1">
                                    {{ appointment.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Next Steps Card -->
                <Card class="mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Prochaines étapes
                        </h2>
                        <ul class="space-y-3">
                            <li class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">
                                    Un email de confirmation vous a été envoyé
                                </span>
                            </li>
                            <li class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">
                                    Vous recevrez un rappel 24h avant le rendez-vous
                                </span>
                            </li>
                            <li class="flex items-start space-x-3">
                                <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">
                                    Préparez les documents nécessaires pour votre rendez-vous
                                </span>
                            </li>
                        </ul>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <Link
                        :href="route('dashboard')"
                        class="flex-1 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors text-center"
                    >
                        Retour au tableau de bord
                    </Link>
                    <Link
                        :href="route('appointments.index')"
                        class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors text-center"
                    >
                        Voir mes rendez-vous
                    </Link>
                </div>
            </div>
        </div>
    </VerticalLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    appointment: {
        type: Object,
        required: true
    }
});

const formatDate = (date) => {
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(date).toLocaleDateString('fr-FR', options);
};

const getAppointmentType = (type) => {
    const types = {
        'consultation': 'Consultation',
        'document_submission': 'Remise de documents',
        'interview': 'Entretien',
        'follow_up': 'Suivi',
        'other': 'Autre'
    };
    return types[type] || type;
};
</script>
