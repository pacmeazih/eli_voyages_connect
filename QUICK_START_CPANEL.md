# 🚀 Guide Rapide : Installation cPanel (5 minutes)

## Méthode 1 : Installation Automatique (Recommandé)

### Via SSH

```bash
# 1. Connectez-vous en SSH
ssh votre_user@votre-domaine.com

# 2. Clonez le projet
cd ~
git clone https://github.com/pacmeazih/eli_voyages_connect.git
cd eli_voyages_connect

# 3. Lancez le script d'installation
bash install-cpanel.sh
```

Le script vous demandera :
- Nom de la base de données MySQL
- Utilisateur et mot de passe MySQL
- URL du site
- Email et mot de passe admin

**C'est tout !** Le script fait tout automatiquement.

---

## Méthode 2 : Installation Manuelle (15 minutes)

### Étape 1 : Base de Données (cPanel)

1. **MySQL Databases** → Créer une base :
   - Nom : `elivoyages_db`
   - Utilisateur : `elivoyages_user`
   - Mot de passe : `[votre_mot_de_passe]`
   - ✅ Donner tous les privilèges

### Étape 2 : Upload des Fichiers

**Via FTP** :
- Uploader tout dans `/home/votre_user/eli_voyages_connect/`
- **PAS dans public_html !**

**Via SSH** :
```bash
cd ~
git clone https://github.com/pacmeazih/eli_voyages_connect.git
cd eli_voyages_connect
```

### Étape 3 : Configuration PHP (cPanel)

1. **Select PHP Version** → Choisir **PHP 8.2+**
2. Activer les extensions :
   - ✅ mbstring
   - ✅ pdo_mysql
   - ✅ curl
   - ✅ zip
   - ✅ bcmath

3. **PHP Options** :
   - `upload_max_filesize = 50M`
   - `post_max_size = 50M`
   - `memory_limit = 256M`

### Étape 4 : Fichier .env

```bash
cd ~/eli_voyages_connect
cp .env.example .env
nano .env
```

**Modifier ces lignes** :
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

DB_DATABASE=elivoyages_db
DB_USERNAME=elivoyages_user
DB_PASSWORD=votre_mot_de_passe_mysql

FILESYSTEM_DISK=local

MAIL_HOST=localhost
MAIL_USERNAME=votre-email@domaine.com
MAIL_PASSWORD=votre_mot_de_passe_email
MAIL_FROM_ADDRESS="no-reply@domaine.com"
```

### Étape 5 : Installation

```bash
# Composer
composer install --optimize-autoloader --no-dev

# Générer la clé
php artisan key:generate

# Permissions
chmod -R 775 storage bootstrap/cache

# Base de données
php artisan migrate --force
php artisan db:seed --force

# Cache
php artisan config:cache
php artisan route:cache
```

### Étape 6 : Lien Symbolique

**Très important** : Laravel nécessite que `public/` soit la racine web.

```bash
cd ~
mv public_html public_html_backup
ln -s ~/eli_voyages_connect/public public_html
```

**Ou via cPanel File Manager** :
1. Renommer `public_html` → `public_html_backup`
2. Créer un lien symbolique :
   - De : `/home/votre_user/eli_voyages_connect/public`
   - Vers : `/home/votre_user/public_html`

### Étape 7 : Créer un Admin

```bash
php artisan tinker
```

Dans tinker :
```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@eli-voyages.com',
    'password' => bcrypt('VotreMotDePasse123!')
]);
$user->assignRole('SuperAdmin');
exit
```

### Étape 8 : SSL & Cron

1. **SSL/TLS** → Activer AutoSSL (Let's Encrypt)

2. **Cron Jobs** → Nouvelle tâche :
   - Commande : `/usr/local/bin/php /home/votre_user/eli_voyages_connect/artisan schedule:run >> /dev/null 2>&1`
   - Intervalle : `* * * * *` (chaque minute)

---

## ✅ Test Final

Visitez : `https://votre-domaine.com`

Vous devriez voir la page de connexion !

Connectez-vous avec l'email admin créé.

---

## 🆘 Problèmes Courants

### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
chmod -R 775 storage bootstrap/cache
```

### CSS/JS ne chargent pas
- Vérifiez que `public_html` est bien un lien symbolique vers `public/`
- Rechargez le cache : `Ctrl+F5` ou mode incognito

### "No application encryption key"
```bash
php artisan key:generate
```

### Base de données inaccessible
- Vérifiez les identifiants dans `.env`
- Testez depuis phpMyAdmin

---

## 📞 Support

- 📧 Email : support@elivoyages.com
- 📖 Doc complète : [INSTALLATION_CPANEL.md](INSTALLATION_CPANEL.md)

---

**Temps total : 5-15 minutes** ⏱️
