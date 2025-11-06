<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ document.name }}</h1>
                    <p class="text-sm text-gray-600 mt-1">{{ document.type }} • {{ formatSize(document.size) }}</p>
                </div>
                <div class="space-x-3">
                    <a :href="route('documents.download', document.id)" class="inline-flex items-center px-3 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">Télécharger</a>
                    <Link :href="route('dossiers.documents.index', document.dossier_id)" class="text-indigo-600 hover:text-indigo-800">↩ Retour aux documents</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Preview -->
                <Card class="md:col-span-2">
                    <div v-if="document.mime_type === 'application/pdf'" class="aspect-[3/4] w-full">
                        <iframe :src="temporaryUrl" class="w-full h-[70vh] rounded" />
                    </div>
                    <div v-else-if="document.mime_type?.startsWith('image/')" class="flex items-center justify-center bg-gray-50 p-4 rounded">
                        <img :src="temporaryUrl" :alt="document.name" class="max-h-[70vh] rounded shadow" />
                    </div>
                    <div v-else class="text-center py-10 text-gray-500">
                        Aperçu non disponible pour ce type de fichier.
                    </div>
                </Card>

                <!-- Details / Actions -->
                <div class="space-y-6">
                    <Card title="Détails">
                        <dl class="divide-y divide-gray-200">
                            <div class="py-3 flex justify-between text-sm">
                                <dt class="text-gray-600">Dossier</dt>
                                <dd class="text-gray-900">{{ document.dossier?.reference }}</dd>
                            </div>
                            <div class="py-3 flex justify-between text-sm">
                                <dt class="text-gray-600">Type</dt>
                                <dd class="text-gray-900">{{ document.type }}</dd>
                            </div>
                            <div class="py-3 flex justify-between text-sm">
                                <dt class="text-gray-600">Taille</dt>
                                <dd class="text-gray-900">{{ formatSize(document.size) }}</dd>
                            </div>
                            <div class="py-3 flex justify-between text-sm">
                                <dt class="text-gray-600">Ajouté par</dt>
                                <dd class="text-gray-900">{{ document.uploader?.name ?? '—' }}</dd>
                            </div>
                        </dl>
                    </Card>

                    <Card title="Mettre à jour">
                        <form @submit.prevent="update">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <input v-model="form.type" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                </div>
                                <div class="flex items-center justify-end">
                                    <button type="submit" :disabled="processing" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </Card>

                    <Card title="Nouvelle version">
                        <form @submit.prevent="uploadVersion">
                            <input type="file" ref="fileInput" class="hidden" @change="handleFile" />
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600" v-if="selectedName">Fichier sélectionné: {{ selectedName }}</div>
                                <div class="text-sm text-gray-500" v-else>Sélectionner un fichier pour créer une nouvelle version</div>
                                <div class="space-x-2">
                                    <button type="button" @click="pickFile" class="px-3 py-2 bg-white border rounded-md hover:bg-gray-50">Choisir</button>
                                    <button type="submit" :disabled="!file || processing" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Uploader</button>
                                </div>
                            </div>
                        </form>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    document: { type: Object, required: true },
    temporaryUrl: { type: String, required: true },
});

const form = useForm({
    name: props.document.name,
    type: props.document.type,
    description: props.document.description ?? '',
});

const processing = computed(() => form.processing);

const update = () => {
    form.put(route('documents.update', props.document.id));
};

const fileInput = ref(null);
const file = ref(null);
const selectedName = computed(() => file.value?.name ?? '');

const pickFile = () => fileInput.value?.click();
const handleFile = (e) => {
    file.value = e.target.files?.[0] ?? null;
};

const uploadVersion = () => {
    if (!file.value) return;
    const data = new FormData();
    data.append('file', file.value);
    router.post(route('documents.version', props.document.id), data);
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
