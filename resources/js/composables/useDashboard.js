import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useDashboard() {
    const page = usePage();
    
    const stats = computed(() => page.props.stats || {});
    const recentDossiers = computed(() => page.props.recentDossiers || []);
    const recentActivity = computed(() => page.props.recentActivity || []);
    const upcomingAppointments = computed(() => page.props.upcomingAppointments || []);
    const pendingActions = computed(() => page.props.pendingActions || []);
    const analytics = computed(() => page.props.analytics || {});
    
    return {
        stats,
        recentDossiers,
        recentActivity,
        upcomingAppointments,
        pendingActions,
        analytics,
    };
}

export function useDashboardStats() {
    const { stats } = useDashboard();
    
    const formatStat = (value, format = 'number') => {
        if (format === 'currency') {
            return new Intl.NumberFormat('fr-FR', { 
                style: 'currency', 
                currency: 'EUR' 
            }).format(value);
        }
        if (format === 'percentage') {
            return `${value}%`;
        }
        return value.toLocaleString('fr-FR');
    };
    
    return {
        stats,
        formatStat,
    };
}
