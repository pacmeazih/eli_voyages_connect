# Installation sur cPanel

Guide complet pour déployer ELI Voyages Connect sur un hébergement cPanel standard.

## Prérequis cPanel

- PHP 8.2 ou supérieur
- MySQL 5.7+ ou MariaDB 10.3+
- Composer installé (ou accès SSH pour l'installer)
- Accès FTP/SSH
- Domaine configuré pointant vers votre hébergement

## Étape 1 : Préparation de la Base de Données

### Via cPanel MySQL
1. Connectez-vous à cPanel
2. Allez dans **MySQL Databases**
3. Créez une nouvelle base de données : `elivoyages_db`
4. Créez un utilisateur MySQL : `elivoyages_user`
5. Définissez un mot de passe fort
6. Associez l'utilisateur à la base avec **tous les privilèges**
7. Notez : nom de la base, utilisateur, mot de passe, et hôte (généralement `localhost`)

## Étape 2 : Configuration PHP (cPanel)

### Vérifier/Modifier la version PHP
1. Dans cPanel, allez dans **Select PHP Version** ou **MultiPHP Manager**
2. Sélectionnez **PHP 8.2** ou supérieur
3. Activez les extensions suivantes :
   - `mbstring`
   - `pdo_mysql`
   - `curl`
   - `openssl`
   - `zip`
   - `bcmath`
   - `json`
   - `tokenizer`
   - `xml`
   - `ctype`
   - `fileinfo`

### Augmenter les limites PHP
Dans **Select PHP Version** → **Options** :
```
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 256M
```

## Étape 3 : Upload des Fichiers

### Option A : Via FTP (FileZilla, etc.)
1. Connectez-vous via FTP
2. Uploadez **tout le projet** dans `/home/votre_user/` (PAS dans `public_html` encore)
3. Structure finale :
   ```
   /home/votre_user/
   ├── eli_voyages_connect/  (tout le code Laravel ici)
   │   ├── app/
   │   ├── bootstrap/
   │   ├── config/
   │   ├── database/
   │   ├── public/         (ce dossier sera le document root)
   │   ├── resources/
   │   ├── routes/
   │   ├── storage/
   │   ├── vendor/
   │   ├── .env
   │   └── ...
   └── public_html/  (on va créer un lien symbolique)
   ```

### Option B : Via SSH (recommandé)
```bash
# Se connecter via SSH
ssh votre_user@votre_domaine.com

# Aller dans le répertoire home
cd ~

# Cloner le projet
git clone https://github.com/pacmeazih/eli_voyages_connect.git
cd eli_voyages_connect

# Installer Composer (si pas déjà installé)
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer

# Installer les dépendances PHP
./composer install --optimize-autoloader --no-dev

# Installer les dépendances Node (si disponible)
npm install
npm run build
```

## Étape 4 : Configuration du Document Root

### Déplacer public_html et créer un lien symbolique

**IMPORTANT** : Laravel nécessite que seul le dossier `public/` soit accessible depuis le web.

```bash
# Via SSH
cd ~

# Renommer l'ancien public_html (backup)
mv public_html public_html_backup

# Créer un lien symbolique vers le dossier public de Laravel
ln -s ~/eli_voyages_connect/public public_html
```

**Ou via cPanel File Manager** :
1. Renommez `public_html` en `public_html_backup`
2. Dans **File Manager** → **Advanced** → activez "Show Hidden Files"
3. Utilisez la fonction **Symbolic Link** pour créer un lien :
   - Source: `/home/votre_user/eli_voyages_connect/public`
   - Destination: `/home/votre_user/public_html`

## Étape 5 : Configuration de l'Environnement

### Créer le fichier .env

```bash
# Via SSH
cd ~/eli_voyages_connect
cp .env.example .env
nano .env  # ou vi .env
```

### Modifier .env avec vos paramètres cPanel

```env
APP_NAME="ELI Voyages Connect"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://votre-domaine.com

APP_LOCALE=fr
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=daily
LOG_LEVEL=error

# Base de données MySQL cPanel
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=elivoyages_db
DB_USERNAME=elivoyages_user
DB_PASSWORD=votre_mot_de_passe_mysql

# Session et cache (fichiers locaux pour cPanel)
SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_STORE=file
QUEUE_CONNECTION=database

# Stockage local (documents dans storage/app)
FILESYSTEM_DISK=local

# Email cPanel (utilise le serveur SMTP de votre hébergement)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=votre-email@votre-domaine.com
MAIL_PASSWORD=votre_mot_de_passe_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@votre-domaine.com"
MAIL_FROM_NAME="${APP_NAME}"

# DocuSeal (optionnel)
DOCUSEAL_API_KEY=
DOCUSEAL_API_URL=https://api.docuseal.co

# WhatsApp (optionnel)
WHATSAPP_API_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_BUSINESS_ACCOUNT_ID=
```

### Générer la clé d'application

```bash
php artisan key:generate
```

## Étape 6 : Permissions des Dossiers

```bash
# Via SSH
cd ~/eli_voyages_connect

# Donner les permissions d'écriture
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Si vous utilisez l'utilisateur web de cPanel
chown -R votre_user:votre_user storage bootstrap/cache
```

**Via cPanel File Manager** :
1. Sélectionnez les dossiers `storage` et `bootstrap/cache`
2. Clic droit → **Change Permissions**
3. Cochez : `Read`, `Write`, `Execute` pour User, Group, World
4. Cochez **Recurse into subdirectories**

## Étape 7 : Initialiser la Base de Données

```bash
# Via SSH
cd ~/eli_voyages_connect

# Exécuter les migrations
php artisan migrate --force

# Créer les rôles et permissions
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder

# (Optionnel) Créer un utilisateur admin
php artisan tinker
>>> $user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@eli-voyages.com', 'password' => bcrypt('VotreMotDePasse123!')]);
>>> $user->assignRole('SuperAdmin');
>>> exit
```

## Étape 8 : Optimisation pour Production

```bash
# Cache les configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimiser l'autoloader
composer install --optimize-autoloader --no-dev
```

## Étape 9 : Configuration SSL (HTTPS)

### Via cPanel
1. Allez dans **SSL/TLS Status**
2. Activez **AutoSSL** ou installez un certificat Let's Encrypt
3. Forcez HTTPS dans `.htaccess` :

```apache
# Dans public/.htaccess (après RewriteEngine On)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Étape 10 : Configuration Cron (pour les tâches planifiées)

### Via cPanel Cron Jobs
1. Allez dans **Cron Jobs**
2. Ajoutez une nouvelle tâche :
   - **Commande** : `/usr/local/bin/php ~/eli_voyages_connect/artisan schedule:run >> /dev/null 2>&1`
   - **Intervalle** : Chaque minute `* * * * *`

## Étape 11 : Test de l'Installation

1. Visitez `https://votre-domaine.com`
2. Vous devriez voir la page d'accueil de Laravel
3. Testez la connexion : `https://votre-domaine.com/login`
4. Connectez-vous avec l'utilisateur admin créé

## Dépannage Courant

### Erreur 500 - Internal Server Error
- Vérifiez les permissions de `storage/` et `bootstrap/cache/`
- Vérifiez que `.env` existe et contient `APP_KEY`
- Consultez les logs : `storage/logs/laravel.log`

### Erreur "No such file or directory" pour sessions
```bash
php artisan session:table
php artisan migrate
```

### Erreur Base de données
- Vérifiez les identifiants dans `.env`
- Testez la connexion MySQL depuis cPanel → phpMyAdmin

### CSS/JS ne se chargent pas
- Vérifiez que le lien symbolique `public_html` pointe vers `eli_voyages_connect/public`
- Vérifiez les permissions du dossier `public/`
- Rebuild les assets : `npm run build`

### Upload de fichiers ne fonctionne pas
- Vérifiez `upload_max_filesize` et `post_max_size` dans PHP
- Vérifiez les permissions de `storage/app/`
- Pour stockage local, les fichiers sont dans `storage/app/dossiers/`

## Maintenance

### Mise à jour du code
```bash
cd ~/eli_voyages_connect
git pull origin main
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Sauvegardes
- **Base de données** : Via cPanel → **phpMyAdmin** → Export (automatisé avec cPanel Backups)
- **Fichiers uploadés** : Sauvegardez `storage/app/dossiers/`
- **Code** : Géré par Git

## Support

Pour toute question :
- Email : support@elivoyages.com
- Documentation : README.md

---

**Félicitations ! Votre application ELI Voyages Connect est maintenant déployée sur cPanel.** 🎉
