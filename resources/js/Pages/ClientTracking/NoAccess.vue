<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
                <svg
                    class="mx-auto h-16 w-16 text-yellow-400 mb-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Aucun Dossier Trouvé</h2>
                <p class="text-gray-600 mb-6">
                    {{ message }}
                </p>
                <p class="text-sm text-gray-500 mb-6">
                    Si vous pensez qu'il s'agit d'une erreur, veuillez contacter notre équipe de support.
                </p>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Retour au tableau de bord
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    message: {
        type: String,
        default: 'Aucun dossier client trouvé pour cet utilisateur.',
    },
});
</script>
