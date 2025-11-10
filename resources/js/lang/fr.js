export default {
    // Common
    welcome: 'Bienvenue',
    loading: 'Chargement...',
    save: 'Enregistrer',
    cancel: 'Annuler',
    delete: 'Supprimer',
    edit: 'Modifier',
    create: 'Créer',
    search: 'Rechercher',
    filter: 'Filtrer',
    export: 'Exporter',
    import: 'Importer',
    close: 'Fermer',
    yes: 'Oui',
    no: 'Non',
    confirm: 'Confirmer',
    back: 'Retour',
    next: 'Suivant',
    previous: 'Précédent',
    finish: 'Terminer',
    submit: 'Soumettre',
    
    // Navigation
    nav: {
        dashboard: 'Tableau de bord',
        dossiers: 'Dossiers',
        documents: 'Documents',
        contracts: 'Contrats',
        clients: 'Clients',
        appointments: 'Rendez-vous',
        invitations: 'Invitations',
        analytics: 'Analytics',
        notifications: 'Notifications',
        settings: 'Paramètres',
        profile: 'Profil',
        logout: 'Déconnexion',
    },
    
    // Dashboard
    dashboard: {
        title: 'Tableau de bord',
        welcome: 'Bienvenue sur votre espace ELI Voyages',
        stats: {
            totalDossiers: 'Total Dossiers',
            activeDossiers: 'En cours',
            documents: 'Documents',
            pendingSignatures: 'Signatures en attente',
            totalClients: 'Clients',
            appointments: 'Rendez-vous',
            contracts: 'Contrats',
        },
        recentActivity: 'Activité récente',
        recentDossiers: 'Dossiers récents',
        upcomingAppointments: 'Rendez-vous à venir',
        quickActions: 'Actions rapides',
    },
    
    // Dossiers
    dossiers: {
        title: 'Dossiers',
        create: 'Créer un dossier',
        edit: 'Modifier le dossier',
        view: 'Voir le dossier',
        delete: 'Supprimer le dossier',
        reference: 'Référence',
        client: 'Client',
        status: 'Statut',
        package: 'Forfait',
        assignedTo: 'Assigné à',
        createdAt: 'Créé le',
        updatedAt: 'Mis à jour le',
        notes: 'Notes',
        timeline: 'Chronologie',
        
        statuses: {
            draft: 'Brouillon',
            awaiting_docs: 'En attente documents',
            under_review: 'En révision',
            waiting_payment: 'En attente paiement',
            approved: 'Approuvé',
            rejected: 'Rejeté',
            closed: 'Clôturé',
            cancelled: 'Annulé',
        },
    },
    
    // Documents
    documents: {
        title: 'Documents',
        upload: 'Télécharger un document',
        download: 'Télécharger',
        delete: 'Supprimer',
        name: 'Nom',
        type: 'Type',
        size: 'Taille',
        uploadedBy: 'Téléchargé par',
        uploadedAt: 'Téléchargé le',
        visibility: 'Visibilité',
        dragDrop: 'Glissez et déposez vos fichiers ici ou cliquez pour parcourir',
    },
    
    // Contracts
    contracts: {
        title: 'Contrats',
        generate: 'Générer un contrat',
        send: 'Envoyer pour signature',
        sign: 'Signer',
        download: 'Télécharger',
        reference: 'Référence',
        type: 'Type',
        status: 'Statut',
        createdAt: 'Créé le',
        
        statuses: {
            draft: 'Brouillon',
            generated: 'Généré',
            sent: 'Envoyé',
            signed: 'Signé',
            expired: 'Expiré',
            cancelled: 'Annulé',
        },
    },
    
    // Clients
    clients: {
        title: 'Clients',
        create: 'Créer un client',
        edit: 'Modifier le client',
        view: 'Voir le client',
        name: 'Nom',
        email: 'Email',
        phone: 'Téléphone',
        language: 'Langue',
        createdAt: 'Créé le',
    },
    
    // Settings
    settings: {
        title: 'Paramètres',
        profile: 'Profil',
        preferences: 'Préférences',
        security: 'Sécurité',
        notifications: 'Notifications',
        theme: 'Thème',
        language: 'Langue',
        darkMode: 'Mode sombre',
        lightMode: 'Mode clair',
    },
    
    // Roles
    roles: {
        SuperAdmin: 'Super Administrateur',
        Consultant: 'Consultant',
        Agent: 'Agent',
        Client: 'Client',
        Guarantor: 'Garant',
    },
    
    // Messages
    messages: {
        success: {
            created: '{item} créé avec succès',
            updated: '{item} mis à jour avec succès',
            deleted: '{item} supprimé avec succès',
            saved: 'Enregistré avec succès',
        },
        error: {
            generic: 'Une erreur s\'est produite',
            notFound: '{item} non trouvé',
            unauthorized: 'Non autorisé',
            validation: 'Erreur de validation',
        },
        confirm: {
            delete: 'Êtes-vous sûr de vouloir supprimer {item} ?',
        },
    },
    
    // Form
    form: {
        required: 'Ce champ est requis',
        email: 'Email invalide',
        phone: 'Numéro de téléphone invalide',
        minLength: 'Minimum {min} caractères',
        maxLength: 'Maximum {max} caractères',
        selectOption: 'Sélectionnez une option',
    },
};
