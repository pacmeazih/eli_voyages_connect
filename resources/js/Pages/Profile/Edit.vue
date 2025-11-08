<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Mon profil</h1>
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                    <input v-model="form.name" type="text" class="mt-1 w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.email" type="email" class="mt-1 w-full rounded border-gray-300" />
                </div>
                <div class="flex space-x-4">
                    <button :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Sauvegarder</button>
                    <button type="button" @click="deleteAccount" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Supprimer le compte</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
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
