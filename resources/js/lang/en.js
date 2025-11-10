export default {
    // Common
    welcome: 'Welcome',
    loading: 'Loading...',
    save: 'Save',
    cancel: 'Cancel',
    delete: 'Delete',
    edit: 'Edit',
    create: 'Create',
    search: 'Search',
    filter: 'Filter',
    export: 'Export',
    import: 'Import',
    close: 'Close',
    yes: 'Yes',
    no: 'No',
    confirm: 'Confirm',
    back: 'Back',
    next: 'Next',
    previous: 'Previous',
    finish: 'Finish',
    submit: 'Submit',
    
    // Navigation
    nav: {
        dashboard: 'Dashboard',
        dossiers: 'Files',
        documents: 'Documents',
        contracts: 'Contracts',
        clients: 'Clients',
        appointments: 'Appointments',
        invitations: 'Invitations',
        analytics: 'Analytics',
        notifications: 'Notifications',
        settings: 'Settings',
        profile: 'Profile',
        logout: 'Logout',
    },
    
    // Dashboard
    dashboard: {
        title: 'Dashboard',
        welcome: 'Welcome to your ELI Voyages space',
        stats: {
            totalDossiers: 'Total Files',
            activeDossiers: 'Active',
            documents: 'Documents',
            pendingSignatures: 'Pending Signatures',
            totalClients: 'Clients',
            appointments: 'Appointments',
            contracts: 'Contracts',
        },
        recentActivity: 'Recent Activity',
        recentDossiers: 'Recent Files',
        upcomingAppointments: 'Upcoming Appointments',
        quickActions: 'Quick Actions',
    },
    
    // Dossiers
    dossiers: {
        title: 'Files',
        create: 'Create File',
        edit: 'Edit File',
        view: 'View File',
        delete: 'Delete File',
        reference: 'Reference',
        client: 'Client',
        status: 'Status',
        package: 'Package',
        assignedTo: 'Assigned To',
        createdAt: 'Created At',
        updatedAt: 'Updated At',
        notes: 'Notes',
        timeline: 'Timeline',
        
        statuses: {
            draft: 'Draft',
            awaiting_docs: 'Awaiting Documents',
            under_review: 'Under Review',
            waiting_payment: 'Waiting Payment',
            approved: 'Approved',
            rejected: 'Rejected',
            closed: 'Closed',
            cancelled: 'Cancelled',
        },
    },
    
    // Documents
    documents: {
        title: 'Documents',
        upload: 'Upload Document',
        download: 'Download',
        delete: 'Delete',
        name: 'Name',
        type: 'Type',
        size: 'Size',
        uploadedBy: 'Uploaded By',
        uploadedAt: 'Uploaded At',
        visibility: 'Visibility',
        dragDrop: 'Drag and drop your files here or click to browse',
    },
    
    // Contracts
    contracts: {
        title: 'Contracts',
        generate: 'Generate Contract',
        send: 'Send for Signature',
        sign: 'Sign',
        download: 'Download',
        reference: 'Reference',
        type: 'Type',
        status: 'Status',
        createdAt: 'Created At',
        
        statuses: {
            draft: 'Draft',
            generated: 'Generated',
            sent: 'Sent',
            signed: 'Signed',
            expired: 'Expired',
            cancelled: 'Cancelled',
        },
    },
    
    // Clients
    clients: {
        title: 'Clients',
        create: 'Create Client',
        edit: 'Edit Client',
        view: 'View Client',
        name: 'Name',
        email: 'Email',
        phone: 'Phone',
        language: 'Language',
        createdAt: 'Created At',
    },
    
    // Settings
    settings: {
        title: 'Settings',
        profile: 'Profile',
        preferences: 'Preferences',
        security: 'Security',
        notifications: 'Notifications',
        theme: 'Theme',
        language: 'Language',
        darkMode: 'Dark Mode',
        lightMode: 'Light Mode',
    },
    
    // Roles
    roles: {
        SuperAdmin: 'Super Administrator',
        Consultant: 'Consultant',
        Agent: 'Agent',
        Client: 'Client',
        Guarantor: 'Guarantor',
    },
    
    // Messages
    messages: {
        success: {
            created: '{item} created successfully',
            updated: '{item} updated successfully',
            deleted: '{item} deleted successfully',
            saved: 'Saved successfully',
        },
        error: {
            generic: 'An error occurred',
            notFound: '{item} not found',
            unauthorized: 'Unauthorized',
            validation: 'Validation error',
        },
        confirm: {
            delete: 'Are you sure you want to delete {item}?',
        },
    },
    
    // Form
    form: {
        required: 'This field is required',
        email: 'Invalid email',
        phone: 'Invalid phone number',
        minLength: 'Minimum {min} characters',
        maxLength: 'Maximum {max} characters',
        selectOption: 'Select an option',
    },
};
