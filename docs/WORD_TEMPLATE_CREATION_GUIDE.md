# 📄 Guide Complet : Créer des Templates Word pour Génération Automatique

## 🎯 Concept

Vous créez un document Word (.docx) avec :
- **En-tête** : Logo, nom agence, coordonnées
- **Pied de page** : Infos légales, contact, numéro RC
- **Corps** : Texte avec des **variables** qui seront remplacées automatiquement

---

## 📝 Étape 1 : Créer le Template Word

### A. Ouvrir Microsoft Word

1. Ouvrez **Microsoft Word**
2. Créez un **nouveau document vierge**

### B. Créer l'En-tête

1. Double-cliquez en haut de la page pour ouvrir l'en-tête
2. Ajoutez votre **logo** : Insertion → Image
3. Ajoutez les infos de l'agence :

```
┌─────────────────────────────────────────────────────────────┐
│  [LOGO ELI-VOYAGES]                                         │
│                                                              │
│  ELI-VOYAGES SARL U                                         │
│  Votre Voyage, Notre Passion                                │
│  ────────────────────────────────────────────────────────── │
│  📍 Adresse : 123 Rue Example, 75001 Paris                  │
│  📞 Tél : +33 1 23 45 67 89                                 │
│  📧 Email : contact@eli-voyages.com                         │
│  🌐 Web : www.eli-voyages.com                               │
└─────────────────────────────────────────────────────────────┘
```

### C. Créer le Pied de page

1. Double-cliquez en bas de la page
2. Ajoutez les mentions légales :

```
┌─────────────────────────────────────────────────────────────┐
│  ELI-VOYAGES SARL U - RC: 123456789                         │
│  SIRET: 12345678900012 - TVA: FR12345678901                 │
│  Capital social: 10 000 € - APE: 7911Z                      │
│  ────────────────────────────────────────────────────────── │
│  📞 Service Client: +33 1 23 45 67 89                       │
│  📧 contact@eli-voyages.com                                 │
│                                                              │
│  Page ${PAGE} sur ${NUMPAGES}                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 Étape 2 : Ajouter les Variables

### Comment ça marche ?

Dans Word, vous écrivez des **variables** entre `${ }` qui seront remplacées automatiquement.

**Syntaxe** : `${nom_variable}`

### Exemple de Contrat de Service

```
═══════════════════════════════════════════════════════════════
                    CONTRAT DE SERVICE
                      N° ${dossier_reference}
═══════════════════════════════════════════════════════════════

Date : ${date_generation}


ENTRE LES SOUSSIGNÉS :

ELI-VOYAGES SARL U
Représentée par son gérant
Adresse : ${agence_adresse}
Email : ${agence_email}
Téléphone : ${agence_telephone}

Ci-après dénommée « L'Agence »

D'UNE PART,

ET

${client_civilite} ${client_nom} ${client_prenom}
Né(e) le : ${client_date_naissance} à ${client_lieu_naissance}
Nationalité : ${client_nationalite}
Adresse : ${client_adresse}
${client_code_postal} ${client_ville}, ${client_pays}
Email : ${client_email}
Téléphone : ${client_telephone}
Passeport N° : ${client_numero_passeport}

Ci-après dénommé(e) « Le Client »

D'AUTRE PART,


───────────────────────────────────────────────────────────────
ARTICLE 1 - OBJET DU CONTRAT
───────────────────────────────────────────────────────────────

Le présent contrat a pour objet de définir les conditions dans 
lesquelles l'Agence s'engage à fournir au Client les prestations 
de services suivantes :

Destination : ${destination}
Pays : ${pays_destination}
Date de départ : ${date_depart}
Date de retour : ${date_retour}
Durée du séjour : ${duree_sejour} jours

Type de visa : ${type_visa}
Motif du voyage : ${motif_voyage}


───────────────────────────────────────────────────────────────
ARTICLE 2 - CONDITIONS FINANCIÈRES
───────────────────────────────────────────────────────────────

Montant total HT :     ${montant_total_ht}
TVA (20%) :            ${montant_tva}
Montant total TTC :    ${montant_total_ttc}

Modalités de paiement :
${modalites_paiement}

Acompte versé :        ${montant_acompte}
Solde à payer :        ${montant_solde}

Échéances :
${echeances_paiement}


───────────────────────────────────────────────────────────────
ARTICLE 3 - OBLIGATIONS DE L'AGENCE
───────────────────────────────────────────────────────────────

L'Agence s'engage à :
• Fournir les prestations convenues dans les meilleurs délais
• Informer le Client de l'avancement de son dossier
• Respecter la confidentialité des données du Client
• Conseiller le Client sur les démarches à effectuer


───────────────────────────────────────────────────────────────
ARTICLE 4 - OBLIGATIONS DU CLIENT
───────────────────────────────────────────────────────────────

Le Client s'engage à :
• Fournir des documents authentiques et complets
• Respecter les échéances de paiement
• Suivre les recommandations de l'Agence
• Informer l'Agence de tout changement dans sa situation


───────────────────────────────────────────────────────────────
ARTICLE 5 - CONDITIONS D'ANNULATION
───────────────────────────────────────────────────────────────

En cas d'annulation par le Client :
• Plus de 30 jours avant le départ : remboursement à 100%
• Entre 15 et 30 jours : remboursement à 50%
• Moins de 15 jours : aucun remboursement

En cas d'annulation par l'Agence :
• Remboursement intégral des sommes versées


───────────────────────────────────────────────────────────────
ARTICLE 6 - RESPONSABILITÉ
───────────────────────────────────────────────────────────────

L'Agence ne peut être tenue responsable :
• Des refus de visa ou d'entrée sur le territoire
• Des modifications imposées par les autorités
• Des cas de force majeure


───────────────────────────────────────────────────────────────
ARTICLE 7 - PROTECTION DES DONNÉES
───────────────────────────────────────────────────────────────

Conformément au RGPD, le Client dispose d'un droit d'accès, 
de rectification et de suppression de ses données personnelles.


───────────────────────────────────────────────────────────────
ARTICLE 8 - LOI APPLICABLE ET JURIDICTION
───────────────────────────────────────────────────────────────

Le présent contrat est soumis au droit français.
En cas de litige, compétence exclusive est attribuée aux 
tribunaux de Paris.


═══════════════════════════════════════════════════════════════

SIGNATURES

Fait en deux exemplaires originaux.
À Paris, le ${date_generation}


Le Client                            L'Agence
${client_nom_complet}                ELI-VOYAGES SARL U


Signature :                          Signature :




[Espace pour signature]              [Espace pour signature]


───────────────────────────────────────────────────────────────
GARANT (le cas échéant)
───────────────────────────────────────────────────────────────

Je soussigné(e) ${guarantor_nom_complet}
Email : ${guarantor_email}
Téléphone : ${guarantor_telephone}
Relation avec le client : ${guarantor_relation}

Me porte garant du respect des engagements du Client.

Signature du garant :



[Espace pour signature]

═══════════════════════════════════════════════════════════════
```

---

## 📋 Liste Complète des Variables Disponibles

### 🗓️ Dates Système
```
${date_generation}      → 10/11/2025
${annee_courante}       → 2025
${mois_courant}         → Novembre
```

### 👤 Informations Client
```
${client_civilite}           → M., Mme, Mlle
${client_nom}                → DUPONT
${client_prenom}             → Jean
${client_nom_complet}        → Jean DUPONT
${client_email}              → jean.dupont@example.com
${client_telephone}          → +33 6 12 34 56 78
${client_adresse}            → 15 rue de la Paix
${client_ville}              → Paris
${client_code_postal}        → 75001
${client_pays}               → France
${client_date_naissance}     → 15/03/1985
${client_lieu_naissance}     → Paris
${client_nationalite}        → Française
${client_numero_passeport}   → 12AB34567
```

### 📁 Informations Dossier
```
${dossier_reference}         → DOS-2025-001
${dossier_statut}            → En cours
${dossier_type}              → Visa touristique
${dossier_date_creation}     → 01/11/2025
```

### ✈️ Informations Voyage
```
${destination}               → Dubaï
${pays_destination}          → Émirats Arabes Unis
${date_depart}               → 15/12/2025
${date_retour}               → 22/12/2025
${duree_sejour}              → 7 jours
${type_visa}                 → Visa touristique
${motif_voyage}              → Tourisme
```

### 💰 Informations Financières
```
${montant_total_ttc}         → 1 500,00 €
${montant_total_ht}          → 1 250,00 €
${montant_tva}               → 250,00 €
${montant_acompte}           → 500,00 €
${montant_solde}             → 1 000,00 €
${devise}                    → EUR
${modalites_paiement}        → Paiement en 3 fois
${echeances_paiement}        → 01/12, 15/12, 22/12
```

### 👨‍👩‍👧 Garant (optionnel)
```
${guarantor_nom}             → MARTIN
${guarantor_prenom}          → Marie
${guarantor_nom_complet}     → Marie MARTIN
${guarantor_email}           → marie.martin@example.com
${guarantor_telephone}       → +33 6 98 76 54 32
${guarantor_adresse}         → 25 avenue Victor Hugo
${guarantor_relation}        → Mère
```

### 🏢 Informations Agence
```
${agence_nom}                → ELI-VOYAGES SARL U
${agence_adresse}            → 123 Rue Example, Paris
${agence_telephone}          → +33 1 23 45 67 89
${agence_email}              → contact@eli-voyages.com
${agence_siret}              → 12345678900012
${agence_rc}                 → RC Paris 123456789
```

---

## 💾 Étape 3 : Enregistrer le Template

1. **Fichier** → **Enregistrer sous**
2. **Nom du fichier** :
   - `service.docx` (pour contrats de service)
   - `reservation.docx` (pour contrats de réservation)
   - `payment.docx` (pour contrats de paiement)
3. **Emplacement** : Notez où vous sauvegardez (Bureau, Téléchargements...)
4. **Format** : Document Word (*.docx)

---

## 📂 Étape 4 : Placer les Templates dans Laravel

### Structure des dossiers

```
storage/
  app/
    templates/
      contracts/
        service.docx          ← Votre template ici
        reservation.docx      ← Votre template ici
        payment.docx          ← Votre template ici
```

### Comment placer les fichiers ?

**Option A : Manuellement (cPanel)**

1. Connectez-vous à votre **cPanel**
2. Allez dans **File Manager**
3. Naviguez vers : `votresite/storage/app/`
4. Créez le dossier : `templates/contracts/`
5. **Uploadez** vos fichiers `.docx`

**Option B : Localement (développement)**

1. Sur votre ordinateur, allez dans le projet
2. Chemin : `storage/app/`
3. Créez : `templates/contracts/`
4. Copiez vos fichiers `.docx`

---

## 🚀 Étape 5 : Tester la Génération

### Test depuis Tinker

```bash
php artisan tinker
```

```php
// Charger le service
$generator = app(\App\Services\ContractGeneratorService::class);

// Préparer des variables de test
$variables = [
    'date_generation' => '10/11/2025',
    'client_nom' => 'DUPONT',
    'client_prenom' => 'Jean',
    'client_email' => 'jean@test.com',
    'dossier_reference' => 'DOS-2025-001',
    'destination' => 'Dubaï',
    'montant_total_ttc' => '1 500,00 €',
    // ... autres variables
];

// Générer le contrat
$pdfPath = $generator->generateContract('service', $variables, 'pdf');

echo "Contrat généré : " . $pdfPath;
// Résultat : contracts/contract_service_1699628400_abc123.pdf
```

Le PDF sera dans : `storage/app/contracts/`

---

## 🎨 Conseils de Mise en Forme Word

### Police et Taille
```
Titres principaux :    Arial 16pt, Gras
Titres articles :      Arial 12pt, Gras
Texte corps :          Arial 11pt, Normal
Variables :            Arial 11pt, Bleu (pour les repérer)
```

### Espacements
- **Avant titre** : 12pt
- **Après titre** : 6pt
- **Entre paragraphes** : 6pt
- **Interligne** : 1,15

### Bordures
Utilisez des **lignes horizontales** pour séparer les sections :
- Insertion → Formes → Ligne
- Ou : Bordures → Bordure inférieure

### Tableaux
Pour les informations financières, utilisez un tableau :

| Description | Montant |
|-------------|---------|
| Montant HT  | ${montant_total_ht} |
| TVA 20%     | ${montant_tva} |
| **Total TTC** | **${montant_total_ttc}** |

---

## ✅ Checklist Finale

- [ ] 1. Template Word créé avec en-tête et pied de page
- [ ] 2. Toutes les variables ajoutées avec la syntaxe `${variable}`
- [ ] 3. Mise en forme professionnelle appliquée
- [ ] 4. Fichier enregistré en `.docx`
- [ ] 5. Template placé dans `storage/app/templates/contracts/`
- [ ] 6. Test de génération effectué avec Tinker
- [ ] 7. PDF généré vérifié visuellement

---

## 🔄 Workflow Complet

```
1. Client remplit formulaire
   ↓
2. Laravel récupère données (client, dossier, package)
   ↓
3. ContractGeneratorService prépare variables
   ↓
4. PHPWord ouvre template.docx
   ↓
5. Remplace toutes les ${variables}
   ↓
6. Génère PDF
   ↓
7. Sauvegarde dans storage/app/contracts/
   ↓
8. [OPTIONNEL] Envoie à DocuSeal pour signature
   ↓
9. Client reçoit email avec lien signature
   ↓
10. Document signé stocké
```

---

## 🆘 Problèmes Courants

### "Template not found"
→ Vérifiez le chemin : `storage/app/templates/contracts/service.docx`

### Variables non remplacées (${variable} reste)
→ Vérifiez l'orthographe exacte de la variable

### PDF mal formaté
→ Utilisez des polices standards (Arial, Times New Roman)

### Images manquantes dans PDF
→ Les images doivent être dans l'en-tête/pied de page Word

### Caractères spéciaux cassés (é, à, ç...)
→ Enregistrez le template Word en UTF-8

---

## 📚 Ressources

- **PHPWord Documentation** : https://phpword.readthedocs.io
- **Template Variables** : Voir `ContractGeneratorService::getAvailableVariables()`

---

## 🎯 Prochaine Étape

Une fois votre template créé et testé :

1. ✅ Intégrez dans le `ContractController`
2. ✅ Testez depuis l'interface frontend
3. ✅ [OPTIONNEL] Envoyez à DocuSeal pour signature

**Votre système de génération automatique est prêt !** 🎉
