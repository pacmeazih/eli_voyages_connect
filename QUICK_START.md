# 🚀 Quick Start Guide - ELI VOYAGES

## Démarrage Rapide en 5 Minutes

### 1️⃣ Installation
```bash
# Dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate --force
php artisan db:seed

# Assets
npm run build
```

### 2️⃣ Lancer l'Application
```bash
# Serveur
php artisan serve

# Assets (mode dev)
npm run dev
```

### 3️⃣ Accès Demo
**Client:**
- URL: http://localhost:8000/login
- Email: `client@example.com`
- Password: `client123`
- Voir: "Mon dossier" (singulier)

**Admin:**
- URL: http://localhost:8000/login
- Email: `admin@example.com`
- Password: `admin123`
- Voir: "Dossiers" (pluriel), "Invitations"

---

## 📋 Fonctionnalités Principales

### ✅ Inviter un Client
1. Login admin → Menu "Invitations"
2. Cliquer "Nouvelle invitation"
3. Remplir: Nom, Prénom, Email, Téléphone
4. Submit → Code auto-généré (EV-2025-0001)
5. Email envoyé automatiquement

### ✅ Accepter Invitation (Client)
1. Ouvrir lien email
2. Code client affiché (EV-2025-XXXX)
3. Choisir civilité + mot de passe
4. Accepter CGU → Submit
5. Auto-login → Dashboard client

### ✅ Upload Document (Client)
1. "Mon dossier" → Section Documents
2. "Ajouter un document"
3. Choisir type (Passeport, Diplôme, etc.)
4. Drag-drop ou cliquer fichier
5. Description optionnelle → Téléverser

### ✅ Approuver/Rejeter Document (Staff)
1. Voir document avec status "En attente"
2. Bouton vert "Approuver" → Confirmer
   OU
   Bouton rouge "Rejeter" → Saisir raison
3. Client notifié

### ✅ Voir Progression (Client)
1. "Mon dossier" → Haut de page
2. Tracker visuel avec 5 étapes
3. Étape active avec bouton action
4. Statistiques Complété/En cours/À venir

---

## 🎨 Interface

### Menu Vertical (Sidebar)
**Client voit:**
- Dashboard
- Mon dossier (→ /dossiers/{id})
- Documents
- Notifications
- Settings

**Staff voit:**
- Dashboard
- Dossiers (→ /dossiers - tous)
- Documents
- Contracts
- Invitations
- Analytics
- Settings

### Mobile
- Sidebar caché
- Hamburger menu ☰ en haut à gauche
- Cliquer → Sidebar slide
- Overlay gris pour fermer

---

## 🔧 Commandes Utiles

### Cache
```bash
php artisan optimize:clear
```

### Logs
```bash
tail -f storage/logs/laravel.log
```

### Migration Reset
```bash
php artisan migrate:fresh --seed
```

### Test Invitation
```bash
php artisan tinker
>>> ClientInvitation::create(['nom' => 'Test', 'prenom' => 'User', 'email' => 'test@test.com', 'telephone' => '0612345678'])
```

---

## 📚 Documentation

- **IMPLEMENTATION_COMPLETE.md** - Résumé complet
- **TESTING_GUIDE.md** - Guide de test détaillé (8 sections)
- **REFACTORING_SUMMARY.md** - Liste des modifications

---

## 🆘 Problèmes Fréquents

**Sidebar ne s'affiche pas?**
```bash
npm run build
# Puis Ctrl+Shift+R dans browser
```

**"Mon dossier" 404?**
```sql
UPDATE users SET client_id = 1 WHERE email = 'client@example.com';
```

**Email non envoyé?**
```bash
# Vérifier .env MAIL_*
php artisan config:cache
php artisan queue:work
```

**Client code vide?**
```php
// Vérifier ClientInvitation::boot() method
// Tester manuellement génération
```

---

## ✨ Nouveautés Version 2.0

✅ Menu vertical à gauche (remplace horizontal)  
✅ Logo branding (Eli-Voyages icon + logo)  
✅ Client ID auto-généré (EV-YYYY-XXXX)  
✅ Invitations email avec code unique  
✅ Upload documents par type (12 types)  
✅ Approbation/Rejet avec raison  
✅ Tracker de progression visuel  
✅ Ordre signature Consultant → Client  
✅ Dark mode complet  
✅ Mobile responsive  

---

**Tout est prêt! Bon développement! 🎉**
