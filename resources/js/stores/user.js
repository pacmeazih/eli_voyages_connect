import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const useUserStore = defineStore('user', () => {
    // State
    const user = ref(null);
    const permissions = ref([]);
    const roles = ref([]);

    // Getters
    const isAuthenticated = computed(() => user.value !== null);
    const fullName = computed(() => user.value?.name || '');
    const email = computed(() => user.value?.email || '');
    const primaryRole = computed(() => roles.value[0] || null);
    const clientId = computed(() => user.value?.client_id || null);
    const hasClientAccount = computed(() => !!user.value?.client_id);
    
    // Role checks
    const isSuperAdmin = computed(() => roles.value.includes('SuperAdmin'));
    const isConsultant = computed(() => roles.value.includes('Consultant'));
    const isAgent = computed(() => roles.value.includes('Agent'));
    const isClient = computed(() => roles.value.includes('Client'));
    const isGuarantor = computed(() => roles.value.includes('Guarantor'));
    const isStaff = computed(() => isSuperAdmin.value || isConsultant.value || isAgent.value);

    // Actions
    function initializeFromPage() {
        const page = usePage();
        if (page.props.auth?.user) {
            user.value = page.props.auth.user;
            permissions.value = page.props.auth.user.permissions || [];
            roles.value = page.props.auth.user.roles || [];
        }
    }

    function can(permission) {
        return permissions.value.includes(permission);
    }

    function canAny(permissionsArray) {
        return permissionsArray.some(permission => permissions.value.includes(permission));
    }

    function canAll(permissionsArray) {
        return permissionsArray.every(permission => permissions.value.includes(permission));
    }

    function hasRole(role) {
        return roles.value.includes(role);
    }

    function hasAnyRole(rolesArray) {
        return rolesArray.some(role => roles.value.includes(role));
    }

    function updateUser(userData) {
        user.value = { ...user.value, ...userData };
    }

    function clearUser() {
        user.value = null;
        permissions.value = [];
        roles.value = [];
    }

    return {
        // State
        user,
        permissions,
        roles,
        // Getters
        isAuthenticated,
        fullName,
        email,
        primaryRole,
        clientId,
        hasClientAccount,
        isSuperAdmin,
        isConsultant,
        isAgent,
        isClient,
        isGuarantor,
        isStaff,
        // Actions
        initializeFromPage,
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
        updateUser,
        clearUser,
    };
});
