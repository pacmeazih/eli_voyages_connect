<template>
    <VerticalLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Mon profil</h1>
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-brand-primary focus:ring-brand-primary"
                    />
                </div>
                <div class="flex space-x-4">
                    <button 
                        :disabled="form.processing" 
                        class="px-4 py-2 bg-brand-primary text-white rounded-md hover:bg-brand-primary/90 disabled:opacity-50 transition"
                    >
                        Sauvegarder
                    </button>
                    <button 
                        type="button" 
                        @click="deleteAccount" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                    >
                        Supprimer le compte
                    </button>
                </div>
            </form>
        </div>
    </VerticalLayout>
</template>

<script setup>
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.user || usePage().props.auth?.user;

const form = useForm({
    name: user?.name || '',
    email: user?.email || '',
});

function submit() {
    form.put(route('profile.update'));
}

function deleteAccount() {
    if (confirm('Confirmer la suppression du compte ?')) {
        router.delete(route('profile.destroy'));
    }
}
</script>
