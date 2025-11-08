#!/bin/bash

# Script d'installation automatique pour cPanel
# Utilisation : bash install-cpanel.sh

echo "============================================"
echo "Installation ELI Voyages Connect - cPanel"
echo "============================================"
echo ""

# Vérifier si on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur : Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

# Demander les informations de base de données
echo "📋 Configuration de la base de données MySQL"
echo "-------------------------------------------"
read -p "Nom de la base de données : " DB_NAME
read -p "Utilisateur MySQL : " DB_USER
read -sp "Mot de passe MySQL : " DB_PASS
echo ""
read -p "Hôte MySQL (généralement localhost) : " DB_HOST
DB_HOST=${DB_HOST:-localhost}

# Demander le domaine
echo ""
echo "🌐 Configuration du domaine"
echo "----------------------------"
read -p "URL du site (ex: https://clients.elivoyages.com) : " APP_URL

# Demander l'email
echo ""
echo "📧 Configuration Email"
echo "----------------------"
read -p "Email d'envoi (ex: no-reply@elivoyages.com) : " MAIL_FROM
read -p "Mot de passe email : " MAIL_PASS

echo ""
echo "🚀 Début de l'installation..."
echo ""

# 1. Copier .env.example vers .env
echo "1️⃣  Création du fichier .env..."
if [ -f ".env" ]; then
    echo "   ⚠️  .env existe déjà, création d'une sauvegarde..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi
cp .env.example .env

# 2. Configurer .env
echo "2️⃣  Configuration de .env..."
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env
sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|" .env
sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"$MAIL_FROM\"|" .env
sed -i "s|MAIL_USERNAME=.*|MAIL_USERNAME=$MAIL_FROM|" .env
sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASS|" .env

# 3. Installer les dépendances Composer
echo "3️⃣  Installation des dépendances Composer..."
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
else
    echo "   ℹ️  Composer non trouvé, téléchargement..."
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --optimize-autoloader --no-dev
fi

# 4. Générer la clé d'application
echo "4️⃣  Génération de la clé d'application..."
php artisan key:generate --force

# 5. Définir les permissions
echo "5️⃣  Configuration des permissions..."
chmod -R 775 storage bootstrap/cache
chmod -R 775 public

# 6. Créer les dossiers nécessaires
echo "6️⃣  Création des dossiers de stockage..."
mkdir -p storage/app/dossiers
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs

# 7. Lancer les migrations
echo "7️⃣  Initialisation de la base de données..."
php artisan migrate --force

# 8. Créer les rôles et permissions
echo "8️⃣  Création des rôles et permissions..."
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=PermissionSeeder --force

# 9. Optimisation
echo "9️⃣  Optimisation pour la production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 10. Créer un utilisateur admin
echo ""
echo "👤 Création de l'utilisateur administrateur"
echo "--------------------------------------------"
read -p "Nom de l'administrateur : " ADMIN_NAME
read -p "Email de l'administrateur : " ADMIN_EMAIL
read -sp "Mot de passe administrateur : " ADMIN_PASS
echo ""

php artisan tinker --execute="
\$user = App\\Models\\User::create([
    'name' => '$ADMIN_NAME',
    'email' => '$ADMIN_EMAIL',
    'password' => bcrypt('$ADMIN_PASS'),
    'email_verified_at' => now()
]);
\$user->assignRole('SuperAdmin');
echo 'Utilisateur admin créé avec succès!';
"

echo ""
echo "✅ Installation terminée avec succès!"
echo ""
echo "📝 Prochaines étapes :"
echo "   1. Créer le lien symbolique public_html :"
echo "      cd ~ && rm -rf public_html && ln -s $(pwd)/public public_html"
echo ""
echo "   2. Configurer le Cron Job dans cPanel :"
echo "      Commande : /usr/local/bin/php $(pwd)/artisan schedule:run >> /dev/null 2>&1"
echo "      Intervalle : * * * * * (chaque minute)"
echo ""
echo "   3. Activer SSL/HTTPS dans cPanel"
echo ""
echo "   4. Visitez : $APP_URL"
echo "   5. Connectez-vous avec : $ADMIN_EMAIL"
echo ""
echo "============================================"
