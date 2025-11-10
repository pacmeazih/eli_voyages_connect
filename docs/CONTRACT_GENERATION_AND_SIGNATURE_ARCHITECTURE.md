# 🔄 Architecture Complète : Génération + Signature de Contrats

## 📊 Vue d'Ensemble

```
┌─────────────────────────────────────────────────────────────────┐
│                    PROCESSUS COMPLET                             │
└─────────────────────────────────────────────────────────────────┘

1️⃣ GÉNÉRATION (Laravel + PHPWord)
   ↓
2️⃣ STOCKAGE (Storage)
   ↓
3️⃣ SIGNATURE (DocuSeal - OPTIONNEL)
   ↓
4️⃣ ARCHIVAGE (Database + Storage)
```

---

## 🏗️ Système 1 : GÉNÉRATION de Contrats

### Objectif
Créer automatiquement des contrats PDF à partir de templates Word

### Technologies
- **PHPWord** : Manipulation de fichiers .docx
- **DomPDF** : Conversion .docx → .pdf
- **Laravel Storage** : Sauvegarde des fichiers

### Flow Détaillé

```
┌──────────────┐
│   CLIENT     │
│  Remplit     │
│  Formulaire  │
└──────┬───────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  FRONTEND (GenerateEnhanced.vue)                 │
│  • Type de contrat                               │
│  • Données client                                │
│  • Données voyage                                │
└──────┬───────────────────────────────────────────┘
       │ POST /contracts/generate/{dossier}
       ↓
┌──────────────────────────────────────────────────┐
│  BACKEND (ContractController@store)              │
│  1. Récupère données (client, dossier, package)  │
│  2. Prépare variables                            │
│  3. Appelle ContractGeneratorService             │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  ContractGeneratorService                        │
│  1. Charge template.docx                         │
│  2. Remplace ${variables}                        │
│  3. Génère PDF                                   │
│  4. Sauvegarde dans storage                      │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  STORAGE                                         │
│  storage/app/contracts/                          │
│  • contract_service_1699628400_abc123.pdf        │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  DATABASE (contracts table)                      │
│  • contract_id                                   │
│  • dossier_id                                    │
│  • type                                          │
│  • file_path                                     │
│  • status: 'generated'                           │
└──────────────────────────────────────────────────┘
```

### Fichiers Impliqués

```
app/
  Services/
    ContractGeneratorService.php  ✅ CRÉÉ
  Http/
    Controllers/
      ContractController.php      ⚠️ À créer/mettre à jour

storage/
  app/
    templates/
      contracts/
        service.docx              📝 VOTRE TEMPLATE ICI
        reservation.docx          📝 VOTRE TEMPLATE ICI
        payment.docx              📝 VOTRE TEMPLATE ICI
    contracts/                    📁 Contrats générés ici
    temp/                         📁 Fichiers temporaires
```

---

## 🏗️ Système 2 : SIGNATURE Électronique (OPTIONNEL)

### Objectif
Envoyer le PDF généré pour signature électronique

### Technologies
- **DocuSeal API** : Plateforme de signature
- **Webhooks** : Notifications en temps réel

### Flow Détaillé

```
┌──────────────────────────────────────────────────┐
│  APRÈS GÉNÉRATION                                │
│  • PDF créé                                      │
│  • Stocké dans storage                           │
└──────┬───────────────────────────────────────────┘
       │ [OPTIONNEL]
       ↓
┌──────────────────────────────────────────────────┐
│  ContractController                              │
│  1. Upload PDF vers DocuSeal                     │
│  2. Ou utilise template DocuSeal                 │
│  3. Crée submission                              │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  DocuSealService@createSubmission                │
│  POST https://api.docuseal.co/submissions        │
│  {                                               │
│    "template_id": 123456,                        │
│    "submitters": [                               │
│      { "role": "client", "email": "..." }        │
│    ]                                             │
│  }                                               │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  DOCUSEAL API                                    │
│  • Crée submission                               │
│  • Génère embed_url                              │
│  • Envoie email au client                        │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  CLIENT                                          │
│  • Reçoit email avec lien                        │
│  • Ouvre iframe de signature                     │
│  • Signe le document                             │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  DOCUSEAL                                        │
│  • Envoie webhook                                │
│  POST /api/webhooks/docuseal                     │
│  { "event": "form.completed", ... }              │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  ContractController@webhook                      │
│  • Met à jour status → 'completed'               │
│  • Télécharge PDF signé                          │
│  • Sauvegarde dans storage                       │
└──────┬───────────────────────────────────────────┘
       │
       ↓
┌──────────────────────────────────────────────────┐
│  DATABASE                                        │
│  contracts:                                      │
│    status: 'completed'                           │
│    signed_document_path: '...'                   │
│    completed_at: '2025-11-10 15:30:00'           │
│                                                  │
│  contract_signatures:                            │
│    status: 'signed'                              │
│    signed_at: '2025-11-10 15:30:00'              │
└──────────────────────────────────────────────────┘
```

---

## 🎯 Deux Modes d'Utilisation

### Mode 1 : GÉNÉRATION SEULE (Simple)

**Cas d'usage** : Contrats imprimés, signés physiquement

```
1. Client remplit formulaire
   ↓
2. Laravel génère PDF depuis template Word
   ↓
3. PDF téléchargeable
   ↓
4. Client imprime et signe à la main
```

**Avantages** :
- ✅ Simple
- ✅ Pas de coût supplémentaire
- ✅ Fonctionne offline

**Fichiers nécessaires** :
- ✅ `ContractGeneratorService.php`
- ✅ Templates Word (.docx)
- ✅ `ContractController` (partie génération)

---

### Mode 2 : GÉNÉRATION + SIGNATURE (Complet)

**Cas d'usage** : Signature électronique légale, 100% digital

```
1. Client remplit formulaire
   ↓
2. Laravel génère PDF depuis template Word
   ↓
3. Laravel envoie à DocuSeal
   ↓
4. Client signe électroniquement
   ↓
5. PDF signé archivé automatiquement
```

**Avantages** :
- ✅ 100% digital
- ✅ Valeur légale
- ✅ Tracking automatique
- ✅ Emails automatiques

**Fichiers nécessaires** :
- ✅ `ContractGeneratorService.php`
- ✅ Templates Word (.docx)
- ✅ `DocuSealService.php`
- ✅ `ContractController` (complet)
- ✅ Template DocuSeal (optionnel)

---

## 🔀 Quelle Option Choisir ?

### Option A : Template Word → DocuSeal

```
1. Créer template Word avec variables
2. Générer PDF avec ContractGeneratorService
3. Upload PDF vers DocuSeal
4. Client signe
```

**Avantage** : Design total dans Word  
**Inconvénient** : Upload du PDF à chaque fois

### Option B : Template DocuSeal Direct

```
1. Créer template directement dans DocuSeal console
2. Définir champs de signature dans DocuSeal
3. Envoyer données via API
4. Client signe
```

**Avantage** : Plus rapide, pas de génération PDF  
**Inconvénient** : Design dans DocuSeal console

### 🎯 RECOMMANDATION : Hybride

```
1. Template Word pour contrats complexes/personnalisés
   → Génération PDF → Archivage

2. Template DocuSeal pour signature simple
   → Envoi direct → Signature → Archivage
```

---

## 📋 Checklist d'Implémentation

### Phase 1 : Génération (Obligatoire)
- [x] ✅ `ContractGeneratorService` créé
- [x] ✅ Dossiers créés (`storage/app/templates/contracts/`)
- [ ] 📝 Créer templates Word (.docx)
- [ ] 📤 Placer templates dans `storage/app/templates/contracts/`
- [ ] 🔧 Mettre à jour `ContractController@store`
- [ ] 🧪 Tester génération avec Tinker

### Phase 2 : Signature (Optionnel)
- [x] ✅ `DocuSealService` créé
- [x] ✅ Migration DocuSeal créée
- [ ] 🔑 Ajouter `DOCUSEAL_API_KEY` dans `.env`
- [ ] 📝 Créer template DocuSeal (ou utiliser PDF)
- [ ] 🔧 Ajouter code signature dans `ContractController`
- [ ] 🪝 Configurer webhook DocuSeal
- [ ] 🧪 Tester signature end-to-end

---

## 🎬 Prochaines Étapes Recommandées

### Étape 1 : Commencer Simple (30 min)
1. Ouvrir Microsoft Word
2. Copier le contenu de `storage/app/templates/contracts/README.md`
3. Coller dans Word
4. Ajouter logo dans en-tête
5. Sauvegarder : `service.docx`
6. Placer dans : `storage/app/templates/contracts/`

### Étape 2 : Tester la Génération (10 min)
```bash
php artisan tinker

$generator = app(\App\Services\ContractGeneratorService::class);
$variables = [
    'dossier_reference' => 'DOS-TEST-001',
    'date_generation' => '10/11/2025',
    'client_nom' => 'DUPONT',
    'client_prenom' => 'Jean',
    'client_nom_complet' => 'Jean DUPONT',
    'destination' => 'Dubaï',
    'montant_total_ttc' => '1 500,00 €',
];
$pdfPath = $generator->generateContract('service', $variables, 'pdf');
echo "PDF généré : " . $pdfPath;
```

### Étape 3 : Intégrer dans le Controller (30 min)
Mettre à jour `ContractController@store` pour utiliser le service

### Étape 4 : Ajouter Signature (Optionnel) (1h)
Intégrer DocuSeal si signature électronique nécessaire

---

## 📚 Documentation Créée

Tous les guides sont dans le dossier `docs/` :

1. ✅ **WORD_TEMPLATE_CREATION_GUIDE.md** - Guide création templates Word
2. ✅ **DOCUSEAL_TEMPLATE_CREATION_GUIDE.md** - Guide DocuSeal (si signature)
3. ✅ **FRONTEND_BACKEND_CONNECTION.md** - Architecture frontend/backend
4. ✅ **README.md** dans `storage/app/templates/contracts/` - Exemple de template

---

## ✨ Récapitulatif

**Vous avez maintenant 2 systèmes** :

1. **Génération automatique de contrats** (Laravel + PHPWord)
   - ✅ Service créé
   - ✅ Dossiers créés
   - 📝 Il vous reste juste à créer vos templates Word

2. **Signature électronique** (DocuSeal - Optionnel)
   - ✅ Service créé
   - ✅ Migration créée
   - ⚙️ Configuration à faire si vous voulez l'utiliser

**C'est à vous de choisir** :
- 🎯 **Génération seule** → Plus simple
- 🚀 **Génération + Signature** → Plus complet

Les deux fonctionnent indépendamment ! 🎉
