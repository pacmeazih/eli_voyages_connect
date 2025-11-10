import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUIStore = defineStore('ui', () => {
    // State
    const isLoading = ref(false);
    const toasts = ref([]);
    const errors = ref([]);
    const sidebarOpen = ref(true);
    const modalOpen = ref(false);
    const modalComponent = ref(null);
    const modalProps = ref({});

    // Toast ID counter
    let toastIdCounter = 0;

    // Actions
    function setLoading(loading) {
        isLoading.value = loading;
    }

    function addToast(toast) {
        const id = ++toastIdCounter;
        const newToast = {
            id,
            message: toast.message || toast,
            type: toast.type || 'info', // 'success', 'error', 'warning', 'info'
            duration: toast.duration || 5000,
            dismissible: toast.dismissible !== false,
        };

        toasts.value.push(newToast);

        // Auto-dismiss after duration
        if (newToast.duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
        }

        return id;
    }

    function showSuccess(message, duration = 5000) {
        return addToast({ message, type: 'success', duration });
    }

    function showError(message, duration = 7000) {
        return addToast({ message, type: 'error', duration });
    }

    function showWarning(message, duration = 6000) {
        return addToast({ message, type: 'warning', duration });
    }

    function showInfo(message, duration = 5000) {
        return addToast({ message, type: 'info', duration });
    }

    function removeToast(id) {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    }

    function clearToasts() {
        toasts.value = [];
    }

    function addError(error) {
        const errorObj = {
            id: Date.now(),
            message: error.message || error,
            code: error.code || null,
            details: error.details || null,
        };
        errors.value.push(errorObj);
    }

    function removeError(id) {
        const index = errors.value.findIndex(e => e.id === id);
        if (index !== -1) {
            errors.value.splice(index, 1);
        }
    }

    function clearErrors() {
        errors.value = [];
    }

    function toggleSidebar() {
        sidebarOpen.value = !sidebarOpen.value;
    }

    function openModal(component, props = {}) {
        modalComponent.value = component;
        modalProps.value = props;
        modalOpen.value = true;
    }

    function closeModal() {
        modalOpen.value = false;
        // Clear after animation
        setTimeout(() => {
            modalComponent.value = null;
            modalProps.value = {};
        }, 300);
    }

    return {
        // State
        isLoading,
        toasts,
        errors,
        sidebarOpen,
        modalOpen,
        modalComponent,
        modalProps,
        // Actions
        setLoading,
        addToast,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        removeToast,
        clearToasts,
        addError,
        removeError,
        clearErrors,
        toggleSidebar,
        openModal,
        closeModal,
    };
});
