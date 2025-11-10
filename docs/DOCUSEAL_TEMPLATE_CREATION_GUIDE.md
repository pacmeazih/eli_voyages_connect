# 📝 Guide Complet : Créer un Template DocuSeal

## 🎯 Objectif
Créer des templates de contrats sur DocuSeal pour permettre la signature électronique.

---

## 📋 Étape 1 : Accéder à DocuSeal Console

1. **Ouvrez votre navigateur** : https://console.docuseal.com
2. **Connectez-vous** avec vos identifiants
3. Vous arriverez sur le **Dashboard**

---

## ➕ Étape 2 : Créer un Nouveau Template

### Option A : À partir d'un PDF existant (RECOMMANDÉ)

1. Cliquez sur **"New Template"** en haut à droite
2. Sélectionnez **"Upload PDF"**
3. Choisissez votre fichier PDF de contrat
4. Cliquez sur **"Upload"**

**Préparez vos PDFs** :
```
📄 Contrat de Service ELI-VOYAGES.pdf
📄 Contrat de Réservation.pdf
📄 Contrat de Paiement.pdf
```

### Option B : Créer depuis zéro

1. Cliquez sur **"New Template"**
2. Sélectionnez **"Create from scratch"**
3. Utilisez l'éditeur pour créer votre document

---

## 👥 Étape 3 : Définir les Signataires (Rôles)

Dans la barre latérale droite, section **"Roles"** :

### Configuration Typique pour ELI-VOYAGES :

```
Role 1: Client
  - Nom affiché : "Client"
  - Code interne : client
  - Couleur : Bleu 🔵
  - Ordre de signature : 1

Role 2: Guarantor (Optionnel)
  - Nom affiché : "Garant"
  - Code interne : guarantor
  - Couleur : Vert 🟢
  - Ordre de signature : 2
```

**Important** : Les codes `client` et `guarantor` doivent correspondre à ce qu'on envoie dans le code Laravel !

---

## 📝 Étape 4 : Ajouter les Champs sur le Document

### Types de Champs Disponibles

| Icône | Champ | Utilisation | Obligatoire |
|-------|-------|-------------|-------------|
| ✍️ | **Signature** | Signature manuscrite | ✅ Oui |
| 📅 | **Date** | Date de signature | ✅ Recommandé |
| 📧 | **Email** | Email du signataire | ⚪ Optionnel |
| ✏️ | **Text** | Texte libre (nom, adresse...) | ⚪ Selon besoin |
| ☑️ | **Checkbox** | Cases à cocher | ⚪ Pour conditions |
| 🔤 | **Initials** | Initiales/Paraphes | ⚪ Pour pages multiples |
| 📷 | **Image** | Upload d'image (photo ID...) | ⚪ Si besoin |

### Exemple de Placement pour un Contrat

```
┌─────────────────────────────────────────────────────────────┐
│                    CONTRAT DE SERVICE                        │
│                    ELI-VOYAGES SARL U                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  INFORMATIONS CLIENT                                        │
│  ────────────────────────────────────────────────────────── │
│  Nom : [Text Field - Client]           Prénom : [Text]     │
│  Email : [Email Field - Client]                             │
│  Téléphone : [Text Field - Client]                          │
│  Adresse : [Text Field - Client]                            │
│                                                              │
│  DÉTAILS DU VOYAGE                                          │
│  ────────────────────────────────────────────────────────── │
│  Référence Dossier : DOS-2025-001 (pré-rempli)            │
│  Destination : [Text - Pré-rempli depuis Laravel]          │
│  Date départ : [Date - Pré-rempli]                         │
│  Montant Total : [Text - Pré-rempli]                       │
│                                                              │
│  CONDITIONS GÉNÉRALES                                        │
│  ────────────────────────────────────────────────────────── │
│  [☑️] J'ai lu et accepte les conditions générales          │
│  [☑️] J'accepte la politique d'annulation                  │
│  [☑️] Je confirme l'exactitude des informations             │
│                                                              │
│  SIGNATURE CLIENT                                           │
│  ────────────────────────────────────────────────────────── │
│  Date : [Date Field - Client - Auto]                       │
│  Signature : [Signature Field - Client]                     │
│                                                              │
│  ────────────────────────────────────────────────────────── │
│                                                              │
│  GARANTOR (SI APPLICABLE)                                   │
│  ────────────────────────────────────────────────────────── │
│  Nom complet : [Text Field - Guarantor]                    │
│  Email : [Email Field - Guarantor]                          │
│  Téléphone : [Text Field - Guarantor]                      │
│                                                              │
│  Date : [Date Field - Guarantor - Auto]                    │
│  Signature : [Signature Field - Guarantor]                  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🏷️ Étape 5 : Nommer les Champs (TRÈS IMPORTANT !)

Pour chaque champ ajouté, cliquez dessus et dans les propriétés à droite :

### Champs Client

```json
{
  "client_nom": "Nom du client",
  "client_prenom": "Prénom du client",
  "client_email": "Email du client",
  "client_telephone": "Téléphone du client",
  "client_adresse": "Adresse complète",
  "client_signature": "Signature du client",
  "client_date": "Date de signature"
}
```

### Champs Guarantor (Garant)

```json
{
  "guarantor_nom": "Nom du garant",
  "guarantor_prenom": "Prénom du garant",
  "guarantor_email": "Email du garant",
  "guarantor_telephone": "Téléphone du garant",
  "guarantor_signature": "Signature du garant",
  "guarantor_date": "Date de signature garant"
}
```

### Champs de Contrat (Pré-remplis depuis Laravel)

```json
{
  "dossier_reference": "Référence du dossier",
  "destination": "Destination du voyage",
  "date_depart": "Date de départ",
  "date_retour": "Date de retour",
  "montant_total": "Montant total TTC",
  "montant_acompte": "Montant de l'acompte",
  "conditions_paiement": "Conditions de paiement"
}
```

**Ces noms seront utilisés dans votre code Laravel** :

```php
// Dans ContractController@store()
$submitters = [
    [
        'role' => 'client',
        'email' => $request->signers[0]['email'],
        'fields' => [
            ['name' => 'client_nom', 'default_value' => $client->nom],
            ['name' => 'client_prenom', 'default_value' => $client->prenom],
            ['name' => 'client_email', 'default_value' => $client->email],
            // ... etc
        ]
    ]
];
```

---

## ⚙️ Étape 6 : Configurer les Options du Template

Dans les paramètres du template :

### 📧 Notifications Email
- ✅ **Send email to signers** : Activé
- ✅ **Send completion email** : Activé
- ⚪ **CC emails** : Optionnel (votre email agence)

### 🔒 Sécurité et Validation
- ✅ **Require all signers** : Activé (tous doivent signer)
- ✅ **Require email verification** : Recommandé
- ✅ **Allow decline** : Activé (permettre refus)
- ⏱️ **Expiration** : 30 jours

### 🔗 Redirections
- **Completion URL** : `https://votredomaine.com/contracts/completed`
- **Decline URL** : `https://votredomaine.com/contracts/declined`

### 📱 Options d'Accès
- ✅ **Allow mobile signature** : Activé
- ✅ **Allow download before signing** : Selon préférence
- ⚪ **Require authentication** : Optionnel

---

## 💾 Étape 7 : Sauvegarder le Template

1. **Donnez un nom clair** :
   ```
   Contrat de Service - ELI VOYAGES
   Contrat de Réservation - ELI VOYAGES
   Contrat de Paiement - ELI VOYAGES
   ```

2. **Ajoutez une description** (optionnel) :
   ```
   Template pour les contrats de service voyage avec signature client + garant
   ```

3. Cliquez sur **"Save Template"** ou **"Publish"**

---

## 🆔 Étape 8 : Récupérer le Template ID

### Méthode 1 : Depuis l'URL

Après sauvegarde, regardez l'URL dans votre navigateur :

```
https://console.docuseal.com/templates/123456
                                        ^^^^^^
                                   Template ID
```

### Méthode 2 : Depuis la Liste

1. Allez dans **"Templates"** dans le menu
2. Trouvez votre template
3. Le Template ID est dans la colonne **"ID"**

### Méthode 3 : Via l'API

```bash
curl https://api.docuseal.co/templates \
  -H "X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg"
```

---

## 🔧 Étape 9 : Configurer dans Laravel

### A. Fichier `.env`

Copiez votre `.env.example` vers `.env` et ajoutez :

```bash
# DocuSeal Configuration
DOCUSEAL_API_KEY=NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
DOCUSEAL_API_URL=https://api.docuseal.co

# Template IDs (remplacez par vos vrais IDs)
DOCUSEAL_TEMPLATE_SERVICE=123456
DOCUSEAL_TEMPLATE_RESERVATION=789012
DOCUSEAL_TEMPLATE_PAYMENT=345678
```

### B. Utilisation dans le Code

```php
// Dans votre ContractController
use Illuminate\Support\Facades\Config;

public function store(Request $request, Dossier $dossier)
{
    // Récupérer le template ID selon le type
    $templateId = Config::get("docuseal.templates.{$request->type}");
    
    // Créer la submission
    $submission = $this->docuSealService->createSubmission(
        $templateId,
        $submitters,
        $options
    );
}
```

---

## 🌐 Étape 10 : Configurer le Webhook

Pour recevoir les notifications quand un contrat est signé :

1. Dans **DocuSeal Console** → **Settings** → **Webhooks**
2. Ajoutez une nouvelle URL de webhook :
   ```
   https://votredomaine.com/api/webhooks/docuseal
   ```
3. Sélectionnez les événements :
   - ✅ `form.viewed`
   - ✅ `form.started`
   - ✅ `form.completed`
   - ✅ `form.declined`
   - ✅ `submission.created`
   - ✅ `submission.completed`
   - ✅ `submission.expired`

4. Sauvegardez

**Important** : Votre webhook doit être accessible publiquement (pas localhost) !

---

## ✅ Étape 11 : Tester le Template

### Test Depuis DocuSeal Console

1. Dans la page du template, cliquez sur **"Test"** ou **"Send Test"**
2. Remplissez les emails de test
3. Vérifiez que vous recevez l'email
4. Signez le document de test
5. Vérifiez que tout fonctionne

### Test Depuis Laravel (Recommandé)

```php
// Dans php artisan tinker
$service = app(\App\Services\DocuSealService::class);

$submission = $service->createSubmission(
    123456, // Votre template ID
    [
        [
            'role' => 'client',
            'email' => 'votre-email@test.com',
            'fields' => [
                ['name' => 'client_nom', 'default_value' => 'Test'],
                ['name' => 'client_prenom', 'default_value' => 'User'],
            ]
        ]
    ]
);

dd($submission);
```

---

## 🎨 Conseils de Design

### Pour un Template Professionnel

1. **Header avec Logo** : Ajoutez votre logo ELI-VOYAGES en haut
2. **Footer avec Infos** : Coordonnées de l'agence en bas
3. **Espacement** : Laissez de l'espace entre les sections
4. **Couleurs** : Utilisez des couleurs cohérentes avec votre marque
5. **Polices** : Utilisez des polices lisibles (Arial, Helvetica)

### Champs Signature

- Placez la signature dans un **cadre visible**
- Ajoutez une **ligne en pointillé** sous la signature
- Mettez un **label clair** : "Signature du client :"
- Ajoutez la **date automatique** à côté

### Checkboxes

- Utilisez-les pour les **conditions obligatoires**
- Mettez un texte court à côté : "J'accepte..."
- Groupez-les logiquement

---

## 📊 Récapitulatif : Checklist de Création

- [ ] 1. Se connecter à console.docuseal.com
- [ ] 2. Créer nouveau template (upload PDF ou from scratch)
- [ ] 3. Définir les rôles (client, guarantor)
- [ ] 4. Placer les champs (signature, date, text, checkbox)
- [ ] 5. Nommer chaque champ (client_nom, client_email...)
- [ ] 6. Configurer les options (email, expiration, redirect)
- [ ] 7. Sauvegarder et publier
- [ ] 8. Récupérer le Template ID
- [ ] 9. Ajouter Template ID dans .env Laravel
- [ ] 10. Configurer le webhook DocuSeal
- [ ] 11. Tester avec un email de test
- [ ] 12. Tester depuis Laravel (tinker)

---

## 🆘 Problèmes Courants

### "Template not found"
→ Vérifiez que le Template ID est correct dans votre `.env`

### "Invalid API key"
→ Vérifiez votre `DOCUSEAL_API_KEY` dans `.env`

### "Role not found"
→ Les rôles dans votre code doivent correspondre aux rôles du template

### "Field not found"
→ Les noms de champs dans votre code doivent correspondre aux noms dans le template

### "Email not sent"
→ Vérifiez que les emails sont valides et que les notifications sont activées

---

## 📚 Ressources

- 📖 **Documentation DocuSeal** : https://www.docuseal.com/docs
- 🎬 **Vidéos Tutoriels** : https://www.docuseal.com/tutorials
- 💬 **Support DocuSeal** : support@docuseal.com
- 📝 **API Reference** : https://www.docuseal.com/docs/api

---

## 🎯 Prochaine Étape

Une fois votre template créé et configuré :

1. ✅ Ajoutez le Template ID dans `.env`
2. ✅ Testez avec `php artisan tinker`
3. ✅ Testez depuis votre interface frontend
4. ✅ Configurez le webhook
5. ✅ Lancez en production ! 🚀

**Votre système de signature électronique est prêt !**
