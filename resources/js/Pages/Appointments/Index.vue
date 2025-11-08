<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/Card.vue';
import axios from 'axios';

defineProps({
    isAgent: Boolean,
});

const currentDate = ref(new Date());
const appointments = ref([]);
const selectedAppointment = ref(null);
const showAppointmentModal = ref(false);
const showBookingModal = ref(false);
const loading = ref(false);
const agents = ref([]);
const availableSlots = ref([]);

// Form data
const bookingForm = ref({
    agent_id: null,
    scheduled_at: '',
    duration_minutes: 30,
    type: 'consultation',
    client_notes: '',
    meeting_link: '',
    location: '',
});

const appointmentTypes = [
    { value: 'consultation', label: 'Consultation', icon: '💬' },
    { value: 'document_review', label: 'Révision de documents', icon: '📄' },
    { value: 'signing', label: 'Signature', icon: '✍️' },
    { value: 'follow_up', label: 'Suivi', icon: '📋' },
];

const statusColors = {
    scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    confirmed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    completed: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    no_show: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
};

const statusLabels = {
    scheduled: 'Planifié',
    confirmed: 'Confirmé',
    completed: 'Terminé',
    cancelled: 'Annulé',
    no_show: 'Absent',
};

// Calendar functions
const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    return new Date(year, month + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    return new Date(year, month, 1).getDay();
});

const monthName = computed(() => {
    return currentDate.value.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
});

const calendarDays = computed(() => {
    const days = [];
    const total = daysInMonth.value;
    const firstDay = firstDayOfMonth.value;
    
    // Add empty slots for days before month starts
    for (let i = 0; i < firstDay; i++) {
        days.push(null);
    }
    
    // Add days of month
    for (let day = 1; day <= total; day++) {
        const date = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), day);
        const dayAppointments = appointments.value.filter(apt => {
            const aptDate = new Date(apt.scheduled_at);
            return aptDate.toDateString() === date.toDateString();
        });
        
        days.push({
            day,
            date,
            appointments: dayAppointments,
            isToday: date.toDateString() === new Date().toDateString(),
            isPast: date < new Date() && !date.toDateString() === new Date().toDateString(),
        });
    }
    
    return days;
});

const upcomingAppointments = computed(() => {
    const now = new Date();
    return appointments.value
        .filter(apt => new Date(apt.scheduled_at) > now && ['scheduled', 'confirmed'].includes(apt.status))
        .sort((a, b) => new Date(a.scheduled_at) - new Date(b.scheduled_at))
        .slice(0, 5);
});

// Methods
const loadAppointments = async () => {
    loading.value = true;
    try {
        const start = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
        const end = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 0);
        
        const response = await axios.get('/appointments/data', {
            params: {
                start: start.toISOString(),
                end: end.toISOString(),
            }
        });
        appointments.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement des rendez-vous:', error);
    } finally {
        loading.value = false;
    }
};

const loadAgents = async () => {
    try {
        const response = await axios.get('/appointments/agents');
        agents.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement des agents:', error);
    }
};

const loadAvailableSlots = async (date) => {
    if (!bookingForm.value.agent_id) return;
    
    try {
        const response = await axios.get('/appointments/slots', {
            params: {
                agent_id: bookingForm.value.agent_id,
                date: date,
            }
        });
        availableSlots.value = response.data;
    } catch (error) {
        console.error('Erreur lors du chargement des créneaux:', error);
    }
};

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1);
    loadAppointments();
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1);
    loadAppointments();
};

const today = () => {
    currentDate.value = new Date();
    loadAppointments();
};

const openBookingModal = (day = null) => {
    if (day) {
        bookingForm.value.scheduled_at = day.date.toISOString().split('T')[0];
        if (bookingForm.value.agent_id) {
            loadAvailableSlots(bookingForm.value.scheduled_at);
        }
    }
    showBookingModal.value = true;
};

const closeBookingModal = () => {
    showBookingModal.value = false;
    resetBookingForm();
};

const resetBookingForm = () => {
    bookingForm.value = {
        agent_id: null,
        scheduled_at: '',
        duration_minutes: 30,
        type: 'consultation',
        client_notes: '',
        meeting_link: '',
        location: '',
    };
    availableSlots.value = [];
};

const bookAppointment = async () => {
    loading.value = true;
    try {
        await axios.post('/appointments', bookingForm.value);
        closeBookingModal();
        loadAppointments();
        alert('Rendez-vous réservé avec succès !');
    } catch (error) {
        console.error('Erreur lors de la réservation:', error);
        alert('Erreur lors de la réservation du rendez-vous');
    } finally {
        loading.value = false;
    }
};

const viewAppointment = (appointment) => {
    selectedAppointment.value = appointment;
    showAppointmentModal.value = true;
};

const closeAppointmentModal = () => {
    showAppointmentModal.value = false;
    selectedAppointment.value = null;
};

const confirmAppointment = async (appointment) => {
    if (!confirm('Confirmer ce rendez-vous ?')) return;
    
    try {
        await axios.post(`/appointments/${appointment.id}/confirm`);
        loadAppointments();
        closeAppointmentModal();
    } catch (error) {
        console.error('Erreur:', error);
    }
};

const cancelAppointment = async (appointment) => {
    const reason = prompt('Raison de l\'annulation (optionnel) :');
    if (reason === null) return;
    
    try {
        await axios.post(`/appointments/${appointment.id}/cancel`, { reason });
        loadAppointments();
        closeAppointmentModal();
    } catch (error) {
        console.error('Erreur:', error);
    }
};

const formatTime = (datetime) => {
    return new Date(datetime).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (datetime) => {
    return new Date(datetime).toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

onMounted(() => {
    loadAppointments();
    loadAgents();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    📅 Calendrier des rendez-vous
                </h1>
                <button
                    @click="openBookingModal()"
                    class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition-all"
                >
                    + Nouveau rendez-vous
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Calendar -->
                <div class="lg:col-span-2">
                    <Card>
                        <!-- Calendar Header -->
                        <div class="flex items-center justify-between mb-6">
                            <button
                                @click="previousMonth"
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                            >
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            
                            <div class="text-center">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white capitalize">
                                    {{ monthName }}
                                </h2>
                            </div>
                            
                            <div class="flex gap-2">
                                <button
                                    @click="today"
                                    class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                >
                                    Aujourd'hui
                                </button>
                                <button
                                    @click="nextMonth"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-1">
                            <!-- Day headers -->
                            <div v-for="day in ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam']" :key="day"
                                class="text-center text-xs font-semibold text-gray-600 dark:text-gray-400 py-2">
                                {{ day }}
                            </div>

                            <!-- Calendar days -->
                            <div v-for="(day, index) in calendarDays" :key="index"
                                :class="[
                                    'min-h-24 p-1 border border-gray-200 dark:border-gray-700 rounded-lg',
                                    day?.isToday ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-500' : '',
                                    day?.isPast ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-800',
                                    day ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors' : ''
                                ]"
                                @click="day && !day.isPast && openBookingModal(day)"
                            >
                                <div v-if="day">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                        {{ day.day }}
                                    </div>
                                    <div v-for="apt in day.appointments.slice(0, 2)" :key="apt.id"
                                        class="text-xs p-1 mb-1 rounded truncate cursor-pointer"
                                        :class="statusColors[apt.status]"
                                        @click.stop="viewAppointment(apt)"
                                    >
                                        {{ formatTime(apt.scheduled_at) }}
                                    </div>
                                    <div v-if="day.appointments.length > 2"
                                        class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                        +{{ day.appointments.length - 2 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Upcoming Appointments Sidebar -->
                <div>
                    <Card>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                            Prochains rendez-vous
                        </h3>
                        
                        <div v-if="upcomingAppointments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            Aucun rendez-vous à venir
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="apt in upcomingAppointments" :key="apt.id"
                                @click="viewAppointment(apt)"
                                class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-xs font-semibold px-2 py-1 rounded" :class="statusColors[apt.status]">
                                        {{ statusLabels[apt.status] }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ apt.duration_minutes }} min
                                    </span>
                                </div>
                                
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ formatDate(apt.scheduled_at) }}
                                </div>
                                
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    🕐 {{ formatTime(apt.scheduled_at) }}
                                </div>
                                
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <span v-if="isAgent">👤 {{ apt.client.name }}</span>
                                    <span v-else>👨‍💼 {{ apt.agent.name }}</span>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Booking Modal -->
        <div v-if="showBookingModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Réserver un rendez-vous
                        </h2>
                        <button @click="closeBookingModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="bookAppointment" class="space-y-4">
                        <!-- Agent Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Agent *
                            </label>
                            <select v-model="bookingForm.agent_id" required
                                @change="bookingForm.scheduled_at && loadAvailableSlots(bookingForm.scheduled_at)"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option :value="null">Sélectionner un agent</option>
                                <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                                    {{ agent.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Date Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date *
                            </label>
                            <input type="date" v-model="bookingForm.scheduled_at" required
                                @change="bookingForm.agent_id && loadAvailableSlots(bookingForm.scheduled_at)"
                                :min="new Date().toISOString().split('T')[0]"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                        </div>

                        <!-- Time Slots -->
                        <div v-if="availableSlots.length > 0">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Créneau horaire *
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="slot in availableSlots" :key="slot.time" type="button"
                                    @click="bookingForm.scheduled_at = slot.datetime"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg border transition-colors',
                                        bookingForm.scheduled_at === slot.datetime
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    ]">
                                    {{ slot.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Type de rendez-vous *
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="type in appointmentTypes" :key="type.value" type="button"
                                    @click="bookingForm.type = type.value"
                                    :class="[
                                        'px-4 py-3 text-sm rounded-lg border transition-colors text-left',
                                        bookingForm.type === type.value
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    ]">
                                    <span class="mr-2">{{ type.icon }}</span>
                                    {{ type.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Durée
                            </label>
                            <select v-model.number="bookingForm.duration_minutes"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                <option :value="15">15 minutes</option>
                                <option :value="30">30 minutes</option>
                                <option :value="45">45 minutes</option>
                                <option :value="60">1 heure</option>
                                <option :value="90">1h30</option>
                                <option :value="120">2 heures</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Notes / Questions
                            </label>
                            <textarea v-model="bookingForm.client_notes" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                placeholder="Ajoutez vos questions ou informations importantes..."></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeBookingModal"
                                class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors">
                                Annuler
                            </button>
                            <button type="submit" :disabled="loading"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all disabled:opacity-50">
                                Réserver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Appointment Details Modal -->
        <div v-if="showAppointmentModal && selectedAppointment" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-xl w-full">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                Détails du rendez-vous
                            </h2>
                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full" :class="statusColors[selectedAppointment.status]">
                                {{ statusLabels[selectedAppointment.status] }}
                            </span>
                        </div>
                        <button @click="closeAppointmentModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ formatDate(selectedAppointment.scheduled_at) }}</span>
                        </div>

                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ formatTime(selectedAppointment.scheduled_at) }} ({{ selectedAppointment.duration_minutes }} min)</span>
                        </div>

                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span v-if="isAgent">Client: {{ selectedAppointment.client.name }}</span>
                            <span v-else>Agent: {{ selectedAppointment.agent.name }}</span>
                        </div>

                        <div v-if="selectedAppointment.client_notes" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes du client:</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedAppointment.client_notes }}</p>
                        </div>

                        <div v-if="selectedAppointment.notes && isAgent" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-1">Notes internes:</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400">{{ selectedAppointment.notes }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button v-if="isAgent && selectedAppointment.status === 'scheduled'"
                            @click="confirmAppointment(selectedAppointment)"
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            Confirmer
                        </button>
                        <button v-if="['scheduled', 'confirmed'].includes(selectedAppointment.status)"
                            @click="cancelAppointment(selectedAppointment)"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                            Annuler
                        </button>
                        <button @click="closeAppointmentModal"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
