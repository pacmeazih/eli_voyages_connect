# 🌍 ELI Voyages Connect - Plateforme Client

Plateforme de gestion des dossiers d'immigration pour ELI Voyages SARL U.

## 🚀 Fonctionnalités

### ✅ Gestion des Dossiers
- Création et suivi de dossiers clients
- Gestion des statuts (brouillon, en cours, approuvé, terminé)
- Assignation aux agents
- Notes et historique complet

### ✅ Génération de Contrats (27 modèles)
- **Français**: 15 types de contrats d'immigration
- **Anglais**: 12 versions anglaises
- Génération automatique avec données client
- Logo et charte graphique ELI-VOYAGES
- Format professionnel .docx téléchargeable

### ✅ Gestion Documentaire
- Upload de documents (passeports, diplômes, relevés, etc.)
- Versionnement des documents
- Organisation par dossier
- Types de documents personnalisables

### ✅ Système d'Invitations
- Invitation de clients par email
- Création de comptes sécurisés via lien unique
- Gestion des rôles (SuperAdmin, Admin, Agent, Client)
- Expiration automatique des invitations

### ✅ Types de Services (12 catégories bilingues)
1. 📚 Permis d'études / Study Permit
2. 💼 Permis de travail / Work Permit
3. ✈️ Visa visiteur / Visitor Visa
4. 👨‍👩‍👧‍👦 Super Visa
5. 💑 Parrainage familial / Family Sponsorship
6. 🇨🇦 Citoyenneté / Citizenship
7. 🎫 AVE / eTA
8. 🇵🇪 CSQ Québec
9. 📋 LMIA
10. 🔄 Restauration de statut / Status Restoration
11. 🆘 Demande d'asile / Asylum Application
12. 📄 Services de traduction / Translation Services

### ✅ Système Bilingue
- Interface disponible en français et anglais
- Changement de langue en un clic
- Tous les contenus traduits

### ✅ Activity Logs
- Suivi de toutes les actions
- Historique des modifications
- Traçabilité complète

### ✅ Intégration DocuSeal (prête)
- Signatures électroniques
- Envoi automatique pour signature
- Webhooks pour notifications en temps réel

## 🔐 Identifiants de Test

```
SuperAdmin:
Email: admin@eli-voyages.com
Mot de passe: password

Admin (votre compte):
Email: koffi@eli-voyages.com
Mot de passe: password123

Agent:
Email: agent@eli-voyages.com
Mot de passe: agent123

Client:
Email: client@example.com
Mot de passe: client123
```

⚠️ **Ces mots de passe sont temporaires - changez-les en production!**

## 📦 Données de Démonstration

La base de données contient:
- **5 utilisateurs** (1 SuperAdmin, 1 Admin, 2 Agents, 1 Client)
- **5 clients** africains avec profils complets
- **8 packages** d'immigration (800K - 3M FCFA)
- **12 types de services** bilingues
- **6 dossiers** avec différents statuts
- **17 documents** répartis dans les dossiers

## 🌐 URLs de Production

- **Plateforme**: https://clients.elivoyages.com
- **Email notifications**: no-reply@elivoyages.com

## 📖 Comment Utiliser

### Pour les Administrateurs

1. **Se connecter** avec vos identifiants admin
2. **Inviter un client**:
   - Allez dans **Invitations** > **Nouvelle invitation**
   - Entrez l'email et sélectionnez "Client"
   - Le client recevra un email pour créer son compte
3. **Créer un dossier**:
   - Allez dans **Dossiers** > **Nouveau dossier**
   - Sélectionnez le client et le package
   - Assignez à un agent
4. **Générer un contrat**:
   - Ouvrez un dossier
   - Cliquez sur **Générer un contrat**
   - Sélectionnez le type de contrat
   - Téléchargez le document .docx généré

### Pour les Clients

1. **Créer un compte** via le lien d'invitation reçu par email
2. **Se connecter** à https://clients.elivoyages.com
3. **Voir vos dossiers** dans le tableau de bord
4. **Télécharger vos documents** et contrats
5. **Suivre l'avancement** de vos demandes

## 🛠️ Technologies

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Vue.js 3, Inertia.js, Tailwind CSS
- **Base de données**: SQLite (dev) / PostgreSQL (production)
- **Documents**: PHPWord pour génération de contrats
- **Emails**: SMTP via cPanel
- **Signatures**: DocuSeal API

## 📞 Support

Pour toute question ou assistance:
- 📧 Email: contact@elivoyages.com
- 📱 Téléphone: [Votre numéro]
- 🌐 Site web: https://elivoyages.com

---

**ELI Voyages SARL U** - Votre partenaire immigration
