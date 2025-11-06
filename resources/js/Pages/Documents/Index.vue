<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Documents — {{ dossier.reference }}</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ dossier.title }}</p>
                </div>
                <Link :href="route('dossiers.show', dossier.id)" class="text-indigo-600 hover:text-indigo-800">↩ Retour au dossier</Link>
            </div>

            <!-- Filters -->
            <Card class="mb-4">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                        <input v-model="form.search" type="text" placeholder="Nom du document" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select v-model="form.type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tous</option>
                            <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ajouté par</label>
                        <select v-model="form.uploader" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tous</option>
                            <option v-for="u in uploaders" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Filtrer</button>
                        <button type="button" @click="clearFilters" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Réinitialiser</button>
                    </div>
                </form>
            </Card>

            <Card>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th @click="sortBy('name')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                    Nom {{ sortIndicator('name') }}
                                </th>
                                <th @click="sortBy('type')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                    Type {{ sortIndicator('type') }}
                                </th>
                                <th @click="sortBy('size')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                    Taille {{ sortIndicator('size') }}
                                </th>
                                <th @click="sortBy('uploaded_by')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                    Ajouté par {{ sortIndicator('uploaded_by') }}
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <Link :href="route('documents.show', doc.id)" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ doc.name }}</Link>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ doc.type }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ doc.formatted_size ?? formatSize(doc.size) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ doc.uploader?.name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a :href="route('documents.download', doc.id)" class="text-gray-600 hover:text-gray-900">Télécharger</a>
                                    <button @click="destroy(doc.id)" class="text-red-600 hover:text-red-800">Supprimer</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="documents.data.length === 0" class="text-center py-8 text-gray-500">Aucun document</div>

                <!-- Pagination -->
                <div v-if="documents.links.length > 3" class="mt-4 flex justify-center gap-1">
                    <Link v-for="link in documents.links" :key="link.label" :href="link.url" :class="['px-3 py-1 border rounded', link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']" v-html="link.label" />
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    dossier: { type: Object, required: true },
    documents: { type: Object, default: () => ({ data: [], links: [] }) },
    types: { type: Array, default: () => [] },
    uploaders: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const form = reactive({
    search: props.filters.search || '',
    type: props.filters.type || '',
    uploader: props.filters.uploader || '',
    sort: props.filters.sort || 'created_at',
    direction: props.filters.direction || 'desc',
});

const applyFilters = () => {
    router.get(route('dossiers.documents.index', props.dossier.id), form, { preserveState: true });
};

const clearFilters = () => {
    form.search = '';
    form.type = '';
    form.uploader = '';
    form.sort = 'created_at';
    form.direction = 'desc';
    applyFilters();
};

const sortBy = (col) => {
    if (form.sort === col) {
        form.direction = form.direction === 'asc' ? 'desc' : 'asc';
    } else {
        form.sort = col;
        form.direction = 'asc';
    }
    applyFilters();
};

const sortIndicator = (col) => {
    if (form.sort !== col) return '';
    return form.direction === 'asc' ? '▲' : '▼';
};

const destroy = (id) => {
    if (confirm('Supprimer ce document ?')) {
        router.delete(route('documents.destroy', id));
    }
};

const formatSize = (bytes) => {
    if (!bytes && bytes !== 0) return '—';
    const units = ['B', 'KB', 'MB', 'GB'];
    let i = 0;
    let b = bytes;
    while (b > 1024 && i < units.length - 1) { b /= 1024; i++; }
    return `${b.toFixed(2)} ${units[i]}`;
};
</script>
