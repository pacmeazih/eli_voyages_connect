<template>
    <VerticalLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Documents — {{ dossier.reference }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ dossier.title }}</p>
                </div>
                <Link :href="route('dossiers.show', dossier.id)" class="text-brand-primary hover:text-brand-primary/90">↩ Retour au dossier</Link>
            </div>

            <!-- Upload Zone with Drag & Drop -->
            <div 
                v-if="userStore.can('documents.create')"
                class="mb-6"
                @drop.prevent="handleDrop"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
            >
                <div 
                    class="border-2 border-dashed rounded-lg p-8 text-center transition-all"
                    :class="{
                        'border-brand-primary bg-brand-primary/5': isDragging,
                        'border-gray-300 dark:border-gray-600 hover:border-brand-primary': !isDragging
                    }"
                >
                    <svg 
                        class="mx-auto h-12 w-12 transition-colors"
                        :class="isDragging ? 'text-brand-primary' : 'text-gray-400'"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        Glissez-déposez vos fichiers ici
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        ou
                    </p>
                    <label class="mt-2 inline-flex items-center px-4 py-2 bg-brand-primary text-white text-sm font-medium rounded-lg hover:bg-brand-primary/90 cursor-pointer transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Parcourir les fichiers
                        <input 
                            type="file" 
                            multiple
                            @change="handleFileSelect"
                            class="hidden"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        />
                    </label>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        PDF, JPG, PNG, DOC jusqu'à 10MB
                    </p>
                </div>

                <!-- Upload Progress -->
                <div v-if="uploadQueue.length > 0" class="mt-4 space-y-3">
                    <div
                        v-for="upload in uploadQueue"
                        :key="upload.id"
                        class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow border border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <svg class="h-8 w-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ upload.file.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatFileSize(upload.file.size) }}
                                    </p>
                                </div>
                            </div>
                            <button
                                v-if="upload.status === 'pending' || upload.status === 'error'"
                                @click="removeFromQueue(upload.id)"
                                class="ml-4 text-gray-400 hover:text-red-600"
                            >
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <!-- Progress Bar -->
                        <div v-if="upload.status === 'uploading'" class="mb-2">
                            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                <span>Téléchargement...</span>
                                <span>{{ upload.progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div 
                                    class="bg-gradient-to-r from-brand-primary to-eli-turquoise-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${upload.progress}%` }"
                                ></div>
                            </div>
                        </div>

                        <!-- Status Messages -->
                        <div v-if="upload.status === 'success'" class="flex items-center text-sm text-green-600 dark:text-green-400">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Téléchargé avec succès
                        </div>

                        <div v-if="upload.status === 'error'" class="flex items-center justify-between">
                            <div class="flex items-center text-sm text-red-600 dark:text-red-400">
                                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                {{ upload.error || 'Erreur lors du téléchargement' }}
                            </div>
                            <button
                                @click="retryUpload(upload)"
                                class="text-sm text-brand-primary hover:underline"
                            >
                                Réessayer
                            </button>
                        </div>

                        <!-- Select document type -->
                        <div v-if="upload.status === 'pending'" class="mt-3">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Type de document
                            </label>
                            <select
                                v-model="upload.type"
                                class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            >
                                <option value="">Sélectionnez un type</option>
                                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Upload All Button -->
                    <div v-if="uploadQueue.some(u => u.status === 'pending')" class="flex justify-end">
                        <button
                            @click="uploadAll"
                            class="px-4 py-2 bg-brand-primary text-white text-sm font-medium rounded-lg hover:bg-brand-primary/90 transition-colors"
                        >
                            Télécharger tout ({{ uploadQueue.filter(u => u.status === 'pending').length }})
                        </button>
                    </div>
                </div>
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
    </VerticalLayout>
</template>


<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useUserStore } from '@/stores/user';
import { useUIStore } from '@/stores/ui';
import VerticalLayout from '@/Layouts/VerticalLayout.vue';
import Card from '@/Components/Card.vue';

const userStore = useUserStore();
const uiStore = useUIStore();

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

// Drag & Drop state
const isDragging = ref(false);
const uploadQueue = ref([]);
let uploadIdCounter = 0;

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

// New drag & drop functions
function formatFileSize(bytes) {
    return formatSize(bytes);
}

function handleDrop(event) {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFilesToQueue(files);
}

function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    addFilesToQueue(files);
    event.target.value = ''; // Reset input
}

function addFilesToQueue(files) {
    const validExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    const maxSize = 10 * 1024 * 1024; // 10MB

    for (const file of files) {
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!validExtensions.includes(extension)) {
            uiStore.showError(`Format non supporté: ${file.name}`);
            continue;
        }

        if (file.size > maxSize) {
            uiStore.showError(`Fichier trop volumineux: ${file.name} (max 10MB)`);
            continue;
        }

        uploadQueue.value.push({
            id: ++uploadIdCounter,
            file,
            type: '',
            status: 'pending', // pending, uploading, success, error
            progress: 0,
            error: null,
        });
    }
}

function removeFromQueue(uploadId) {
    uploadQueue.value = uploadQueue.value.filter(u => u.id !== uploadId);
}

async function uploadFile(upload) {
    if (!upload.type) {
        uiStore.showError('Veuillez sélectionner un type de document');
        return;
    }

    upload.status = 'uploading';
    upload.progress = 0;

    const formData = new FormData();
    formData.append('file', upload.file);
    formData.append('type', upload.type);
    formData.append('dossier_id', props.dossier.id);

    try {
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                upload.progress = Math.round((e.loaded / e.total) * 100);
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                upload.status = 'success';
                upload.progress = 100;
                uiStore.showSuccess(`${upload.file.name} téléchargé avec succès`);
                
                // Refresh documents list after 1 second
                setTimeout(() => {
                    router.reload({ only: ['documents'] });
                    removeFromQueue(upload.id);
                }, 1000);
            } else {
                throw new Error(xhr.statusText || 'Erreur de téléchargement');
            }
        });

        xhr.addEventListener('error', () => {
            throw new Error('Erreur réseau');
        });

        xhr.open('POST', route('documents.store'));
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);

    } catch (error) {
        upload.status = 'error';
        upload.error = error.message;
        uiStore.showError(`Erreur: ${upload.file.name}`);
    }
}

function retryUpload(upload) {
    upload.status = 'pending';
    upload.progress = 0;
    upload.error = null;
    uploadFile(upload);
}

async function uploadAll() {
    const pendingUploads = uploadQueue.value.filter(u => u.status === 'pending');
    
    for (const upload of pendingUploads) {
        await uploadFile(upload);
    }
}
</script>

