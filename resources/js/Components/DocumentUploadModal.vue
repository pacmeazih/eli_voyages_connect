<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="close" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/30" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
                            <DialogTitle as="h3" class="text-xl font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-6">
                                Téléverser un document
                            </DialogTitle>

                            <form @submit.prevent="submit">
                                <!-- Document Type Selection -->
                                <div class="mb-6">
                                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Type de document <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50"
                                        :class="{ 'border-red-500': form.errors.type }"
                                    >
                                        <option value="">-- Sélectionnez un type --</option>
                                        <option value="passeport">Passeport</option>
                                        <option value="carte_identite">Carte d'identité</option>
                                        <option value="photo">Photo d'identité</option>
                                        <option value="diplome">Diplôme</option>
                                        <option value="relevé_notes">Relevé de notes</option>
                                        <option value="cv">Curriculum Vitae</option>
                                        <option value="lettre_motivation">Lettre de motivation</option>
                                        <option value="certificat_naissance">Certificat de naissance</option>
                                        <option value="certificat_mariage">Certificat de mariage</option>
                                        <option value="preuve_paiement">Preuve de paiement</option>
                                        <option value="attestation">Attestation</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                    <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.type }}
                                    </p>
                                </div>

                                <!-- File Upload Area -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fichier <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <!-- Drop Zone -->
                                    <div
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop"
                                        :class="[
                                            'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors',
                                            isDragging 
                                                ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' 
                                                : 'border-gray-300 dark:border-gray-600 hover:border-amber-400 dark:hover:border-amber-500',
                                            form.errors.file ? 'border-red-500' : ''
                                        ]"
                                        @click="$refs.fileInput.click()"
                                    >
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            class="hidden"
                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                            @change="handleFileSelect"
                                        />

                                        <!-- Icon and Instructions -->
                                        <div v-if="!selectedFile">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-semibold text-amber-600 dark:text-amber-400">Cliquez pour sélectionner</span> ou glissez-déposez
                                            </p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                                PDF, JPG, PNG, DOC jusqu'à 10MB
                                            </p>
                                        </div>

                                        <!-- Selected File Preview -->
                                        <div v-else class="flex items-center justify-center gap-3">
                                            <svg class="h-10 w-10 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="text-left flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ selectedFile.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(selectedFile.size) }}</p>
                                            </div>
                                            <button
                                                type="button"
                                                @click.stop="removeFile"
                                                class="text-red-600 hover:text-red-700 dark:text-red-400"
                                            >
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.file" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.file }}
                                    </p>
                                </div>

                                <!-- Optional Description -->
                                <div class="mb-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Description (optionnel)
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50"
                                        placeholder="Ajoutez une note ou description pour ce document..."
                                    ></textarea>
                                </div>

                                <!-- Progress Bar (when uploading) -->
                                <div v-if="form.progress" class="mb-6">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                        <div 
                                            class="bg-gradient-to-r from-amber-500 to-orange-500 h-full transition-all duration-300"
                                            :style="{ width: `${form.progress.percentage}%` }"
                                        ></div>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 text-center">
                                        Téléversement en cours... {{ form.progress.percentage }}%
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="close"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                        :disabled="form.processing"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-5 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-600 to-orange-600 rounded-lg hover:from-amber-700 hover:to-orange-700 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        :disabled="form.processing || !selectedFile || !form.type"
                                    >
                                        <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                                            <path d="M9 13h2v5a1 1 0 11-2 0v-5z" />
                                        </svg>
                                        Téléverser
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';

const props = defineProps({
    isOpen: Boolean,
    dossierId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['close', 'uploaded']);

const isDragging = ref(false);
const selectedFile = ref(null);
const fileInput = ref(null);

const form = useForm({
    type: '',
    file: null,
    description: '',
});

// Reset form when modal opens
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        form.reset();
        selectedFile.value = null;
    }
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        validateAndSetFile(file);
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        validateAndSetFile(file);
    }
};

const validateAndSetFile = (file) => {
    // Max 10MB
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('Le fichier est trop volumineux. Maximum 10MB.');
        return;
    }

    // Accepted types
    const acceptedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!acceptedTypes.includes(file.type)) {
        alert('Type de fichier non supporté. Utilisez PDF, JPG, PNG ou DOC.');
        return;
    }

    selectedFile.value = file;
    form.file = file;
};

const removeFile = () => {
    selectedFile.value = null;
    form.file = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const submit = () => {
    form.post(route('dossiers.documents.store', props.dossierId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('uploaded');
            close();
        },
    });
};

const close = () => {
    if (!form.processing) {
        emit('close');
    }
};
</script>
