<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
            Progression de votre dossier
        </h3>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="relative">
                <!-- Background line -->
                <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700"></div>
                
                <!-- Progress line -->
                <div 
                    class="absolute top-5 left-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-500"
                    :style="{ width: progressPercentage + '%' }"
                ></div>

                <!-- Steps -->
                <div class="relative flex justify-between">
                    <div 
                        v-for="(step, index) in steps" 
                        :key="index"
                        class="flex flex-col items-center"
                        :class="{ 'flex-1': index !== steps.length - 1 }"
                    >
                        <!-- Step Circle -->
                        <div 
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 z-10',
                                getStepClasses(step.status)
                            ]"
                        >
                            <!-- Completed Icon -->
                            <svg v-if="step.status === 'completed'" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            
                            <!-- Active Spinner -->
                            <div v-else-if="step.status === 'active'" class="relative">
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            </div>
                            
                            <!-- Pending Number -->
                            <span v-else class="text-gray-500">{{ index + 1 }}</span>
                        </div>

                        <!-- Step Label -->
                        <div class="mt-3 text-center max-w-[120px]">
                            <p 
                                :class="[
                                    'text-xs font-medium transition-colors',
                                    step.status === 'completed' ? 'text-green-600 dark:text-green-400' :
                                    step.status === 'active' ? 'text-amber-600 dark:text-amber-400' :
                                    'text-gray-500 dark:text-gray-400'
                                ]"
                            >
                                {{ step.label }}
                            </p>
                            <p 
                                v-if="step.date" 
                                class="text-xs text-gray-400 dark:text-gray-500 mt-1"
                            >
                                {{ formatDate(step.date) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Status Card -->
        <div 
            :class="[
                'rounded-lg p-4 border-l-4',
                currentStep.status === 'completed' ? 'bg-green-50 dark:bg-green-900/20 border-green-500' :
                currentStep.status === 'active' ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-500' :
                'bg-gray-50 dark:bg-gray-900/20 border-gray-300'
            ]"
        >
            <div class="flex items-start gap-3">
                <div 
                    :class="[
                        'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                        currentStep.status === 'completed' ? 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400' :
                        currentStep.status === 'active' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400' :
                        'bg-gray-100 dark:bg-gray-800 text-gray-500'
                    ]"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 
                        :class="[
                            'text-sm font-semibold mb-1',
                            currentStep.status === 'completed' ? 'text-green-900 dark:text-green-100' :
                            currentStep.status === 'active' ? 'text-amber-900 dark:text-amber-100' :
                            'text-gray-900 dark:text-gray-100'
                        ]"
                    >
                        {{ currentStep.label }}
                    </h4>
                    <p 
                        :class="[
                            'text-xs',
                            currentStep.status === 'completed' ? 'text-green-700 dark:text-green-300' :
                            currentStep.status === 'active' ? 'text-amber-700 dark:text-amber-300' :
                            'text-gray-600 dark:text-gray-400'
                        ]"
                    >
                        {{ currentStep.description }}
                    </p>
                    
                    <!-- Action Button (if current step needs action) -->
                    <button
                        v-if="currentStep.action && currentStep.status === 'active'"
                        @click="$emit('action', currentStep.action)"
                        class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-r from-amber-600 to-orange-600 rounded-lg hover:from-amber-700 hover:to-orange-700 transition-all"
                    >
                        {{ currentStep.actionLabel }}
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ completedCount }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Complété</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ activeCount }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">En cours</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-500 dark:text-gray-400">{{ pendingCount }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">À venir</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    steps: {
        type: Array,
        required: true,
        // Example structure:
        // [
        //   { label: 'Soumission', status: 'completed', date: '2024-01-15', description: 'Dossier soumis avec succès' },
        //   { label: 'Documents', status: 'active', description: 'Téléversez vos documents', action: 'upload', actionLabel: 'Ajouter des documents' },
        //   { label: 'Paiement', status: 'pending', description: 'En attente des documents' },
        //   { label: 'Traitement', status: 'pending', description: 'En attente du paiement' },
        //   { label: 'Approbation', status: 'pending', description: 'En attente du traitement' },
        // ]
    },
});

defineEmits(['action']);

const currentStep = computed(() => {
    return props.steps.find(step => step.status === 'active') || props.steps[props.steps.length - 1];
});

const completedCount = computed(() => {
    return props.steps.filter(step => step.status === 'completed').length;
});

const activeCount = computed(() => {
    return props.steps.filter(step => step.status === 'active').length;
});

const pendingCount = computed(() => {
    return props.steps.filter(step => step.status === 'pending').length;
});

const progressPercentage = computed(() => {
    if (props.steps.length === 0) return 0;
    const completed = completedCount.value;
    return (completed / props.steps.length) * 100;
});

const getStepClasses = (status) => {
    switch (status) {
        case 'completed':
            return 'bg-green-600 text-white shadow-lg shadow-green-500/50';
        case 'active':
            return 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/50';
        case 'pending':
        default:
            return 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    }).format(date);
};
</script>
