<template>
    <form @submit.prevent="submit">
        <div class="space-y-6">
            <!-- Client -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Client</label>
                <select v-model="form.client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option :value="null">Sélectionner un client…</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }} ({{ c.email }})</option>
                </select>
                <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Titre</label>
                <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ex. Dossier visa / voyage" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea v-model="form.notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <Link :href="route('dossiers.index')" class="text-gray-600 hover:text-gray-800">Annuler</Link>
            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    clients: { type: Array, required: true },
    initialValues: { type: Object, default: () => ({ client_id: null, title: '', notes: '' }) },
    submitRoute: { type: String, required: true },
    method: { type: String, default: 'post' },
    submitLabel: { type: String, default: 'Enregistrer' },
});

const form = useForm({
    client_id: props.initialValues.client_id ?? null,
    title: props.initialValues.title ?? '',
    notes: props.initialValues.notes ?? '',
});

const submit = () => {
    if (props.method.toLowerCase() === 'post') {
        form.post(props.submitRoute);
    } else if (props.method.toLowerCase() === 'put' || props.method.toLowerCase() === 'patch') {
        form.put(props.submitRoute);
    } else {
        form.post(props.submitRoute, { _method: props.method.toUpperCase() });
    }
};
</script>
