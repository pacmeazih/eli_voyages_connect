# 🔒 Automatic Backup - Guide Complet

## 📋 Vue d'Ensemble

Système de sauvegarde automatique complet utilisant **spatie/laravel-backup** pour protéger les données de l'application ELI Voyages Connect.

---

## ✅ Configuration Réalisée

### 1. Package Installé

**Package:** `spatie/laravel-backup` version 9.3.6 ✅

```bash
composer show spatie/laravel-backup
```

### 2. Fichiers de Configuration

#### `config/backup.php` ✅

Configuration complète incluant :

- **Sources sauvegardées:**
  - `app/` - Code application
  - `bootstrap/` - Fichiers bootstrap
  - `config/` - Configuration
  - `database/` - Migrations, seeders
  - `routes/` - Routes
  - `resources/` - Vues, assets
  - `public/` - Assets publics
  - `.env` - Variables d'environnement
  - `composer.json`, `composer.lock`, `package.json`
  - `storage/app/documents/` - **Documents uploadés (IMPORTANT)**
  - `storage/app/contracts/` - **Contrats générés**

- **Exclusions:**
  - `vendor/` - Dépendances Composer (réinstallables)
  - `node_modules/` - Dépendances npm (réinstallables)
  - `storage/framework/` - Cache, sessions (temporaire)
  - `storage/logs/` - Logs (volumineux et non critiques)
  - `storage/app/public/` - Symlink

- **Base de données:**
  - SQLite incluse dans la sauvegarde
  - Compression optionnelle (désactivée par défaut)
  - Timestamp format: `Y-m-d-H-i-s`

- **Destinations:**
  - Disque: `backup` (local: `storage/app/backups/`)
  - Préfixe: `eli-voyages-`
  - Format: `eli-voyages-2025-11-08-01-00-00.zip`

- **Chiffrement (optionnel):**
  - Variable: `BACKUP_ARCHIVE_PASSWORD` dans `.env`
  - Algorithme: AES-256 (si disponible)
  - Par défaut: désactivé (`null`)

#### `config/filesystems.php` ✅

Disque `backup` ajouté :

```php
'backup' => [
    'driver' => 'local',
    'root' => storage_path('app/backups'),
    'throw' => false,
    'report' => false,
],
```

#### `bootstrap/providers.php` ✅

Service provider enregistré :

```php
Spatie\Backup\BackupServiceProvider::class,
```

### 3. Scheduler Configuré ✅

`routes/console.php` :

```php
// Backup Schedules
Schedule::command('backup:run')->daily()->at('01:00');     // Backup complet
Schedule::command('backup:clean')->daily()->at('02:00');   // Nettoyage anciens backups
Schedule::command('backup:monitor')->daily()->at('03:00'); // Vérification santé
```

**Horaires choisis:**
- **01:00** - Backup (faible activité utilisateurs)
- **02:00** - Cleanup (après backup)
- **03:00** - Monitor (vérification post-cleanup)

### 4. Politique de Rétention

Configuration dans `config/backup.php` :

| Période | Rétention |
|---------|-----------|
| **Tous les backups** | 7 jours |
| **Backups quotidiens** | 16 jours |
| **Backups hebdomadaires** | 8 semaines |
| **Backups mensuels** | 4 mois |
| **Backups annuels** | 2 ans |
| **Limite espace disque** | 5000 MB (5 GB) |

**Exemple de timeline:**
- **Jours 1-7:** Tous les backups conservés
- **Jours 8-16:** 1 backup par jour
- **Semaines 3-8:** 1 backup par semaine
- **Mois 3-4:** 1 backup par mois
- **Années 1-2:** 1 backup par an

### 5. Notifications Email

Configuration dans `config/backup.php` :

**Events notifiés:**
- ✅ Backup réussi
- ❌ Backup échoué
- ✅ Cleanup réussi
- ❌ Cleanup échoué
- ⚠️ Backup unhealthy détecté
- ✅ Backup healthy confirmé

**Destinataire:**  
Variable `.env`: `BACKUP_MAIL_TO=admin@eli-voyages.com`

---

## 🚀 Commandes Disponibles

### 1. Créer un Backup

```bash
# Backup complet (base de données + fichiers)
php artisan backup:run

# Backup uniquement de la base de données
php artisan backup:run --only-db

# Backup uniquement des fichiers
php artisan backup:run --only-files

# Backup sans notifications
php artisan backup:run --disable-notifications
```

**Output attendu:**
```
Starting backup...
Dumping database sqlite...
Determining files to backup...
Zipping 1234 files...
Backup created successfully (Size: 45.3 MB)
Backup completed at: 2025-11-08 01:00:00
```

### 2. Lister les Backups

```bash
# Lister tous les backups
php artisan backup:list

# Output:
# Name             Disk    Reachable  Count  Size     Newest backup                 Oldest backup
# ---------------  ------  ---------  -----  -------  ----------------------------  ----------------------------
# eli-voyages      backup  yes        5      225 MB   2025-11-08 01:00:00 (1 day)   2025-11-01 01:00:00 (7 days)
```

### 3. Nettoyer les Anciens Backups

```bash
# Nettoyer selon politique de rétention
php artisan backup:clean

# Nettoyer et garder seulement les 3 derniers
php artisan backup:clean --keep=3

# Dry run (simuler sans supprimer)
php artisan backup:clean --dry-run
```

### 4. Surveiller la Santé des Backups

```bash
# Vérifier la santé des backups
php artisan backup:monitor

# Vérifie:
# - Âge du dernier backup (< 1 jour)
# - Espace disque utilisé (< 5 GB)
# - Accessibilité du disque
```

---

## 📂 Structure des Backups

### Emplacement

```
storage/app/backups/
├── eli-voyages-2025-11-08-01-00-00.zip
├── eli-voyages-2025-11-07-01-00-00.zip
├── eli-voyages-2025-11-06-01-00-00.zip
└── ...
```

### Contenu d'un Backup (ZIP)

```
eli-voyages-2025-11-08-01-00-00.zip
├── db-dumps/
│   └── sqlite-eli-voyages-2025-11-08-01-00-00.sqlite
├── app/
├── bootstrap/
├── config/
├── database/
├── routes/
├── resources/
├── public/
├── storage/
│   └── app/
│       ├── documents/
│       └── contracts/
├── .env
├── composer.json
├── composer.lock
└── package.json
```

**Taille estimée:** 40-100 MB (selon nombre de documents)

---

## 🔄 Restauration d'un Backup

### Méthode Manuelle (Recommandée)

1. **Arrêter l'application:**
   ```bash
   php artisan down
   ```

2. **Localiser le backup:**
   ```bash
   php artisan backup:list
   ```

3. **Extraire le backup:**
   ```bash
   cd storage/app/backups
   unzip eli-voyages-2025-11-08-01-00-00.zip -d /tmp/restore
   ```

4. **Restaurer la base de données:**
   ```bash
   # SQLite
   cp /tmp/restore/db-dumps/sqlite-*.sqlite database/database.sqlite
   ```

5. **Restaurer les fichiers:**
   ```bash
   # Documents
   cp -r /tmp/restore/storage/app/documents/* storage/app/documents/
   
   # Contrats
   cp -r /tmp/restore/storage/app/contracts/* storage/app/contracts/
   
   # Configuration (si nécessaire)
   cp /tmp/restore/.env .env
   ```

6. **Permissions:**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

7. **Redémarrer l'application:**
   ```bash
   php artisan config:cache
   php artisan cache:clear
   php artisan up
   ```

8. **Vérifier:**
   ```bash
   php artisan migrate:status
   php artisan queue:work --once
   ```

---

## 🛠️ Configuration Production

### 1. Variables d'Environnement (.env)

Ajouter dans `.env` :

```env
# Backup Configuration
BACKUP_MAIL_TO=admin@eli-voyages.com
BACKUP_ARCHIVE_PASSWORD=  # Laisser vide ou définir un mot de passe fort

# Optionnel: Backup vers AWS S3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=eli-voyages-backups
```

### 2. Backup vers AWS S3 (Recommandé pour Production)

**Modifier `config/backup.php` :**

```php
'destination' => [
    'disks' => [
        'backup',  // Local
        's3',      // AWS S3 (cloud)
    ],
],
```

**Avantages S3:**
- ✅ Redondance géographique
- ✅ Durabilité 99.999999999% (11 nines)
- ✅ Pas de limite d'espace
- ✅ Lifecycle policies automatiques
- ✅ Versioning intégré

**Installation:**
```bash
composer require league/flysystem-aws-s3-v3
```

### 3. Cron Job (Serveur Linux)

Le scheduler Laravel doit tourner en continu. Ajouter au crontab :

```bash
crontab -e

# Ajouter:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Vérification:**
```bash
# Voir les schedules
php artisan schedule:list

# Forcer l'exécution immédiate (test)
php artisan schedule:run
```

### 4. Monitoring avec Supervisor (Optionnel)

Créer `/etc/supervisor/conf.d/eli-voyages-scheduler.conf` :

```ini
[program:eli-voyages-scheduler]
process_name=%(program_name)s
command=php /path/to/project/artisan schedule:work
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/eli-voyages-scheduler.log
```

Redémarrer Supervisor :
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eli-voyages-scheduler
```

---

## 🧪 Tests

### 1. Test Backup Complet

```bash
# Créer un backup de test
php artisan backup:run

# Vérifier la création
php artisan backup:list

# Vérifier le fichier
ls -lh storage/app/backups/
```

**Résultat attendu:**
- ✅ Fichier ZIP créé dans `storage/app/backups/`
- ✅ Nom: `eli-voyages-YYYY-MM-DD-HH-MM-SS.zip`
- ✅ Taille: 40-100 MB
- ✅ Email de confirmation reçu (si configuré)

### 2. Test Restoration

```bash
# 1. Créer un backup
php artisan backup:run

# 2. Modifier la base de données (test)
php artisan tinker
>>> User::first()->update(['name' => 'TEST BEFORE RESTORE']);

# 3. Extraire le backup
cd storage/app/backups
unzip eli-voyages-*.zip -d /tmp/test-restore

# 4. Restaurer la DB
cp /tmp/test-restore/db-dumps/*.sqlite database/database.sqlite

# 5. Vérifier
php artisan tinker
>>> User::first()->name  // Devrait être le nom d'origine
```

### 3. Test Cleanup

```bash
# Simuler (dry run)
php artisan backup:clean --dry-run

# Exécuter
php artisan backup:clean

# Vérifier
php artisan backup:list
```

### 4. Test Monitoring

```bash
# Vérifier santé
php artisan backup:monitor

# Résultat attendu:
# ✅ Backup eli-voyages is healthy
# Last backup: 12 hours ago
# Size: 45.3 MB
```

---

## 📊 Statistiques & Métriques

### Taille des Backups (Estimations)

| Contenu | Taille |
|---------|--------|
| Base de données SQLite | 5-20 MB |
| Code source (app, config, routes) | 5-10 MB |
| Resources (views, js, css) | 10-20 MB |
| Documents uploadés | 20-50 MB |
| Contrats générés | 5-15 MB |
| **TOTAL** | **45-115 MB** |

### Espace Disque Requis

| Période | Backups | Espace Total |
|---------|---------|--------------|
| **1 semaine** | 7 | ~500 MB |
| **1 mois** | 16 | ~1.2 GB |
| **3 mois** | 24 | ~2 GB |
| **1 an** | 50 | ~4 GB |
| **Limite max** | Auto-cleanup | 5 GB |

### Performance

| Opération | Temps Moyen | Charge CPU | Charge I/O |
|-----------|-------------|------------|------------|
| Backup run | 30-60s | Moyenne | Élevée |
| Backup clean | 5-15s | Faible | Moyenne |
| Backup monitor | 2-5s | Faible | Faible |
| Restauration | 2-5 min | Moyenne | Élevée |

---

## 🔐 Sécurité

### Bonnes Pratiques

1. **Chiffrement des Backups:**
   ```env
   BACKUP_ARCHIVE_PASSWORD=VotreMot2PasseTrèsComplexe!2025
   ```

2. **Permissions Strictes:**
   ```bash
   chmod 700 storage/app/backups
   chown www-data:www-data storage/app/backups
   ```

3. **Backup Hors Site:**
   - Utiliser AWS S3 ou équivalent
   - Activer le versioning S3
   - Configurer lifecycle policies

4. **Test de Restauration Régulier:**
   - Tester la restauration une fois par mois
   - Documenter la procédure
   - Former l'équipe technique

5. **Rotation des Backups:**
   - Ne jamais supprimer le dernier backup
   - Conserver au moins 3 générations
   - Archiver les backups annuels

---

## 🚨 Troubleshooting

### Erreur: "Backup failed: disk not reachable"

**Cause:** Disque `backup` non configuré

**Solution:**
```bash
# Créer le répertoire
mkdir -p storage/app/backups
chmod 775 storage/app/backups

# Vérifier config/filesystems.php
php artisan config:cache
```

### Erreur: "Database dump failed"

**Cause:** Impossible de dumper SQLite

**Solution:**
```bash
# Vérifier permissions
chmod 664 database/database.sqlite
chmod 775 database/

# Tester manuellement
sqlite3 database/database.sqlite .dump > /tmp/test.sql
```

### Erreur: "Backup exceeds maximum size"

**Cause:** Trop de documents uploadés

**Solution:**
```php
// Modifier config/backup.php
'delete_oldest_backups_when_using_more_megabytes_than' => 10000, // 10 GB
```

### Erreur: "Commands not found (backup:run)"

**Cause:** Service provider non enregistré

**Solution:**
```bash
# Vérifier bootstrap/providers.php
# Régénérer autoloader
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Backups Non Exécutés Automatiquement

**Cause:** Cron job non configuré

**Solution:**
```bash
# Ajouter au crontab
crontab -e
* * * * * cd /path/to/project && php artisan schedule:run

# Vérifier
php artisan schedule:list

# Tester manuellement
php artisan schedule:run
```

---

## 📋 Checklist Déploiement Production

- [ ] Package `spatie/laravel-backup` installé
- [ ] Configuration `config/backup.php` personnalisée
- [ ] Disque `backup` dans `config/filesystems.php`
- [ ] Répertoire `storage/app/backups/` créé avec permissions 775
- [ ] Variables `.env` configurées (`BACKUP_MAIL_TO`)
- [ ] Schedules enregistrés dans `routes/console.php`
- [ ] Cron job configuré sur le serveur
- [ ] Test backup: `php artisan backup:run` ✅
- [ ] Test list: `php artisan backup:list` ✅
- [ ] Test clean: `php artisan backup:clean --dry-run` ✅
- [ ] Test monitor: `php artisan backup:monitor` ✅
- [ ] Test restauration complète ✅
- [ ] Email de notification reçu ✅
- [ ] Backup S3 configuré (si production) 📌
- [ ] Documentation équipe technique ✅
- [ ] Procédure de restauration documentée ✅
- [ ] Test de restauration mensuel planifié 📅

---

## 📚 Ressources

- **Documentation officielle:** https://spatie.be/docs/laravel-backup
- **GitHub:** https://github.com/spatie/laravel-backup
- **Support:** https://github.com/spatie/laravel-backup/issues

---

## 🎓 Résumé

✅ **Configuration complète:**
- 3 commandes schedulées (backup/clean/monitor)
- Politique de rétention intelligente (7j → 2 ans)
- Notifications email automatiques
- Support local + cloud (S3)
- Chiffrement optionnel AES-256

✅ **Protection des données:**
- Base de données SQLite
- Code source complet
- Documents uploadés (**CRITIQUE**)
- Contrats générés
- Configuration .env

✅ **Production ready:**
- Scheduler configuré
- Monitoring automatique
- Cleanup automatique
- Notifications email
- Documentation complète

---

**Mis à jour le:** 2025-11-08  
**Version:** 1.0.0  
**Statut:** ✅ Configuré et Testé
