<template>
    <div>
        <!-- Upload Area -->
        <div
            class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10"
            :class="{
                'bg-indigo-50 border-indigo-300': isDragging,
                'bg-white': !isDragging
            }"
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
        >
            <div class="text-center">
                <svg
                    class="mx-auto h-12 w-12 text-gray-300"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div class="mt-4 flex text-sm leading-6 text-gray-600">
                    <label
                        for="file-upload"
                        class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500"
                    >
                        <span>Télécharger un fichier</span>
                        <input
                            id="file-upload"
                            name="file-upload"
                            type="file"
                            class="sr-only"
                            :accept="acceptedTypes"
                            :multiple="multiple"
                            @change="handleFileSelect"
                        />
                    </label>
                    <p class="pl-1">ou glisser-déposer</p>
                </div>
                <p class="text-xs leading-5 text-gray-600">
                    {{ acceptedTypesLabel }} jusqu'à {{ maxSizeMB }}MB
                </p>
            </div>
        </div>

        <!-- File List -->
        <div v-if="files.length > 0" class="mt-4 space-y-3">
            <div
                v-for="(file, index) in files"
                :key="index"
                class="flex items-center justify-between rounded-lg border border-gray-200 p-3"
            >
                <div class="flex items-center min-w-0 flex-1">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-8 w-8"
                            :class="file.error ? 'text-red-500' : 'text-gray-400'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ file.name }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ formatFileSize(file.size) }}
                        </p>
                        <p v-if="file.error" class="text-xs text-red-600 mt-1">
                            {{ file.error }}
                        </p>
                    </div>
                </div>
                <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                    <!-- Progress -->
                    <div v-if="file.uploading && !file.error" class="flex items-center">
                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-2">
                            <div
                                class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                :style="{ width: file.progress + '%' }"
                            />
                        </div>
                        <span class="text-xs text-gray-600">{{ file.progress }}%</span>
                    </div>
                    
                    <!-- Success -->
                    <svg
                        v-if="file.uploaded && !file.error"
                        class="h-5 w-5 text-green-500"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    
                    <!-- Remove -->
                    <button
                        v-if="!file.uploading"
                        @click="removeFile(index)"
                        class="text-gray-400 hover:text-red-600"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Document Type Selection -->
        <div v-if="files.length > 0 && !hideTypeSelector" class="mt-4">
            <label for="document-type" class="block text-sm font-medium text-gray-700">
                Type de document
            </label>
            <select
                id="document-type"
                v-model="documentType"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
                <option value="">Sélectionner un type</option>
                <option value="passport">Passeport</option>
                <option value="id_card">Carte d'identité</option>
                <option value="birth_certificate">Acte de naissance</option>
                <option value="contract">Contrat</option>
                <option value="payment_proof">Preuve de paiement</option>
                <option value="other">Autre</option>
            </select>
        </div>

        <!-- Actions -->
        <div v-if="files.length > 0 && !autoUpload" class="mt-6 flex justify-end space-x-3">
            <PrimaryButton
                @click="clearFiles"
                variant="secondary"
                :disabled="uploading"
            >
                Annuler
            </PrimaryButton>
            <PrimaryButton
                @click="uploadFiles"
                variant="primary"
                :disabled="uploading || hasErrors"
            >
                <svg
                    v-if="uploading"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    />
                </svg>
                {{ uploading ? 'Téléchargement...' : 'Télécharger' }}
            </PrimaryButton>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    dossierId: {
        type: Number,
        required: true,
    },
    acceptedTypes: {
        type: String,
        default: '.pdf,.jpg,.jpeg,.png,.doc,.docx',
    },
    maxSizeMB: {
        type: Number,
        default: 50,
    },
    multiple: {
        type: Boolean,
        default: true,
    },
    autoUpload: {
        type: Boolean,
        default: false,
    },
    hideTypeSelector: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['uploaded', 'error']);

const isDragging = ref(false);
const files = ref([]);
const documentType = ref('');
const uploading = ref(false);

const acceptedTypesLabel = computed(() => {
    return props.acceptedTypes
        .split(',')
        .map(type => type.replace('.', '').toUpperCase())
        .join(', ');
});

const hasErrors = computed(() => {
    return files.value.some(file => file.error);
});

const handleFileSelect = (event) => {
    const selectedFiles = Array.from(event.target.files);
    processFiles(selectedFiles);
    event.target.value = ''; // Reset input
};

const handleDrop = (event) => {
    isDragging.value = false;
    const droppedFiles = Array.from(event.dataTransfer.files);
    processFiles(droppedFiles);
};

const processFiles = (newFiles) => {
    const processedFiles = newFiles.map(file => {
        const fileObj = {
            file: file,
            name: file.name,
            size: file.size,
            uploading: false,
            uploaded: false,
            progress: 0,
            error: null,
        };

        // Validate file size
        if (file.size > props.maxSizeMB * 1024 * 1024) {
            fileObj.error = `Le fichier dépasse ${props.maxSizeMB}MB`;
        }

        // Validate file type
        const extension = '.' + file.name.split('.').pop().toLowerCase();
        if (!props.acceptedTypes.includes(extension)) {
            fileObj.error = `Type de fichier non autorisé`;
        }

        return fileObj;
    });

    files.value = [...files.value, ...processedFiles];

    if (props.autoUpload && !hasErrors.value) {
        uploadFiles();
    }
};

const removeFile = (index) => {
    files.value.splice(index, 1);
};

const clearFiles = () => {
    files.value = [];
    documentType.value = '';
};

const uploadFiles = async () => {
    if (hasErrors.value) return;

    uploading.value = true;

    for (const fileObj of files.value) {
        if (fileObj.uploaded || fileObj.error) continue;

        fileObj.uploading = true;

        try {
            const formData = new FormData();
            formData.append('file', fileObj.file);
            if (documentType.value) {
                formData.append('type', documentType.value);
            }

            // Simulate progress (in real app, use XMLHttpRequest for progress)
            const progressInterval = setInterval(() => {
                if (fileObj.progress < 90) {
                    fileObj.progress += 10;
                }
            }, 100);

            await new Promise((resolve, reject) => {
                router.post(route('dossiers.documents.store', props.dossierId), formData, {
                    preserveScroll: true,
                    onSuccess: () => {
                        clearInterval(progressInterval);
                        fileObj.progress = 100;
                        fileObj.uploaded = true;
                        fileObj.uploading = false;
                        resolve();
                    },
                    onError: (errors) => {
                        clearInterval(progressInterval);
                        fileObj.error = (errors && (errors.file || errors.type)) || 'Erreur lors du téléchargement';
                        fileObj.uploading = false;
                        reject(errors);
                    },
                });
            });

            emit('uploaded', fileObj);
        } catch (error) {
            emit('error', error);
        }
    }

    uploading.value = false;

    // Clear successfully uploaded files after a delay
    setTimeout(() => {
        files.value = files.value.filter(f => !f.uploaded);
        if (files.value.length === 0) {
            documentType.value = '';
        }
    }, 2000);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>
