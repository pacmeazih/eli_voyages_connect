# 🎉 SYSTÈME DE GÉNÉRATION DE CONTRATS - INSTALLATION TERMINÉE

## ✅ CE QUI A ÉTÉ CRÉÉ

### 1. **Contrats (26 templates texte total)**

#### Français (15 contrats) - `models_contrat/`
1. ✅ Contrat_prestation_service_model_etude.txt
2. ✅ Contrat_prestation_service_model_etude_2e_et_3e_cycle.txt
3. ✅ Contrat_prestation_service_model_etude_version_garant_et_beneficiaire.txt
4. ✅ Contrat_prestation_service_model_entre_express.txt
5. ✅ Contrat_prestation_service_model_permis_travail.txt
6. ✅ Contrat_prestation_service_model_visa_visiteur.txt
7. ✅ Contrat_prestation_service_model_super_visa.txt
8. ✅ Contrat_prestation_service_model_parrainage_familial.txt
9. ✅ Contrat_prestation_service_model_citoyennete.txt
10. ✅ Contrat_prestation_service_model_ave.txt
11. ✅ Contrat_prestation_service_model_csq_quebec.txt
12. ✅ Contrat_prestation_service_model_lmia.txt
13. ✅ Contrat_prestation_service_model_restauration_prolongation.txt
14. ✅ Contrat_prestation_service_model_demande_asile.txt
15. ✅ Contrat_prestation_service_model_traduction_documents.txt

#### Anglais (11 contrats) - `models_contrat/`
1. ✅ prestation_service_study_model_english version.txt (existant)
2. ✅ Service_agreement_work_permit_model_english.txt
3. ✅ Service_agreement_visitor_visa_model_english.txt
4. ✅ Service_agreement_super_visa_model_english.txt
5. ✅ Service_agreement_family_sponsorship_model_english.txt
6. ✅ Service_agreement_citizenship_model_english.txt
7. ✅ Service_agreement_eTA_model_english.txt
8. ✅ Service_agreement_CSQ_Quebec_model_english.txt
9. ✅ Service_agreement_LMIA_model_english.txt
10. ✅ Service_agreement_status_restoration_extension_model_english.txt
11. ✅ Service_agreement_asylum_claim_model_english.txt
12. ✅ Service_agreement_document_translation_model_english.txt

### 2. **Services PHP**
- ✅ `app/Services/ContractGenerationService.php`
  - `generateContract()` - Génère .docx avec remplacement de variables
  - `createDocxTemplate()` - Convertit .txt → .docx avec en-tête/pied de page
  - `generateFromDocxTemplate()` - Utilise templates .docx existants
  - `getAvailableContractTypes()` - Liste les types de contrats
  - `getContractTemplateFilename()` - Obtient le nom du fichier template

### 3. **Contrôleurs**
- ✅ `app/Http/Controllers/ContractController.php`
  - `generate()` - Génère un contrat pour un dossier
  - `download()` - Télécharge un contrat généré
  - `preview()` - Prévisualise avant génération
  - `prepareContractVariables()` - Prépare ~40 variables automatiquement

### 4. **Commandes Artisan**
- ✅ `app/Console/Commands/GenerateContractTemplates.php`
  - Génère tous les templates .docx depuis les .txt
  - Usage: `php artisan contracts:generate-templates --lang=both`

### 5. **Routes**
✅ Ajoutées dans `routes/web.php`:
```php
// Contracts
Route::prefix('dossiers/{dossier}')->group(function () {
    Route::post('/contracts/generate', [ContractController::class, 'generate'])->name('dossiers.contracts.generate');
    Route::get('/contracts/{document}/download', [ContractController::class, 'download'])->name('dossiers.contracts.download');
});
Route::post('/contracts/preview', [ContractController::class, 'preview'])->name('contracts.preview');
```

### 6. **Extensions PHP activées**
- ✅ `ext-gd` - Pour manipulation d'images
- ✅ `ext-zip` - Pour compression ZIP

### 7. **Packages Composer**
- ✅ `phpoffice/phpword: ^1.4.0` - Génération de documents Word

---

## 📋 VARIABLES DISPONIBLES (~40+)

### Client
- `${client_civilite}` / `${client_civilite}` - M., Mme, Mlle
- `${client_nom}` / `${client_last_name}` - Nom de famille
- `${client_prenom}` / `${client_first_name}` - Prénom
- `${client_nom_complet}` / `${client_full_name}` - Nom complet
- `${client_adresse}` / `${client_address}` - Adresse complète
- `${client_cin_numero}` / `${client_id_number}` - Numéro CIN
- `${client_cin_lieu}` / `${client_id_place}` - Lieu émission CIN
- `${client_cin_date}` / `${client_id_date}` - Date émission CIN
- `${client_cin_expiration}` / `${client_id_expiry}` - Date expiration CIN
- `${client_telephone}` / `${client_phone}` - Téléphone
- `${client_email}` - Email
- `${client_passeport_numero}` / `${client_passport_number}` - Numéro passeport

### Garant/Sponsor
- `${garant_civilite}` / `${sponsor_civilite}` - Civilité
- `${garant_nom_complet}` / `${sponsor_full_name}` - Nom complet
- `${garant_adresse}` / `${sponsor_address}` - Adresse
- `${garant_telephone}` / `${sponsor_phone}` - Téléphone
- `${parrain_nom}` / `${sponsor_name}` - Nom parrain

### Bénéficiaire
- `${beneficiaire_nom}` / `${beneficiary_name}` - Nom bénéficiaire
- `${beneficiaire_nom_complet}` / `${beneficiary_full_name}` - Nom complet
- `${beneficiaire_date_naissance}` / `${beneficiary_birth_date}` - Date naissance
- `${nombre_beneficiaires}` / `${number_of_beneficiaries}` - Nombre bénéficiaires
- `${relation_avec_parrain}` / `${relationship_with_sponsor}` - Relation avec parrain

### Dossier
- `${numero_dossier}` / `${file_number}` - Numéro de dossier
- `${type_service}` / `${service_type}` - Type de service
- `${session_universitaire}` / `${academic_session}` - Session universitaire
- `${duree_contrat}` / `${contract_duration}` - Durée du contrat

### Financier
- `${montant_total}` / `${total_amount}` - Montant total
- `${montant_admission}` / `${admission_amount}` - Montant admission
- `${montant_permis}` / `${permit_amount}` - Montant permis
- `${montant_ouverture}` / `${opening_amount}` - Montant ouverture dossier
- `${montant_soumission}` / `${submission_amount}` - Montant soumission
- `${depot_initial}` / `${initial_deposit}` - Dépôt initial (500 000 FCFA)
- `${montant_preparation}` / `${preparation_amount}` - Montant préparation
- `${montant_final}` / `${final_amount}` - Montant final

### Dates
- `${date_signature}` / `${signature_date}` - Date de signature
- `${date_limite_signature}` / `${deadline_date}` - Date limite signature (14 jours)

### Agent
- `${agent_nom}` / `${agent_last_name}` - Nom de l'agent
- `${agent_prenom}` / `${agent_first_name}` - Prénom de l'agent

### LMIA spécifique
- `${employeur_nom}` / `${employer_name}` - Nom employeur
- `${employeur_adresse}` / `${employer_address}` - Adresse employeur
- `${employeur_representant}` / `${employer_representative}` - Représentant
- `${employeur_telephone}` / `${employer_phone}` - Téléphone employeur
- `${candidat_nom}` / `${candidate_name}` - Nom candidat
- `${poste}` / `${job_position}` - Poste

### Traduction spécifique
- `${nombre_documents}` / `${number_of_documents}` - Nombre de documents
- `${nombre_pages}` / `${number_of_pages}` - Nombre de pages
- `${langue_source}` / `${source_language}` - Langue source
- `${langue_cible}` / `${target_language}` - Langue cible
- `${delai_traduction}` / `${translation_deadline}` - Délai traduction
- `${tarif_par_page}` / `${rate_per_page}` - Tarif par page

---

## 🚀 UTILISATION

### 1. Générer les templates .docx (à faire UNE FOIS)

```bash
# Générer tous les templates français et anglais
php artisan contracts:generate-templates --lang=both

# Ou seulement français
php artisan contracts:generate-templates --lang=fr

# Ou seulement anglais
php artisan contracts:generate-templates --lang=en
```

Les templates seront créés dans `storage/app/templates/contracts/`

### 2. Générer un contrat pour un dossier (API)

```php
// Dans votre code PHP/Controller
use App\Services\ContractGenerationService;

$contractService = app(ContractGenerationService::class);

$variables = [
    'client_nom_complet' => 'AZIH Koffi Pacôme',
    'client_adresse' => 'Lomé, Togo',
    'client_telephone' => '+228 XX XX XX XX',
    'montant_total' => '2 000 000 F CFA',
    'date_signature' => '6 novembre 2025',
    // ... autres variables
];

$contractPath = $contractService->generateContract(
    'etude',      // Type de contrat
    $variables,   // Variables
    'fr'          // Langue
);

// $contractPath contient le chemin vers le .docx généré
```

### 3. Via route HTTP (depuis Vue.js)

```javascript
// Générer un contrat
axios.post(`/dossiers/${dossierId}/contracts/generate`, {
    contract_type: 'etude',
    language: 'fr',
    variables: {
        client_nom_complet: 'AZIH Koffi Pacôme',
        // ... autres variables
    }
})

// Télécharger un contrat
window.location.href = `/dossiers/${dossierId}/contracts/${documentId}/download`;

// Prévisualiser
axios.post('/contracts/preview', {
    contract_type: 'etude',
    language: 'fr',
    dossier_id: dossierId
})
```

---

## 📁 STRUCTURE DES FICHIERS

```
eli_voyages_connect/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── GenerateContractTemplates.php   ✅ Nouvelle commande
│   ├── Http/
│   │   └── Controllers/
│   │       └── ContractController.php          ✅ Nouveau contrôleur
│   ├── Services/
│   │   └── ContractGenerationService.php       ✅ Nouveau service
│   └── templates/
│       └── contracts/
│           └── En tete et pied de page model.docx  ✅ Template existant
├── models_contrat/                             ✅ 26 fichiers .txt
│   ├── Contrat_prestation_service_model_etude.txt
│   ├── Service_agreement_work_permit_model_english.txt
│   └── ... (tous les autres)
├── routes/
│   └── web.php                                 ✅ Routes ajoutées
└── storage/
    └── app/
        ├── contracts/                          ✅ Contrats générés
        └── templates/
            └── contracts/                      ✅ Templates .docx
```

---

## ⚙️ CONFIGURATION

### Variables d'environnement (.env)
```env
# Pas de configuration supplémentaire nécessaire
# Les contrats sont stockés dans storage/app/contracts/
```

### Permissions requises
- Le dossier `storage/app/contracts/` doit être accessible en écriture
- Le dossier `storage/app/templates/contracts/` doit être accessible en écriture

---

## 🎯 PROCHAINES ÉTAPES

1. **Créer l'interface Vue pour générer les contrats**
   - Page `resources/js/Pages/Contracts/Generate.vue`
   - Formulaire avec sélection du type de contrat
   - Prévisualisation avant génération
   - Bouton de téléchargement

2. **Intégrer DocuSeal pour les signatures électroniques**
   - Upload des contrats vers DocuSeal
   - Configuration des zones de signature
   - Webhooks pour notification de signature

3. **Créer le système de types de services**
   - Migration `service_types`
   - CRUD SuperAdmin
   - Association contrat ↔ type de service

4. **Système bilingue complet**
   - Traductions FR/EN pour toute l'interface
   - Sélecteur de langue

---

## 🐛 TROUBLESHOOTING

### PHPWord non trouvé
```bash
composer dump-autoload
php artisan optimize:clear
```

### Extensions PHP manquantes
Vérifier que `gd` et `zip` sont activées:
```bash
php -m | findstr "gd zip"
```

Si absentes, éditer `php.ini` (C:\xampp\php\php.ini):
```ini
extension=gd
extension=zip
```

### Erreur lors de la génération
Vérifier les permissions:
```bash
chmod -R 775 storage/app/contracts
chmod -R 775 storage/app/templates
```

---

## 📊 STATISTIQUES

- **Contrats français**: 15 ✅
- **Contrats anglais**: 12 ✅
- **Variables disponibles**: ~40+ ✅
- **Services créés**: 1 ✅
- **Contrôleurs créés**: 1 ✅
- **Commandes Artisan**: 1 ✅
- **Routes ajoutées**: 3 ✅

**TOTAL: 30+ fichiers créés/modifiés** 🎉

---

## 💡 NOTES IMPORTANTES

1. **Variables manquantes**: Si une variable ${...} n'est pas remplacée, elle restera visible dans le contrat final. Assurez-vous de toujours fournir toutes les variables nécessaires.

2. **Format des montants**: Les montants doivent être formatés avec espaces (ex: "2 000 000 F CFA")

3. **Format des dates**: Les dates doivent être formatées en français (ex: "6 novembre 2025") ou anglais (ex: "November 6, 2025")

4. **Versions Word**: Les contrats sont générés en format .docx (Word 2007+)

5. **Logo**: Le logo ELI-VOYAGES peut être ajouté dans l'en-tête en modifiant le service

---

## 🎨 CHARTE GRAPHIQUE ELI-VOYAGES

### Configuration Visuelle
Le système utilise une charte graphique professionnelle définie dans `app/Services/BrandingConfig.php` :

#### 🎨 Couleurs
- **Bleu Principal** (#1F497D) - Titres, nom de l'entreprise
- **Or/Jaune** (#FFD700) - Accent élégant
- **Texte Foncé** (#333333) - Contenu principal
- **Texte Clair** (#666666) - Informations secondaires

#### 📏 Dimensions
- **Logo**: 120x60 points (en-tête centré)
- **Marges**: 2cm gauche/droite, 3cm haut (pour logo), 2cm bas
- **Polices**: 
  - Nom entreprise: 14pt gras
  - Titres articles: 11pt gras
  - Texte normal: 10pt
  - Pied de page: 9pt

#### 📄 Structure Documents
- **En-tête**:
  - Logo ELI-VOYAGES centré
  - Nom de l'entreprise (bleu, gras)
  - Coordonnées (adresse + téléphone)
- **Pied de page**:
  - Numéros de page (Page X / Total)
- **Contenu**:
  - Articles en gras bleu
  - Texte justifié
  - Listes à puces formatées

### Fichiers Branding
```
app/templates/branding/
├── Eli-Voyages LOGO.png    (120x60) Logo principal
└── Eli-Voyages icon.png    Icône/favicon
```

---

Créé le 6 novembre 2025 par GitHub Copilot 🤖
