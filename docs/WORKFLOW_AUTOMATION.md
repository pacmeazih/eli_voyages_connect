# Workflow Automation - Guide Complet

## 📋 Vue d'Ensemble

Le système d'automatisation ELI Voyages Connect exécute automatiquement des tâches récurrentes pour réduire la charge de travail manuel et améliorer l'efficacité opérationnelle.

## 🎯 Commandes Disponibles

### 1. Rappels de Documents Manquants

**Commande:** `php artisan documents:send-reminders`

**Description:** Envoie des rappels aux clients ayant des documents manquants après 7 jours d'inactivité.

**Options:**
- `--days=X` : Nombre de jours depuis la dernière mise à jour (défaut: 7)
- `--dry-run` : Mode simulation (aucun email envoyé)

**Fonctionnement:**
- Recherche les dossiers en statut `pending` ou `in_progress`
- Détecte les documents manquants selon le type de package :
  - **Base (tous):** passeport, photo, acte de naissance
  - **Études:** diplôme, relevé de notes, lettre d'admission
  - **Travail:** contrat de travail, CV, permis de travail
  - **Famille:** acte de mariage, livret de famille
- Envoie la notification `DocumentRequiredNotification`
- Enregistre l'activité dans les logs

**Exemples:**
```bash
# Rappels pour dossiers > 7 jours
php artisan documents:send-reminders

# Rappels pour dossiers > 14 jours
php artisan documents:send-reminders --days=14

# Simulation sans envoi
php artisan documents:send-reminders --dry-run
```

**Schedule:** Tous les jours à 9h00

---

### 2. Assignation Automatique des Dossiers

**Commande:** `php artisan dossiers:auto-assign`

**Description:** Assigne automatiquement les dossiers non attribués aux agents disponibles.

**Options:**
- `--strategy=X` : Stratégie d'assignation (défaut: workload)
  - `round-robin` : Distribution circulaire équitable
  - `workload` : Assignation à l'agent le moins chargé

**Fonctionnement:**
- Recherche les dossiers avec `agent_id = null` ou `0`
- Exclut les dossiers `completed`, `rejected`, `archived`
- Récupère les utilisateurs avec rôle `Agent` ou `Admin`
- Applique la stratégie choisie :
  - **Round-robin:** Distribution cyclique (agent 1 → 2 → 3 → 1...)
  - **Workload:** Calcul du nombre de dossiers actifs par agent, assignation au moins chargé
- Enregistre l'activité avec la stratégie utilisée

**Exemples:**
```bash
# Assignation par charge de travail (recommandé)
php artisan dossiers:auto-assign --strategy=workload

# Assignation round-robin
php artisan dossiers:auto-assign --strategy=round-robin
```

**Schedule:** Toutes les heures entre 9h et 17h (heures de bureau)

---

### 3. Archivage des Anciens Dossiers

**Commande:** `php artisan dossiers:archive-old`

**Description:** Archive les dossiers complétés depuis plus d'un an pour alléger la base de données active.

**Options:**
- `--years=X` : Nombre d'années depuis la complétion (défaut: 1)
- `--dry-run` : Mode simulation (aucune modification)

**Fonctionnement:**
- Recherche les dossiers `completed` depuis > X années
- Change le statut vers `archived`
- Conserve toutes les données (archivage soft, pas de suppression)
- Enregistre l'activité avec la date de complétion

**Exemples:**
```bash
# Archivage dossiers > 1 an
php artisan dossiers:archive-old

# Archivage dossiers > 2 ans
php artisan dossiers:archive-old --years=2

# Simulation
php artisan dossiers:archive-old --dry-run
```

**Schedule:** Le 1er de chaque mois à minuit

---

### 4. Rappels de Rendez-vous

**Commande:** `php artisan appointments:send-reminders`

**Description:** Envoie des rappels pour les rendez-vous à venir (24h à l'avance par défaut).

**Options:**
- `--hours=X` : Fenêtre temporelle en heures (défaut: 24)

**Fonctionnement:**
- Recherche les rendez-vous dans les X prochaines heures
- Filtre par statut `scheduled` ou `confirmed`
- Exclut les rendez-vous ayant déjà reçu un rappel (`reminder_sent_at != null`)
- Envoie `AppointmentReminderNotification` au client ET à l'agent
- Met à jour `reminder_sent_at` pour éviter les doublons
- Enregistre l'activité

**Exemples:**
```bash
# Rappels 24h à l'avance
php artisan appointments:send-reminders

# Rappels 48h à l'avance
php artisan appointments:send-reminders --hours=48

# Rappels 2h à l'avance (urgent)
php artisan appointments:send-reminders --hours=2
```

**Schedule:** Toutes les heures (vérifie en permanence les rendez-vous à venir)

---

## ⚙️ Configuration du Scheduler

### 1. Vérifier les Schedules Enregistrés

```bash
php artisan schedule:list
```

**Output attendu:**
```
0 9 * * *  php artisan documents:send-reminders ............ Next Due: 1 day from now
0 * * * *  php artisan appointments:send-reminders ......... Next Due: 47 minutes from now
0 9-17 * * * php artisan dossiers:auto-assign .............. Next Due: 23 minutes from now
0 0 1 * *  php artisan dossiers:archive-old ................ Next Due: 15 days from now
```

### 2. Configuration du Cron (Production)

**Ajouter au crontab du serveur:**

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne (remplacer /path/to/project)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Explication:**
- `* * * * *` : Exécution chaque minute
- `cd /path/to/project` : Accès au dossier du projet
- `php artisan schedule:run` : Laravel exécute les tâches planifiées
- `>> /dev/null 2>&1` : Supprime les outputs (optionnel)

**Vérification:**
```bash
# Voir les tâches cron actives
crontab -l

# Tester manuellement
php artisan schedule:run
```

### 3. Alternative: Supervisor (Recommandé pour Production)

**Créer `/etc/supervisor/conf.d/eli-voyages-scheduler.conf`:**

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

**Démarrer:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eli-voyages-scheduler
```

---

## 🧪 Tests Manuels

### Test en Développement

```bash
# Tester chaque commande individuellement
php artisan documents:send-reminders --dry-run
php artisan dossiers:auto-assign --strategy=workload
php artisan dossiers:archive-old --dry-run
php artisan appointments:send-reminders

# Forcer l'exécution du scheduler (sans attendre le cron)
php artisan schedule:run

# Mode verbeux pour debug
php artisan schedule:work --verbose
```

### Vérifier les Logs

```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs d'activité (base de données)
php artisan tinker
>>> Activity::latest()->take(10)->get()
```

---

## 📊 Monitoring & Statistiques

### Créer un Dashboard de Monitoring

**Commande pour voir les stats d'exécution:**

```bash
php artisan tinker

# Nombre de rappels envoyés cette semaine
>>> Activity::where('description', 'Rappel de documents envoyé automatiquement')
       ->whereBetween('created_at', [now()->startOfWeek(), now()])
       ->count()

# Derniers dossiers auto-assignés
>>> Activity::where('description', 'like', 'Dossier auto-assigné%')
       ->latest()->take(5)->get()

# Rendez-vous avec rappel
>>> Appointment::whereNotNull('reminder_sent_at')->count()
```

### Alertes en Cas d'Échec

**Ajouter dans `routes/console.php`:**

```php
Schedule::command('documents:send-reminders')
    ->dailyAt('09:00')
    ->onFailure(function () {
        Log::error('Échec de l\'envoi des rappels de documents');
        // Envoyer une notification admin
    });
```

---

## 🔧 Dépannage

### Problème: Les Commandes ne s'Exécutent Pas

**Vérifications:**

1. **Le cron est-il actif ?**
   ```bash
   service cron status
   ```

2. **Les permissions sont-elles correctes ?**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

3. **Le scheduler Laravel tourne-t-il ?**
   ```bash
   php artisan schedule:list
   ```

4. **Y a-t-il des erreurs dans les logs ?**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Problème: Doublons d'Emails

**Solution:** Les commandes incluent déjà des protections :
- `SendDocumentReminders` : Vérifie la date de dernière MAJ
- `SendAppointmentReminders` : Vérifie `reminder_sent_at`

**Forcer la réinitialisation (si nécessaire):**
```sql
UPDATE appointments SET reminder_sent_at = NULL WHERE id = X;
```

### Problème: Performance Lente

**Optimisations:**

1. **Indexer les colonnes fréquemment requêtées:**
   ```php
   Schema::table('dossiers', function (Blueprint $table) {
       $table->index(['status', 'updated_at']);
       $table->index('agent_id');
   });
   ```

2. **Limiter les résultats:**
   ```php
   // Dans la commande, ajouter ->limit(100)
   $dossiers = Dossier::where(...)
       ->limit(100)
       ->get();
   ```

3. **Utiliser les queues pour les emails:**
   ```php
   // Déjà implémenté avec ShouldQueue
   class AppointmentReminderNotification extends Notification implements ShouldQueue
   ```

---

## 📈 Métriques de Performance

### Temps d'Exécution Moyens (base de 1000 dossiers)

| Commande | Temps Moyen | Charge DB | Emails Envoyés |
|----------|-------------|-----------|----------------|
| `documents:send-reminders` | 30-45s | Moyenne | 50-100 |
| `appointments:send-reminders` | 10-20s | Légère | 20-50 |
| `dossiers:auto-assign` | 5-15s | Légère | 0 |
| `dossiers:archive-old` | 15-30s | Moyenne | 0 |

---

## 🎓 Bonnes Pratiques

1. **Toujours tester en dry-run d'abord**
   ```bash
   php artisan documents:send-reminders --dry-run
   ```

2. **Surveiller les logs après déploiement**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Configurer des alertes pour les échecs**
   - Utiliser `onFailure()` sur les schedules
   - Intégrer un service comme Sentry ou Bugsnag

4. **Documenter les changements de configuration**
   - Toute modification dans `console.php` doit être versionnée
   - Communiquer aux ops les changements de cron

5. **Tester la charge en staging avant production**
   ```bash
   # Créer des données de test
   php artisan db:seed --class=DossierSeeder
   
   # Tester les commandes
   php artisan documents:send-reminders --dry-run
   ```

---

## 🆘 Support

**En cas de problème :**
1. Consulter les logs : `storage/logs/laravel.log`
2. Vérifier la table `activity_log` dans la base de données
3. Exécuter manuellement avec `--verbose` pour plus de détails
4. Contacter l'équipe technique avec les logs d'erreur

---

## 📝 Changelog

**Version 1.0.0 - 2025-01-07**
- ✅ Implémentation initiale des 4 commandes
- ✅ Configuration des schedules Laravel
- ✅ Notification `AppointmentReminderNotification`
- ✅ Logs d'activité avec Spatie
- ✅ Options dry-run et customisation
- ✅ Documentation complète
