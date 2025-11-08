# 🎭 Accès Démo - ELI Voyages Connect

## 🌐 URL de l'application
**Serveur local** : http://127.0.0.1:8000

---

## 👥 Comptes de Démonstration

### 🔴 SuperAdmin - Accès Complet
- **Email** : `admin@eli-voyages.com`
- **Mot de passe** : `password`
- **Permissions** : Toutes les permissions
- **Utilisation** : Administration complète du système

---

### 🟠 Admin - Gestion principale
- **Email** : `koffi@eli-voyages.com`
- **Mot de passe** : `password123`
- **Permissions** : Gestion des utilisateurs, dossiers, documents
- **Utilisation** : Responsable des opérations

---

### 🟡 Agent - Opérations quotidiennes
- **Email** : `agent@eli-voyages.com`
- **Mot de passe** : `agent123`
- **Permissions** : 
  - Créer et gérer les dossiers
  - Uploader et modifier les documents
  - Inviter des clients
  - Gérer les rendez-vous
- **Utilisation** : Conseiller client / Agent de voyage

---

### 🟢 Client - Vue limitée
- **Email** : `client@example.com`
- **Mot de passe** : `client123`
- **Permissions** : 
  - Voir ses propres dossiers
  - Télécharger ses documents
  - Prendre des rendez-vous
  - Signer des contrats
- **Utilisation** : Client final

---

## 🎯 Scénarios de Démonstration

### Scénario 1 : SuperAdmin
1. Connexion avec `admin@eli-voyages.com`
2. Dashboard → Vue complète des statistiques
3. Dossiers → Créer un nouveau dossier
4. Ouvrir un dossier → Valider et Approuver
5. Notifications → Badge avec 5 notifications
6. Analytics → Graphiques et métriques

### Scénario 2 : Agent
1. Connexion avec `agent@eli-voyages.com`
2. Dossiers → Créer un dossier client
3. Upload de documents (passeport, visa, etc.)
4. Invitations → Inviter un nouveau client
5. Rendez-vous → Planifier une consultation

### Scénario 3 : Client
1. Connexion avec `client@example.com`
2. Dashboard → Vue personnelle
3. Mes dossiers → Voir le statut
4. Documents → Télécharger
5. Rendez-vous → Réserver un créneau

---

## 🔧 Commandes utiles

### Redémarrer le serveur
```bash
php artisan serve
```

### Recréer les utilisateurs
```bash
php artisan db:seed --class=AdminUserSeeder
```

### Créer des notifications de test
```bash
php artisan db:seed --class=NotificationSeeder
```

### Vider les caches
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

---

## 📱 Fonctionnalités à montrer

### ✅ Système de permissions
- Boutons conditionnels selon les rôles
- Validation et approbation (SuperAdmin/Consultant)
- Upload limité aux agents

### ✅ Notifications en temps réel
- Badge dynamique avec compteur
- Liste paginée avec filtres
- Marquage comme lu

### ✅ Gestion des dossiers
- Création avec workflow
- Suivi d'activité (timeline)
- Changement de statut

### ✅ Documents
- Upload sécurisé
- Prévisualisation
- Téléchargement avec logs

### ✅ Rendez-vous
- Calendrier interactif
- Disponibilités agents
- Confirmation par email

### ✅ Analytics
- Taux de conversion
- Métriques de performance
- Graphiques temps réel

---

## ⚠️ Important pour la démo

1. **Hard Refresh** : Appuyer sur `Ctrl + F5` avant chaque démo
2. **Permissions** : Se connecter avec le bon rôle selon la fonctionnalité
3. **Notifications** : 5 notifications pré-créées pour admin@eli-voyages.com
4. **Service Worker** : Peut cacher les modifications, utiliser mode navigation privée si besoin

---

## 🎨 Design Highlights

- **Thème** : Gradient bleu/indigo avec accents or (couleurs ELI Voyages)
- **Responsive** : Optimisé mobile, tablette, desktop
- **Dark mode** : Support du mode sombre
- **PWA** : Installation possible comme app
- **Animations** : Transitions fluides avec Tailwind

---

*Dernière mise à jour : 8 novembre 2025*
