# 🚀 ELI Voyages Connect - Backend Features Documentation

> Documentation complète des fonctionnalités backend de la plateforme ELI Voyages Connect
> 
> **Version**: 1.0  
> **Framework**: Laravel 11.46.1  
> **Date**: 8 novembre 2025

---

## 📋 Table des matières

1. [Architecture](#architecture)
2. [Authentification & Autorisation](#authentification--autorisation)
3. [Gestion des Utilisateurs](#gestion-des-utilisateurs)
4. [Gestion des Dossiers](#gestion-des-dossiers)
5. [Gestion des Documents](#gestion-des-documents)
6. [Système de Contrats](#système-de-contrats)
7. [Notifications](#notifications)
8. [Rendez-vous](#rendez-vous)
9. [Analytics](#analytics)
10. [Invitations](#invitations)
11. [Client Tracking](#client-tracking)
12. [API REST](#api-rest)
13. [Services](#services)
14. [Webhooks](#webhooks)
15. [Sécurité](#sécurité)

---

## 🏗️ Architecture

### Stack Technique
- **Framework**: Laravel 11.46.1
- **Base de données**: SQLite (dev) / PostgreSQL (prod)
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **Frontend**: Inertia.js + Vue 3
- **Styling**: Tailwind CSS
- **Activity Logs**: Spatie Laravel Activitylog

### Structure MVC
```
app/
├── Http/Controllers/          # Contrôleurs web et API
├── Models/                    # Modèles Eloquent
├── Policies/                  # Politiques d'autorisation
├── Services/                  # Services métier
├── Notifications/             # Notifications système
└── Providers/                 # Service providers
```

---

## 🔐 Authentification & Autorisation

### Système d'authentification
- **Provider**: Laravel Sanctum (session + token-based)
- **Routes**: `/login`, `/register`, `/logout`, `/forgot-password`, `/reset-password`
- **Vérification email**: Route `/verify-email/{id}/{hash}`
- **Protection CSRF**: Activée sur toutes les routes web

### Système de Permissions (RBAC)

#### 5 Rôles Définis
1. **SuperAdmin** - Accès complet au système
2. **Consultant** - Validation et approbation des dossiers
3. **Agent** - Gestion opérationnelle quotidienne
4. **Client** - Accès limité à ses propres dossiers
5. **Guarantor** - Rôle de garant (extensible)

#### 26 Permissions Granulaires

**User Management** (3)
- `manage users` - Gestion complète des utilisateurs
- `invite users` - Invitation de nouveaux utilisateurs
- `view users` - Consultation des utilisateurs

**Client Management** (4)
- `create clients` - Création de clients
- `edit clients` - Modification de clients
- `view clients` - Consultation des clients
- `delete clients` - Suppression de clients

**Dossier Management** (6)
- `create dossiers` - Création de dossiers
- `edit dossiers` - Modification de dossiers
- `view dossiers` - Consultation des dossiers
- `delete dossiers` - Suppression de dossiers
- `validate dossiers` - **Validation de dossiers** (Consultant)
- `approve dossiers` - **Approbation de dossiers** (Consultant)

**Document Management** (5)
- `upload documents` - Upload de documents
- `view documents` - Consultation de documents
- `edit documents` - Modification de documents
- `delete documents` - Suppression de documents
- `download documents` - Téléchargement de documents

**Contract Management** (4)
- `generate contracts` - Génération de contrats
- `send contracts` - Envoi de contrats
- `view contracts` - Consultation de contrats
- `sign contracts` - Signature de contrats

**Package Management** (1)
- `manage packages` - Gestion des forfaits voyage

**System Administration** (3)
- `view audit logs` - Consultation des logs d'audit
- `export data` - Export de données
- `manage settings` - Gestion des paramètres système

### Policies (Politiques d'autorisation)
- **DossierPolicy**: Contrôle d'accès aux dossiers (view, create, update, delete, validate, approve)
- **DocumentPolicy**: Contrôle d'accès aux documents (view, update, delete, download)

---

## 👥 Gestion des Utilisateurs

### Modèle User
**Table**: `users`

**Champs**:
- `id` - Identifiant unique
- `name` - Nom complet
- `email` - Email (unique, vérifié)
- `password` - Mot de passe hashé (bcrypt)
- `email_verified_at` - Date de vérification email
- `remember_token` - Token de session
- `created_at`, `updated_at` - Timestamps

**Relations**:
- `hasMany(Dossier)` - Dossiers créés
- `hasMany(Document)` - Documents uploadés
- `hasMany(Appointment)` - Rendez-vous
- `morphMany(Notification)` - Notifications
- `belongsToMany(Role)` - Rôles Spatie
- `belongsToMany(Permission)` - Permissions Spatie

### Routes Profil
```php
GET    /profile              # Afficher le profil
PUT    /profile              # Mettre à jour le profil
DELETE /profile              # Supprimer le compte
```

### Fonctionnalités
- ✅ Création de compte avec vérification email
- ✅ Connexion/déconnexion sécurisée
- ✅ Réinitialisation de mot de passe
- ✅ Gestion du profil (nom, email)
- ✅ Suppression de compte
- ✅ Attribution de rôles et permissions
- ✅ Système de tokens API (Sanctum)

---

## 📁 Gestion des Dossiers

### Modèle Dossier
**Table**: `dossiers`

**Champs**:
- `id` - Identifiant unique
- `reference` - Référence unique (auto-générée: DOS-YYYYMMDD-XXXX)
- `client_id` - ID du client (foreign key)
- `title` - Titre du dossier
- `notes` - Notes internes
- `status` - Statut (enum: draft, pending, in_progress, approved, rejected, completed, archived)
- `created_by` - ID de l'utilisateur créateur
- `created_at`, `updated_at` - Timestamps

**Relations**:
- `belongsTo(Client)` - Client associé
- `belongsTo(User, 'created_by')` - Créateur
- `hasMany(Document)` - Documents du dossier
- `morphMany(Activity)` - Logs d'activité

### Routes CRUD Complètes
```php
GET    /dossiers                        # Liste des dossiers (paginée, filtrable)
GET    /dossiers/create                 # Formulaire de création
POST   /dossiers                        # Créer un dossier
GET    /dossiers/{id}                   # Détail d'un dossier
GET    /dossiers/{id}/edit              # Formulaire d'édition
PUT    /dossiers/{id}                   # Mettre à jour un dossier
DELETE /dossiers/{id}                   # Supprimer un dossier
```

### Routes Actions Spéciales
```php
POST   /dossiers/{id}/validate          # Valider un dossier (Consultant)
POST   /dossiers/{id}/approve           # Approuver un dossier (Consultant)
POST   /dossiers/{id}/change-status     # Changer le statut
```

### Fonctionnalités DossierController

#### `index()` - Liste des dossiers
- ✅ Pagination (15 par page)
- ✅ Recherche full-text (référence, titre, nom client)
- ✅ Filtrage par statut
- ✅ Filtrage par rôle (clients voient uniquement leurs dossiers)
- ✅ Comptage des documents associés
- ✅ Flag `canCreate` pour affichage conditionnel du bouton

#### `create()` - Formulaire création
- ✅ Liste des clients disponibles
- ✅ Autorisation via Policy

#### `store()` - Création
- ✅ Validation des données (client_id, title, notes)
- ✅ Génération automatique de référence unique
- ✅ Transaction database
- ✅ Log d'activité
- ✅ Notification au client
- ✅ Redirection vers la vue détaillée

#### `show()` - Détail
- ✅ Chargement eager loading (client, documents, activities)
- ✅ Liste des documents avec uploader
- ✅ Timeline d'activités (20 dernières)
- ✅ Flags de permissions:
  - `canEdit` - Peut modifier
  - `canDelete` - Peut supprimer
  - `canValidate` - Peut valider (Consultant)
  - `canApprove` - Peut approuver (Consultant)
  - `canChangeStatus` - Peut changer le statut
  - `canUploadDocuments` - Peut uploader
  - `canDeleteDocuments` - Peut supprimer des documents

#### `edit()` - Formulaire édition
- ✅ Chargement du dossier avec relations
- ✅ Liste des clients
- ✅ Flag `canDelete`

#### `update()` - Mise à jour
- ✅ Validation partielle (client_id, title, notes)
- ✅ Log d'activité
- ✅ Message de succès

#### `destroy()` - Suppression
- ✅ Log d'activité avant suppression
- ✅ Soft delete (si configuré)
- ✅ Redirection vers la liste

#### `validate()` - Validation ⭐
- ✅ Autorisation via Policy (Consultant uniquement)
- ✅ Log d'activité
- ✅ Message de succès
- 🔜 TODO: Mise à jour du statut à 'validated'

#### `approve()` - Approbation ⭐
- ✅ Autorisation via Policy (Consultant uniquement)
- ✅ Log d'activité
- ✅ Message de succès
- 🔜 TODO: Mise à jour du statut à 'approved'

#### `changeStatus()` - Changement de statut
- ✅ Validation du statut (draft, pending, in_progress, approved, rejected, completed)
- ✅ Notification automatique au client
- ✅ Log d'activité
- ✅ Support de la locale (FR/EN)

### Statuts de Dossier
1. **draft** - Brouillon
2. **pending** - En attente
3. **in_progress** - En cours
4. **approved** - Approuvé
5. **rejected** - Rejeté
6. **completed** - Complété
7. **archived** - Archivé

---

## 📄 Gestion des Documents

### Modèle Document
**Table**: `documents`

**Champs**:
- `id` - Identifiant unique
- `dossier_id` - ID du dossier parent
- `name` - Nom du fichier
- `type` - Type de document (passport, visa, ticket, etc.)
- `file_path` - Chemin de stockage
- `mime_type` - Type MIME
- `size` - Taille en octets
- `uploaded_by` - ID de l'utilisateur
- `version` - Version du document
- `parent_id` - ID du document parent (versioning)
- `created_at`, `updated_at` - Timestamps

**Relations**:
- `belongsTo(Dossier)` - Dossier parent
- `belongsTo(User, 'uploaded_by')` - Uploader
- `hasMany(Document, 'parent_id')` - Versions
- `belongsTo(Document, 'parent_id')` - Version parent

### Routes
```php
# Nested sous dossiers
GET    /dossiers/{dossier}/documents          # Liste des documents d'un dossier
POST   /dossiers/{dossier}/documents          # Upload un document

# Routes directes
GET    /documents/{id}                        # Détail d'un document
PUT    /documents/{id}                        # Mettre à jour métadonnées
DELETE /documents/{id}                        # Supprimer un document
GET    /documents/{id}/download               # Télécharger un document
POST   /documents/{id}/version                # Créer une nouvelle version
```

### Fonctionnalités DocumentController

#### `index()` - Liste documents
- ✅ Filtrage par dossier
- ✅ Chargement de l'uploader
- ✅ Tri par date

#### `store()` - Upload
- ✅ Validation du fichier (max 10MB, types autorisés)
- ✅ Stockage sécurisé dans `storage/app/documents`
- ✅ Génération de nom unique
- ✅ Extraction des métadonnées (mime_type, size)
- ✅ Log d'activité
- ✅ Notification au client

#### `show()` - Détail
- ✅ Chargement avec relations
- ✅ Informations complètes
- ✅ Flags de permissions

#### `update()` - Mise à jour
- ✅ Modification du nom et type
- ✅ Log d'activité

#### `destroy()` - Suppression
- ✅ Suppression du fichier physique
- ✅ Suppression de l'enregistrement
- ✅ Log d'activité
- ✅ Autorisation via Policy

#### `download()` - Téléchargement
- ✅ Vérification d'existence
- ✅ Streaming sécurisé
- ✅ Headers appropriés (Content-Type, Content-Disposition)
- ✅ Log d'activité

#### `version()` - Versioning
- ✅ Création d'une nouvelle version
- ✅ Lien parent_id
- ✅ Incrémentation automatique du numéro de version
- ✅ Conservation de l'historique

### Types de Documents Supportés
- **passport** - Passeport
- **visa** - Visa
- **ticket** - Billet d'avion
- **insurance** - Assurance voyage
- **hotel_reservation** - Réservation hôtel
- **itinerary** - Itinéraire
- **medical_certificate** - Certificat médical
- **financial_proof** - Justificatif financier
- **contract** - Contrat signé
- **other** - Autre

### Stockage
- **Disk**: Local (`storage/app/documents`)
- **Visibilité**: Privée (authentification requise)
- **Structure**: `documents/{year}/{month}/{filename}`

---

## 📝 Système de Contrats

### Intégration DocuSeal

**Service**: `DocuSealService`

Le système utilise DocuSeal pour la génération et signature électronique de contrats.

### Routes
```php
GET    /dossiers/{dossier}/contracts/create           # Formulaire de création
POST   /dossiers/{dossier}/contracts/generate         # Générer un contrat
GET    /dossiers/{dossier}/contracts/{doc}/download   # Télécharger le contrat
POST   /contracts/preview                             # Prévisualiser un contrat
```

### Fonctionnalités ContractController

#### `create()` - Formulaire
- ✅ Chargement du dossier et client
- ✅ Liste des templates disponibles
- ✅ Variables de personnalisation

#### `generate()` - Génération
- ✅ Validation des données (template, client_name, etc.)
- ✅ Appel API DocuSeal
- ✅ Création d'une submission
- ✅ Stockage du contrat comme document
- ✅ Email automatique au client
- ✅ Logs d'activité

#### `download()` - Téléchargement
- ✅ Vérification que c'est un contrat
- ✅ Streaming sécurisé

#### `preview()` - Aperçu
- ✅ Génération PDF temporaire
- ✅ Variables dynamiques

### Service DocuSealService

**Méthodes**:
```php
createSubmission(array $data)           # Créer une demande de signature
getSubmission(string $submissionId)     # Récupérer une submission
downloadDocument(string $submissionId)  # Télécharger le PDF signé
```

**Configuration**: `.env`
```env
DOCUSEAL_API_KEY=your_api_key
DOCUSEAL_API_URL=https://api.docuseal.co
```

### Templates de Contrats
- Contrat de prestation de services
- Conditions générales de vente
- Accord de traitement de données
- Formulaire de consentement

### Workflow de Signature
1. Agent génère le contrat depuis un dossier
2. Système envoie email au client avec lien
3. Client signe électroniquement via DocuSeal
4. Webhook reçoit la confirmation
5. Document signé stocké dans le dossier
6. Notification envoyée à l'agent

---

## 🔔 Notifications

### Système de Notifications Laravel

**Table**: `notifications`

**Champs**:
- `id` - UUID
- `type` - Classe de notification
- `notifiable_type`, `notifiable_id` - Polymorphic relation
- `data` - JSON contenant titre, message, action_url
- `read_at` - Date de lecture (nullable)
- `created_at`, `updated_at` - Timestamps

### Routes
```php
GET    /notifications                    # Liste paginée (API)
GET    /notifications/page               # Page Inertia
GET    /notifications/unread-count       # Compteur non lues
POST   /notifications/{id}/read          # Marquer comme lue
POST   /notifications/read-all           # Tout marquer comme lu
DELETE /notifications/{id}               # Supprimer une notification
```

### Fonctionnalités NotificationController

#### `index()` - Liste API
- ✅ Pagination (20 par page)
- ✅ Filtrage par statut (read, unread, all)
- ✅ Tri chronologique inverse
- ✅ Format JSON

#### `page()` - Page Inertia
- ✅ Bootstrap stats (unread count, total)
- ✅ Initial notifications (20 premières)
- ✅ Flag `hasMore` pour pagination
- ✅ Rendu Vue SSR-like

#### `unreadCount()` - Compteur
- ✅ Retour rapide du nombre de non lues
- ✅ Utilisé pour le badge

#### `markAsRead()` - Marquer comme lue
- ✅ Mise à jour de `read_at`
- ✅ Retour du compteur mis à jour

#### `markAllAsRead()` - Tout marquer
- ✅ Bulk update sur toutes les notifications
- ✅ Performance optimisée

#### `destroy()` - Suppression
- ✅ Suppression individuelle
- ✅ Autorisation (propriétaire uniquement)

### Types de Notifications

**Implémentées**:
1. **DossierCreatedNotification** - Nouveau dossier créé
2. **DossierStatusChangedNotification** - Changement de statut
3. **DocumentUploadedNotification** - Document uploadé
4. **GenericInfoNotification** - Notification générique (demo)

**Structure JSON data**:
```json
{
  "title": "Titre de la notification",
  "message": "Message détaillé",
  "action_url": "/dossiers/123",
  "action_text": "Voir le dossier",
  "type": "info|success|warning|error"
}
```

### NotificationSeeder (Demo)
- ✅ Génère 5 notifications de test
- ✅ Pour l'utilisateur admin@eli-voyages.com
- ✅ Titres et messages variés
- ✅ Liens d'action fonctionnels

### Service NotificationService

**Méthodes**:
```php
notifyDossierCreated(Dossier $dossier, User $user)
notifyDossierStatusChanged(Dossier $dossier, string $oldStatus)
notifyDocumentUploaded(Document $document, User $user)
sendCustomNotification(User $user, array $data)
```

### Canaux de Notification
- ✅ **Database** - Stockage en base (actif)
- 🔜 **Email** - Envoi par email (configurable)
- 🔜 **SMS** - Via Twilio (planifié)
- 🔜 **WhatsApp** - Via WhatsApp Business API (planifié)

### Intégration Frontend
- ✅ Badge dynamique dans AppLayout (🔔 avec compteur rouge)
- ✅ Partage Inertia global (`unreadNotifications`)
- ✅ Page dédiée avec filtres et pagination
- ✅ Marquage temps réel

---

## 📅 Rendez-vous

### Modèle Appointment
**Table**: `appointments`

**Champs**:
- `id` - Identifiant unique
- `client_id` - ID du client
- `agent_id` - ID de l'agent assigné
- `dossier_id` - ID du dossier (nullable)
- `type` - Type de RDV (consultation, document_review, signing, follow_up)
- `scheduled_at` - Date/heure du RDV
- `duration_minutes` - Durée en minutes
- `status` - Statut (scheduled, confirmed, completed, cancelled, no_show)
- `location` - Lieu physique (nullable)
- `meeting_link` - Lien visio (nullable)
- `client_notes` - Notes du client
- `agent_notes` - Notes de l'agent
- `created_at`, `updated_at` - Timestamps

**Relations**:
- `belongsTo(User, 'client_id')` - Client
- `belongsTo(User, 'agent_id')` - Agent
- `belongsTo(Dossier)` - Dossier associé (optionnel)

### Routes
```php
GET    /appointments                      # Page principale (Inertia)
GET    /appointments/data                 # Liste des RDV (API)
GET    /appointments/slots                # Créneaux disponibles (API)
GET    /appointments/agents               # Liste des agents (API)
POST   /appointments                      # Créer un RDV
PUT    /appointments/{id}                 # Modifier un RDV
POST   /appointments/{id}/confirm         # Confirmer un RDV
POST   /appointments/{id}/cancel          # Annuler un RDV
DELETE /appointments/{id}                 # Supprimer un RDV
```

### Fonctionnalités AppointmentController

#### `index()` - Page principale
- ✅ Vue calendrier interactive
- ✅ Flag `isAgent` pour permissions
- ✅ Rendu Inertia

#### `getAppointments()` - Liste API
- ✅ Filtrage par date (start, end)
- ✅ Filtrage par agent
- ✅ Filtrage par statut
- ✅ Chargement des relations (client, agent, dossier)
- ✅ Format calendrier

#### `getAvailableSlots()` - Créneaux
- ✅ Calcul des disponibilités
- ✅ Par agent et par date
- ✅ Respect des horaires d'ouverture (9h-18h)
- ✅ Exclusion des créneaux réservés
- ✅ Durée configurable (30, 60, 90 min)

#### `getAgents()` - Liste agents
- ✅ Utilisateurs avec rôle Agent ou Admin
- ✅ Format select (id, name)

#### `store()` - Création
- ✅ Validation complète (agent_id, scheduled_at, duration, type)
- ✅ Vérification de disponibilité
- ✅ Assignation automatique du client
- ✅ Notification à l'agent
- ✅ Email de confirmation

#### `update()` - Modification
- ✅ Autorisation (propriétaire ou agent)
- ✅ Mise à jour partielle
- ✅ Notification des changements

#### `confirm()` - Confirmation
- ✅ Changement de statut à 'confirmed'
- ✅ Email de confirmation
- ✅ Accessible par agent uniquement

#### `cancel()` - Annulation
- ✅ Changement de statut à 'cancelled'
- ✅ Note d'annulation optionnelle
- ✅ Email de notification
- ✅ Log d'activité

#### `destroy()` - Suppression
- ✅ Suppression définitive
- ✅ Autorisation stricte

### Types de Rendez-vous
1. **consultation** - Consultation initiale (💬)
2. **document_review** - Révision de documents (📄)
3. **signing** - Signature de contrats (✍️)
4. **follow_up** - Suivi de dossier (📋)

### Statuts
- **scheduled** - Planifié (bleu)
- **confirmed** - Confirmé (vert)
- **completed** - Terminé (gris)
- **cancelled** - Annulé (rouge)
- **no_show** - Absent (jaune)

### Règles de Disponibilité
- Horaires: Lundi-Vendredi 9h-18h
- Durées: 30, 60, 90 minutes
- Pas de chevauchement d'agents
- Buffer de 15 min entre RDV

---

## 📊 Analytics

### Fonctionnalités AnalyticsController

Le système analytics fournit des métriques détaillées sur les performances de la plateforme.

### Routes
```php
GET    /analytics              # Données analytics (API)
GET    /analytics/page         # Page analytics (Inertia)
```

#### `index()` - Données Analytics
**Paramètre**: `period` (7days, 30days, 12months)

**Métriques retournées**:

1. **Conversion Metrics** (Métriques de conversion)
   - Taux de conversion global
   - Nombre de conversions
   - Tendance (hausse/baisse)

2. **Approval Metrics** (Métriques d'approbation)
   - Taux d'approbation
   - Nombre approuvés vs rejetés
   - Délai moyen d'approbation

3. **Document Metrics** (Métriques documents)
   - Total documents uploadés
   - Par type de document
   - Taille moyenne
   - Documents par dossier

4. **User Activity** (Activité utilisateurs)
   - Utilisateurs actifs
   - Par rôle
   - Taux d'engagement

5. **Time Metrics** (Métriques temporelles)
   - Temps moyen de traitement
   - Temps par statut
   - Goulets d'étranglement

6. **Dossier Statistics** (Statistiques dossiers)
   - Total dossiers
   - Par statut
   - Évolution temporelle
   - Top agents

7. **Revenue Metrics** (Métriques revenus) 🔜
   - Chiffre d'affaires
   - Par package
   - Prévisions

### Format de Réponse
```json
{
  "conversion_metrics": {
    "conversion_rate": 75.5,
    "conversions": 45,
    "trend": "up"
  },
  "approval_metrics": {
    "approval_rate": 89.2,
    "approved": 80,
    "rejected": 10,
    "avg_time_days": 3.5
  },
  "document_metrics": {
    "total": 450,
    "by_type": {...},
    "avg_size_mb": 2.3
  },
  "dossier_stats": {
    "total": 120,
    "by_status": {...},
    "chart_data": [...]
  }
}
```

### Throttling
- Limite: 100 requêtes par minute
- Protection contre la surcharge

### Visualisations Frontend
- Graphiques en courbes (tendances)
- Graphiques en barres (comparaisons)
- KPI cards avec icônes
- Filtres de période
- Export CSV/PDF 🔜

---

## 📨 Invitations

### Modèle Invitation
**Table**: `invitations`

**Champs**:
- `id` - Identifiant unique
- `email` - Email de l'invité
- `token` - Token unique (UUID)
- `role` - Rôle à assigner
- `invited_by` - ID de l'inviteur
- `expires_at` - Date d'expiration (7 jours)
- `accepted_at` - Date d'acceptation
- `created_at`, `updated_at` - Timestamps

**Relations**:
- `belongsTo(User, 'invited_by')` - Inviteur

### Routes
```php
# Public (sans auth)
GET    /invitations/{token}                # Afficher l'invitation
POST   /invitations/{token}/accept         # Accepter l'invitation

# Protégées (avec permission 'invite users')
GET    /invitations                        # Liste des invitations
POST   /invitations                        # Créer une invitation
DELETE /invitations/{id}                   # Supprimer une invitation
POST   /invitations/{id}/resend            # Renvoyer une invitation
```

### Fonctionnalités InvitationController

#### `index()` - Liste
- ✅ Toutes les invitations (actives, expirées, acceptées)
- ✅ Chargement de l'inviteur
- ✅ Tri chronologique
- ✅ Restriction par permission

#### `store()` - Création
- ✅ Validation (email unique, rôle valide)
- ✅ Génération token UUID
- ✅ Expiration 7 jours
- ✅ Email automatique avec lien
- ✅ Log d'activité

#### `show()` - Affichage public
- ✅ Vérification du token
- ✅ Vérification d'expiration
- ✅ Affichage des infos (email, rôle, inviteur)
- ✅ Page spéciale si expirée

#### `accept()` - Acceptation
- ✅ Vérification token et expiration
- ✅ Création du compte utilisateur
- ✅ Assignation du rôle
- ✅ Marquage acceptée
- ✅ Connexion automatique
- ✅ Redirection vers dashboard

#### `resend()` - Renvoi
- ✅ Génération nouveau token
- ✅ Extension expiration (+7 jours)
- ✅ Nouvel email
- ✅ Autorisation inviteur uniquement

#### `destroy()` - Suppression
- ✅ Annulation d'invitation
- ✅ Autorisation inviteur uniquement

### Workflow Invitation
1. Agent avec permission crée une invitation
2. Email envoyé avec lien unique
3. Invité clique sur le lien
4. Formulaire d'inscription pré-rempli
5. Acceptation → compte créé + rôle assigné
6. Connexion automatique

### Sécurité
- Token UUID aléatoire
- Expiration obligatoire (7 jours)
- Vérification d'unicité email
- Email verification après création compte
- Rate limiting sur acceptation

---

## 🔍 Client Tracking

### Tableau de bord client personnalisé

**Routes**:
```php
GET    /client-tracking              # Dashboard client
GET    /client-tracking/{dossier}    # Détail dossier client
```

### Fonctionnalités ClientTrackingController

#### `index()` - Dashboard
- ✅ Vue spéciale pour rôle Client
- ✅ Liste des dossiers personnels
- ✅ Statuts et progression
- ✅ Documents téléchargeables
- ✅ Prochains rendez-vous
- ✅ Notifications importantes

#### `show()` - Détail dossier
- ✅ Vérification propriétaire
- ✅ Timeline complète
- ✅ Documents accessibles
- ✅ Historique des actions
- ✅ Messages de l'agent

### Vue Client
- Interface simplifiée (pas de fonctions admin)
- Lecture seule (pas d'édition)
- Focus sur l'information et le suivi
- Actions limitées: download, signature, prise RDV

---

## 🔌 API REST

### Endpoints API (Sanctum Protected)

**Base URL**: `/api`

**Authentication**: Bearer Token (Sanctum)

### Users API
```php
GET    /api/users              # Liste utilisateurs
GET    /api/users/{id}         # Détail utilisateur
POST   /api/users              # Créer utilisateur
PUT    /api/users/{id}         # Modifier utilisateur
DELETE /api/users/{id}         # Supprimer utilisateur
```

### Clients API
```php
GET    /api/clients            # Liste clients
GET    /api/clients/{id}       # Détail client
POST   /api/clients            # Créer client
PUT    /api/clients/{id}       # Modifier client
DELETE /api/clients/{id}       # Supprimer client
```

### Packages API
```php
GET    /api/packages           # Liste packages
GET    /api/packages/{id}      # Détail package
POST   /api/packages           # Créer package
PUT    /api/packages/{id}      # Modifier package
DELETE /api/packages/{id}      # Supprimer package
```

### Dossiers API
```php
GET    /api/dossiers                          # Liste dossiers
GET    /api/dossiers/{id}                     # Détail dossier
POST   /api/dossiers                          # Créer dossier
PUT    /api/dossiers/{id}                     # Modifier dossier
DELETE /api/dossiers/{id}                     # Supprimer dossier
POST   /api/dossiers/{id}/documents           # Upload document
GET    /api/dossiers/{id}/documents/{doc}    # Download document
```

### Format Réponse
```json
{
  "success": true,
  "data": {...},
  "message": "Operation successful",
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

### Rate Limiting
- Authentifié: 100 req/min
- Non authentifié: 10 req/min
- Search: 100 req/min
- Analytics: 100 req/min

---

## 🎣 Webhooks

### DocuSeal Webhook

**Route**: `POST /api/webhooks/docuseal`

**Événements écoutés**:
- `submission.completed` - Contrat signé
- `submission.viewed` - Contrat consulté
- `submission.sent` - Contrat envoyé

### Fonctionnalités WebhookController

#### `docuseal()` - Handler DocuSeal
- ✅ Vérification signature HMAC
- ✅ Traitement événement
- ✅ Mise à jour document
- ✅ Notification agent
- ✅ Log d'activité
- ✅ Response 200 OK

### Sécurité Webhooks
- Vérification signature
- Rate limiting (60 req/min)
- IP whitelisting (configurable)
- Logging complet
- Retry logic côté émetteur

---

## 🛡️ Sécurité

### Mesures de Sécurité Implémentées

#### 1. Authentication & Authorization
- ✅ Laravel Sanctum (sessions + tokens)
- ✅ RBAC avec Spatie Permissions
- ✅ Policies granulaires
- ✅ Email verification obligatoire
- ✅ Password hashing (bcrypt)
- ✅ Remember me tokens sécurisés

#### 2. Protection CSRF
- ✅ Tokens CSRF sur toutes les routes web
- ✅ Vérification automatique Laravel
- ✅ Expiration après 2h d'inactivité

#### 3. Headers de Sécurité
```php
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000
Content-Security-Policy: default-src 'self'
```

#### 4. Rate Limiting
- Login: 5 tentatives / minute
- API: 100 requêtes / minute
- Search: 100 requêtes / minute
- Webhooks: 60 requêtes / minute

#### 5. Validation des Entrées
- ✅ Validation Laravel Request
- ✅ Sanitization automatique
- ✅ Type checking strict
- ✅ Max upload size: 10MB

#### 6. Stockage Sécurisé
- ✅ Documents en storage privé
- ✅ Accès via controller (authentification)
- ✅ Pas d'accès direct filesystem
- ✅ Noms de fichiers hashés

#### 7. Logging & Monitoring
- ✅ Activity logs (Spatie)
- ✅ Laravel logs (storage/logs)
- ✅ Error tracking
- ✅ Audit trail complet

#### 8. Database Security
- ✅ Prepared statements (PDO)
- ✅ Query bindings
- ✅ Mass assignment protection
- ✅ Soft deletes

#### 9. API Security
- ✅ Bearer token authentication
- ✅ CORS configuré
- ✅ Rate limiting
- ✅ Input validation
- ✅ Response sanitization

#### 10. Environnement
- ✅ `.env` hors du repository
- ✅ Secrets dans variables d'environnement
- ✅ Debug mode OFF en production
- ✅ Logs sensibles masqués

### Configuration CORS
```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['Content-Type', 'Authorization'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

---

## 🧰 Services

### 1. DocumentService
**Responsabilités**:
- Upload sécurisé
- Génération noms uniques
- Extraction métadonnées
- Stockage organisé
- Versioning
- Suppression propre

### 2. ContractService
**Responsabilités**:
- Génération contrats
- Templates management
- Variables substitution
- PDF generation
- Envoi pour signature

### 3. ContractGenerationService
**Responsabilités**:
- Rendu HTML avec variables
- Conversion HTML → PDF
- Branding (logo, couleurs)
- Layouts professionnels

### 4. DocuSealService
**Responsabilités**:
- API calls DocuSeal
- Submission management
- Document retrieval
- Status tracking
- Error handling

### 5. NotificationService
**Responsabilités**:
- Envoi notifications
- Multiple canaux (DB, Email, SMS)
- Queue management
- Template rendering
- Locale handling

### 6. WhatsAppService
**Responsabilités**:
- Envoi messages WhatsApp Business
- Templates management
- Media messages
- Status callbacks
- Rate limiting

### 7. BrandingConfig
**Responsabilités**:
- Configuration branding
- Logo path
- Couleurs corporate
- Coordonnées entreprise
- Réseaux sociaux

---

## 📦 Modèles Supplémentaires

### Client Model
**Table**: `clients`

**Champs**:
- `id`, `name`, `email`, `phone`
- `address`, `city`, `country`
- `passport_number`, `passport_expiry`
- `created_at`, `updated_at`

**Relations**:
- `hasMany(Dossier)` - Dossiers du client

### Package Model
**Table**: `packages`

**Champs**:
- `id`, `name`, `description`
- `destination`, `duration_days`
- `price`, `currency`
- `includes` (JSON)
- `is_active`
- `created_at`, `updated_at`

**Relations**:
- `hasMany(Dossier)` - Dossiers utilisant ce package

---

## 🔄 Activity Logging

### Spatie Laravel Activitylog

**Table**: `activity_log`

**Événements Loggés**:
- ✅ Création de dossier
- ✅ Modification de dossier
- ✅ Suppression de dossier
- ✅ Validation de dossier
- ✅ Approbation de dossier
- ✅ Upload de document
- ✅ Téléchargement de document
- ✅ Suppression de document
- ✅ Création d'invitation
- ✅ Acceptation d'invitation
- ✅ Création de rendez-vous
- ✅ Annulation de rendez-vous

**Informations Capturées**:
- Utilisateur effectuant l'action
- Type d'action (created, updated, deleted, etc.)
- Modèle affecté
- Propriétés avant/après (pour updates)
- Timestamp
- Données contextuelles

### Utilisation
```php
activity()
    ->performedOn($dossier)
    ->causedBy(auth()->user())
    ->withProperties(['reference' => $dossier->reference])
    ->log('Dossier created');
```

---

## 🚦 Middleware Stack

### Global Middleware
1. **TrustProxies** - Proxy headers
2. **PreventRequestsDuringMaintenance** - Mode maintenance
3. **ValidatePostSize** - Limite taille POST
4. **TrimStrings** - Trim inputs
5. **ConvertEmptyStringsToNull** - Normalisation

### Web Middleware
1. **EncryptCookies** - Encryption cookies
2. **AddQueuedCookiesToResponse** - Queue cookies
3. **StartSession** - Sessions
4. **ShareErrorsFromSession** - Flash errors
5. **VerifyCsrfToken** - CSRF protection
6. **SubstituteBindings** - Route model binding
7. **HandleInertiaRequests** - Inertia.js

### API Middleware
1. **Throttle:60,1** - Rate limiting
2. **SubstituteBindings** - Route model binding

### Custom Middleware
- **SecurityHeaders** - Headers de sécurité
- **Localization** - Détection langue
- **ActivityLogger** - Logging automatique

---

## 📈 Performance & Optimization

### Database
- ✅ Indexes sur foreign keys
- ✅ Eager loading pour éviter N+1
- ✅ Query optimization
- ✅ Pagination (15-20 items)

### Caching
- ✅ Config cache
- ✅ Route cache
- ✅ View cache
- 🔜 Query result cache (Redis)

### Queue System
- ✅ Emails en queue
- ✅ Notifications en queue
- 🔜 Document processing en queue

### Frontend Optimization
- ✅ Vite build optimization
- ✅ CSS purge (Tailwind)
- ✅ Asset versioning
- ✅ Lazy loading components

---

## 🧪 Testing

### Tests Unitaires
```bash
php artisan test
```

**Coverage**:
- Models relationships
- Services logic
- Policies authorization
- Helpers functions

### Tests Feature
**Coverage**:
- Routes responses
- CRUD operations
- Authentication flow
- Permissions checks
- File uploads

### Tests Browser (Dusk)
🔜 En développement

---

## 🔧 Configuration

### Fichiers de Configuration

#### `config/app.php`
- Timezone: Africa/Abidjan
- Locale: fr
- Fallback locale: en
- Providers & Aliases

#### `config/auth.php`
- Guards: web (session), sanctum (token)
- Providers: users (eloquent)
- Passwords: resets table

#### `config/filesystems.php`
- Default: local
- Disks: local, public, s3
- Document storage: `storage/app/documents`

#### `config/mail.php`
- Mailer: smtp
- From: noreply@eli-voyages.com
- Queue: true

#### `config/services.php`
- DocuSeal API
- WhatsApp Business API
- AWS S3 (optionnel)

#### `config/permission.php`
- Spatie Permission configuration
- Models: Role, Permission
- Table names
- Cache settings

---

## 📚 Migrations Database

### Tables Principales (22 tables)

1. **users** - Utilisateurs système
2. **password_reset_tokens** - Tokens reset password
3. **sessions** - Sessions utilisateurs
4. **personal_access_tokens** - Tokens Sanctum
5. **roles** - Rôles Spatie
6. **permissions** - Permissions Spatie
7. **role_has_permissions** - Pivot rôles-permissions
8. **model_has_roles** - Pivot modèles-rôles
9. **model_has_permissions** - Pivot modèles-permissions
10. **clients** - Clients
11. **packages** - Forfaits voyage
12. **dossiers** - Dossiers clients
13. **documents** - Documents
14. **appointments** - Rendez-vous
15. **invitations** - Invitations système
16. **notifications** - Notifications Laravel
17. **activity_log** - Logs d'activité Spatie
18. **failed_jobs** - Jobs échoués
19. **jobs** - Queue jobs
20. **cache** - Cache table
21. **cache_locks** - Cache locks
22. **job_batches** - Batch jobs

### Seeders

1. **RolesAndPermissionsSeeder** - Rôles et permissions
2. **AdminUserSeeder** - Utilisateurs admin/test
3. **ClientSeeder** - Clients de démo
4. **PackageSeeder** - Packages de démo
5. **NotificationSeeder** - Notifications de démo
6. **DatabaseSeeder** - Seeder principal

---

## 📖 Documentation API

### Authentification

**Login**
```http
POST /login
Content-Type: application/json

{
  "email": "admin@eli-voyages.com",
  "password": "password"
}
```

**Response**
```json
{
  "user": {...},
  "token": "1|abc123..."
}
```

### Exemple Requête API

**Get Dossiers**
```http
GET /api/dossiers
Authorization: Bearer 1|abc123...
Accept: application/json
```

**Response**
```json
{
  "data": [
    {
      "id": 1,
      "reference": "DOS-20251108-0001",
      "title": "Voyage Paris",
      "status": "in_progress",
      "client": {...},
      "documents_count": 5,
      "created_at": "2025-11-08T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  }
}
```

---

## 🎯 Roadmap & TODO

### Court Terme
- [ ] Implémenter changement statut dans validate/approve
- [ ] Ajouter filtres avancés sur dossiers
- [ ] Export CSV des dossiers
- [ ] Statistiques temps réel (dashboard)

### Moyen Terme
- [ ] Système de messagerie interne
- [ ] Templates emails personnalisables
- [ ] Intégration paiement (Stripe/PayPal)
- [ ] Signature électronique avancée
- [ ] Multi-langue complet (EN, FR, ES)

### Long Terme
- [ ] Application mobile (React Native)
- [ ] Intégration CRM externe
- [ ] IA pour suggestions documents
- [ ] Chatbot support client
- [ ] Rapports personnalisés

---

## 📞 Support & Contact

**Équipe Technique**
- Email: dev@eli-voyages.com
- Slack: #eli-connect-support

**Documentation**
- Wiki interne: wiki.eli-voyages.com
- API Docs: api.eli-voyages.com/docs
- GitHub: github.com/pacmeazih/eli_voyages_connect

---

## 📝 Changelog

### Version 1.0.0 (8 novembre 2025)
- ✅ Release initiale
- ✅ Système complet CRUD dossiers
- ✅ Gestion documents avec versioning
- ✅ Système permissions RBAC
- ✅ Notifications temps réel
- ✅ Rendez-vous avec calendrier
- ✅ Analytics avancés
- ✅ Intégration DocuSeal
- ✅ API REST complète
- ✅ Interface Inertia.js + Vue 3

---

*Document généré le 8 novembre 2025*  
*ELI Voyages Connect - Plateforme de Gestion de Dossiers Clients*
