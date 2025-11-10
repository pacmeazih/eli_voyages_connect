<template>
    <component :is="dashboardComponent" v-bind="dashboardProps" />
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useUserStore } from '@/stores/user';

const page = usePage();
const userStore = useUserStore();

// Initialize user store
userStore.initializeFromPage();

// Determine which dashboard to load based on user role
const dashboardComponent = computed(() => {
    const role = userStore.primaryRole;
    
    const dashboards = {
        SuperAdmin: defineAsyncComponent(() => import('./Roles/SuperAdmin.vue')),
        Consultant: defineAsyncComponent(() => import('./Roles/Consultant.vue')),
        Agent: defineAsyncComponent(() => import('./Roles/Agent.vue')),
        Client: defineAsyncComponent(() => import('./Roles/Client.vue')),
        Guarantor: defineAsyncComponent(() => import('./Roles/Guarantor.vue')),
    };
    
    return dashboards[role] || dashboards.Client;
});

// Pass all page props to the dashboard component
const dashboardProps = computed(() => ({
    stats: page.props.stats || {},
    recentDossiers: page.props.recentDossiers || [],
    recentActivity: page.props.recentActivity || [],
    upcomingAppointments: page.props.upcomingAppointments || [],
    pendingActions: page.props.pendingActions || [],
    analytics: page.props.analytics || {},
}));
</script>
