<template>
    <div class="relative w-full max-w-lg">
        <!-- Search Input -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                v-model="searchQuery"
                @input="onSearchInput"
                @focus="showResults = true"
                @keydown.esc="closeResults"
                @keydown.down.prevent="navigateDown"
                @keydown.up.prevent="navigateUp"
                @keydown.enter.prevent="selectResult"
                type="text"
                placeholder="Rechercher dossiers, clients, documents..."
                class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition"
            />
            
            <!-- Clear Button -->
            <button
                v-if="searchQuery"
                @click="clearSearch"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Loading Spinner -->
            <div
                v-if="loading"
                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
            >
                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>

        <!-- Search Results Dropdown -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="showResults && (searchQuery.length >= 2 || hasResults)"
                class="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto"
                @click.stop
            >
                <!-- Loading State -->
                <div v-if="loading" class="p-4 text-center">
                    <svg class="inline animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Recherche...</p>
                </div>

                <!-- No Results -->
                <div v-else-if="!hasResults && searchQuery.length >= 2" class="p-4 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aucun résultat trouvé</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Essayez avec d'autres mots-clés</p>
                </div>

                <!-- Results by Category -->
                <div v-else class="py-2">
                    <!-- Dossiers -->
                    <div v-if="results.dossiers.length > 0">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Dossiers ({{ results.dossiers.length }})
                        </div>
                        <a
                            v-for="(item, index) in results.dossiers"
                            :key="`dossier-${item.id}`"
                            :href="item.url"
                            class="block px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer"
                            :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': selectedIndex === getGlobalIndex('dossiers', index) }"
                            @click="closeResults"
                        >
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="h-8 w-8 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white" v-html="highlight(item.title)"></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate" v-html="highlight(item.subtitle)"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 truncate">{{ item.description }}</p>
                                </div>
                                <span
                                    class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    :class="statusClass(item.status)"
                                >
                                    {{ item.status }}
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- Clients -->
                    <div v-if="results.clients.length > 0" class="border-t border-gray-200 dark:border-gray-700">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Clients ({{ results.clients.length }})
                        </div>
                        <a
                            v-for="(item, index) in results.clients"
                            :key="`client-${item.id}`"
                            :href="item.url"
                            class="block px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer"
                            :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': selectedIndex === getGlobalIndex('clients', index) }"
                            @click="closeResults"
                        >
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white" v-html="highlight(item.title)"></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate" v-html="highlight(item.subtitle)"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 truncate">{{ item.description }}</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Documents -->
                    <div v-if="results.documents.length > 0" class="border-t border-gray-200 dark:border-gray-700">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Documents ({{ results.documents.length }})
                        </div>
                        <a
                            v-for="(item, index) in results.documents"
                            :key="`document-${item.id}`"
                            :href="item.url"
                            class="block px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer"
                            :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': selectedIndex === getGlobalIndex('documents', index) }"
                            @click="closeResults"
                        >
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="h-8 w-8 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white" v-html="highlight(item.title)"></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate" v-html="highlight(item.subtitle)"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 truncate">{{ item.description }}</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Users -->
                    <div v-if="results.users.length > 0" class="border-t border-gray-200 dark:border-gray-700">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Utilisateurs ({{ results.users.length }})
                        </div>
                        <a
                            v-for="(item, index) in results.users"
                            :key="`user-${item.id}`"
                            :href="item.url"
                            class="block px-3 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer"
                            :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': selectedIndex === getGlobalIndex('users', index) }"
                            @click="closeResults"
                        >
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="h-8 w-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                        <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                                            {{ item.title.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white" v-html="highlight(item.title)"></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate" v-html="highlight(item.subtitle)"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 truncate">{{ item.description }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="hasResults" class="border-t border-gray-200 dark:border-gray-700 px-3 py-2 bg-gray-50 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ totalResults }} résultat{{ totalResults > 1 ? 's' : '' }} trouvé{{ totalResults > 1 ? 's' : '' }}
                        • Utilisez ↑↓ pour naviguer, ↵ pour sélectionner
                    </p>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const searchQuery = ref('');
const results = ref({
    dossiers: [],
    clients: [],
    documents: [],
    users: [],
});
const loading = ref(false);
const showResults = ref(false);
const selectedIndex = ref(0);
let debounceTimeout = null;

const hasResults = computed(() => {
    return results.value.dossiers.length > 0 ||
           results.value.clients.length > 0 ||
           results.value.documents.length > 0 ||
           results.value.users.length > 0;
});

const totalResults = computed(() => {
    return results.value.dossiers.length +
           results.value.clients.length +
           results.value.documents.length +
           results.value.users.length;
});

const allResults = computed(() => {
    return [
        ...results.value.dossiers,
        ...results.value.clients,
        ...results.value.documents,
        ...results.value.users,
    ];
});

const onSearchInput = () => {
    clearTimeout(debounceTimeout);
    
    if (searchQuery.value.length < 2) {
        results.value = { dossiers: [], clients: [], documents: [], users: [] };
        showResults.value = false;
        return;
    }

    debounceTimeout = setTimeout(async () => {
        await performSearch();
    }, 300);
};

const performSearch = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('search'), {
            params: { q: searchQuery.value, limit: 5 }
        });
        results.value = response.data.results;
        showResults.value = true;
        selectedIndex.value = 0;
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        loading.value = false;
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    results.value = { dossiers: [], clients: [], documents: [], users: [] };
    showResults.value = false;
    selectedIndex.value = 0;
};

const closeResults = () => {
    showResults.value = false;
};

const navigateDown = () => {
    if (selectedIndex.value < totalResults.value - 1) {
        selectedIndex.value++;
    }
};

const navigateUp = () => {
    if (selectedIndex.value > 0) {
        selectedIndex.value--;
    }
};

const selectResult = () => {
    if (allResults.value[selectedIndex.value]) {
        window.location.href = allResults.value[selectedIndex.value].url;
    }
};

const getGlobalIndex = (category, localIndex) => {
    let index = 0;
    const categories = ['dossiers', 'clients', 'documents', 'users'];
    
    for (const cat of categories) {
        if (cat === category) {
            return index + localIndex;
        }
        index += results.value[cat].length;
    }
    return 0;
};

const highlight = (text) => {
    if (!searchQuery.value || !text) return text;
    const regex = new RegExp(`(${searchQuery.value})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-600 text-gray-900 dark:text-white px-0.5 rounded">$1</mark>');
};

const statusClass = (status) => {
    const classes = {
        'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'completed': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (showResults.value && !event.target.closest('.relative')) {
        closeResults();
    }
};

document.addEventListener('click', handleClickOutside);
</script>
