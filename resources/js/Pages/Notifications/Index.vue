<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Notifications</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gérez toutes vos notifications</p>
            </div>

            <!-- Actions Bar -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex space-x-2">
                    <button
                        @click="filter = 'all'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition"
                        :class="filter === 'all' 
                            ? 'bg-indigo-600 text-white' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                        Toutes ({{ stats.total }})
                    </button>
                    <button
                        @click="filter = 'unread'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition"
                        :class="filter === 'unread' 
                            ? 'bg-indigo-600 text-white' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    >
                        Non lues ({{ stats.unread }})
                    </button>
                </div>

                <button
                    v-if="stats.unread > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition"
                >
                    Tout marquer comme lu
                </button>
            </div>

            <!-- Notifications List -->
            <Card>
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div v-else-if="filteredNotifications.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                        {{ filter === 'unread' ? 'Aucune notification non lue' : 'Aucune notification' }}
                    </p>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="notification in filteredNotifications"
                        :key="notification.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                        :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': !notification.read_at }"
                    >
                        <div class="flex items-start space-x-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ notification.data.title || 'Notification' }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ notification.data.message }}
                                        </p>
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                            {{ formatDate(notification.created_at) }}
                                        </p>
                                    </div>

                                    <!-- Unread Badge -->
                                    <span
                                        v-if="!notification.read_at"
                                        class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200"
                                    >
                                        Non lu
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4 flex items-center space-x-4">
                                    <a
                                        v-if="notification.data.action_url"
                                        :href="notification.data.action_url"
                                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                                        @click="markAsRead(notification.id)"
                                    >
                                        {{ notification.data.action_text || 'Voir' }} →
                                    </a>
                                    
                                    <button
                                        v-if="!notification.read_at"
                                        @click="markAsRead(notification.id)"
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium"
                                    >
                                        Marquer comme lu
                                    </button>

                                    <button
                                        @click="deleteNotification(notification.id)"
                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Load More -->
            <div v-if="hasMore && !loading" class="mt-6 text-center">
                <button
                    @click="loadMore"
                    class="px-6 py-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition border border-gray-200 dark:border-gray-700"
                >
                    Charger plus
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import axios from 'axios';

const notifications = ref([]);
const loading = ref(false);
const filter = ref('all');
const stats = ref({
    total: 0,
    unread: 0
});
const hasMore = ref(false);
const page = ref(1);

const filteredNotifications = computed(() => {
    if (filter.value === 'unread') {
        return notifications.value.filter(n => !n.read_at);
    }
    return notifications.value;
});

const loadNotifications = async (loadMore = false) => {
    loading.value = true;
    try {
        const response = await axios.get(route('notifications.index'), {
            params: { page: loadMore ? page.value : 1 }
        });
        
        if (loadMore) {
            notifications.value.push(...response.data.notifications);
        } else {
            notifications.value = response.data.notifications;
        }
        
        stats.value = {
            total: notifications.value.length,
            unread: response.data.unread_count
        };
        hasMore.value = response.data.has_more;
    } catch (error) {
        console.error('Error loading notifications:', error);
    } finally {
        loading.value = false;
    }
};

const loadMore = () => {
    page.value++;
    loadNotifications(true);
};

const markAsRead = async (id) => {
    try {
        await axios.post(route('notifications.markAsRead', id));
        
        const notification = notifications.value.find(n => n.id === id);
        if (notification) {
            notification.read_at = new Date().toISOString();
            stats.value.unread--;
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.markAllAsRead'));
        
        notifications.value.forEach(notification => {
            notification.read_at = new Date().toISOString();
        });
        stats.value.unread = 0;
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

const deleteNotification = async (id) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
        return;
    }

    try {
        await axios.delete(route('notifications.destroy', id));
        
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            const wasUnread = !notifications.value[index].read_at;
            notifications.value.splice(index, 1);
            stats.value.total--;
            if (wasUnread) {
                stats.value.unread--;
            }
        }
    } catch (error) {
        console.error('Error deleting notification:', error);
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'À l\'instant';
    if (diffMins < 60) return `Il y a ${diffMins} minute${diffMins > 1 ? 's' : ''}`;
    if (diffHours < 24) return `Il y a ${diffHours} heure${diffHours > 1 ? 's' : ''}`;
    if (diffDays < 7) return `Il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}`;
    
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    loadNotifications();
});
</script>
