# Architecture OOP - ELI VOYAGES Connect

## ✅ Améliorations Complétées

### 1. Login par Client Code (ELV-YYYY-###)

**Avant :**
```php
// Login uniquement par email
'email' => 'client@example.com'
```

**Après :**
```php
// Login par client_code OU email
'login' => 'ELV-2025-001'  // ou 'client@example.com'
```

**Fichiers modifiés :**
- `app/Models/Client.php` : Format ELV-YYYY-### au lieu de EV-YYYY-####
- `app/Http/Requests/Auth/LoginRequest.php` : Validation regex + détection automatique
- `resources/js/Pages/Auth/Login.vue` : Champ "Code client ou Email"

---

### 2. Services OOP (Logique Métier)

#### ClientService
```php
use App\Services\ClientService;

$clientService = app(ClientService::class);

// Créer client avec compte utilisateur
$client = $clientService->createClientWithUser($clientData, $userData);

// Trouver par code ou email
$client = $clientService->findByClientCodeOrEmail('ELV-2025-001');

// Statistiques client
$stats = $clientService->getClientStats($clientId);
// ['total_dossiers' => 3, 'active_dossiers' => 1, 'completed_dossiers' => 2, 'pending_documents' => 5]
```

#### DossierService
```php
use App\Services\DossierService;

$dossierService = app(DossierService::class);

// Créer dossier
$dossier = $dossierService->createDossier($clientId, $packageId, ['title' => 'Visa France']);

// Changer statut (avec log automatique)
$dossierService->updateStatus($dossierId, 'approuve', 'Documents validés');

// Progression
$progress = $dossierService->getProgress($dossierId);
// ['percentage' => 75, 'uploaded' => 3, 'required' => 4]

// Vérifier si complet
$isComplete = $dossierService->hasAllRequiredDocuments($dossierId);
```

#### DocumentService (existant)
```php
use App\Services\DocumentService;

$documentService = app(DocumentService::class);

// Upload document
$document = $documentService->uploadDocument($dossierId, $file, 'passeport');

// Changer statut
$documentService->updateStatus($documentId, 'approuve', 'Document conforme');
```

---

### 3. Controllers Refactorés

**Avant (logique dans Controller) :**
```php
class DossierController extends Controller
{
    public function show(Dossier $dossier)
    {
        $dossier->load(['client']);
        $documents = $dossier->documents()->with('uploader')->latest()->get();
        $activities = activity()->forSubject($dossier)->latest()->take(20)->get();
        // ...
    }
}
```

**Après (utilise Service) :**
```php
class DossierController extends Controller
{
    public function __construct(protected DossierService $dossierService) {}
    
    public function show(Dossier $dossier)
    {
        // Service gère la complexité
        $dossier = $this->dossierService->getDossierWithRelations($dossier->id);
        $progress = $this->dossierService->getProgress($dossier->id);
        
        return inertia('Dossiers/Show', compact('dossier', 'progress'));
    }
}
```

---

### 4. Relations et Scopes Models

#### Client Model

**Relations :**
```php
$client = Client::find(1);

// Relations
$client->dossiers;           // HasMany
$client->user;               // BelongsTo (via email)
$client->latestDossier;      // HasOne (dernier)
$client->activeDossier;      // HasOne (actif)
```

**Scopes :**
```php
// Clients avec statistiques
Client::withStats()->get();

// Clients avec dossiers actifs
Client::hasActiveDossiers()->get();
```

**Accessors :**
```php
$client->full_name;          // "Jean Dupont"
$client->formatted_phone;    // "06 12 34 56 78"
```

**Méthodes utiles :**
```php
$client->hasActiveDossiers(); // bool
```

#### Dossier Model

**Scopes :**
```php
// Dossiers actifs
Dossier::active()->get();

// Dossiers terminés
Dossier::completed()->get();

// Dossiers archivés
Dossier::archived()->get();

// Par statut
Dossier::byStatus('verification')->get();

// Assignés à un utilisateur
Dossier::assignedTo($userId)->get();

// Recherche
Dossier::search('Jean')->get();

// Avec toutes les relations
Dossier::withAllRelations()->get();
```

**Accessors :**
```php
$dossier->progress_percentage;    // 75
$dossier->status_badge_color;     // "green"
$dossier->status_label;           // "En cours"
```

**Méthodes utiles :**
```php
$dossier->isActive();                  // bool
$dossier->isCompleted();               // bool
$dossier->hasAllRequiredDocuments();   // bool
```

#### User Model

**Relations :**
```php
$user = auth()->user();

$user->client;           // HasOne (si Client role)
$user->dossiers();       // Via client
```

**Méthodes utiles :**
```php
$user->isClient();               // bool
$user->getActiveDossier();       // Dossier|null
```

---

### 5. DTOs (Data Transfer Objects)

#### ClientData
```php
use App\DTOs\ClientData;

// Depuis Request
$clientData = ClientData::fromRequest($request);

// Depuis Array
$clientData = ClientData::fromArray([
    'email' => 'client@example.com',
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    // ...
]);

// Méthodes
$clientData->fullName();           // "Jean Dupont"
$clientData->hasValidPassport();   // bool
$clientData->isComplete();         // bool

// Conversion
$array = $clientData->toArray();   // Pour DB
```

#### DossierData
```php
use App\DTOs\DossierData;

// Nouveau dossier
$dossierData = DossierData::forNewDossier($clientId, $packageId, [
    'title' => 'Visa France',
    'assigned_to' => $agentId,
]);

// Méthodes
$dossierData->canBeSubmitted();    // bool
$dossierData->isFinal();           // bool

// Conversion
$array = $dossierData->toArray();  // Pour DB
```

#### DocumentData
```php
use App\DTOs\DocumentData;

// Depuis fichier uploadé
$documentData = DocumentData::fromUploadedFile($file, $dossierId, 'passeport');

// Méthodes
$documentData->hasFile();          // bool
$documentData->isApproved();       // bool
$documentData->isPdf();            // bool
$documentData->isImage();          // bool
$documentData->getSizeInMB();      // 2.5

// Avec path
$documentData = $documentData->withFichier('path/to/file.pdf');
```

---

### 6. HandleInertiaRequests Amélioré

**Avant :**
```php
$client = \App\Models\Client::where('email', $user->email)->first();
$clientData = ['id' => $client->id, 'dossier_id' => $client->dossiers()->latest()->first()?->id];
```

**Après (utilise User model methods) :**
```php
if ($user->isClient()) {
    $client = $user->client;
    $activeDossier = $user->getActiveDossier();
    
    $clientData = [
        'id' => $client->id,
        'client_code' => $client->client_code,
        'full_name' => $client->full_name,
        'dossier_id' => $activeDossier?->id,
        'has_active_dossier' => $client->hasActiveDossiers(),
    ];
}
```

**Frontend (Inertia) :**
```javascript
// userStore.js
const user = computed(() => page.props.auth.user)
const clientCode = computed(() => user.value?.client?.client_code)  // "ELV-2025-001"
const fullName = computed(() => user.value?.client?.full_name)      // "Jean Dupont"
```

---

## 📋 Exemples d'Utilisation

### Création de Client avec Dossier (Service)

```php
use App\Services\ClientService;
use App\Services\DossierService;
use App\DTOs\ClientData;

$clientService = app(ClientService::class);
$dossierService = app(DossierService::class);

// 1. Créer client
$clientData = ClientData::fromRequest($request);
$client = $clientService->createClientWithUser(
    $clientData->toArray(),
    ['password' => 'secret123']
);

// 2. Créer dossier
$dossier = $dossierService->createDossier(
    $client->id,
    $packageId,
    ['title' => 'Visa Schengen', 'assigned_to' => $agentId]
);

// 3. Vérifier progression
$progress = $dossierService->getProgress($dossier->id);

return response()->json([
    'client_code' => $client->client_code,  // ELV-2025-001
    'dossier_ref' => $dossier->reference,   // ELI-2025-123456
    'progress' => $progress['percentage'],  // 0
]);
```

### Query avec Scopes

```php
// Dashboard Admin : Dossiers actifs avec stats clients
$dossiers = Dossier::active()
    ->withAllRelations()
    ->whereHas('client', fn($q) => $q->withStats())
    ->paginate(15);

// Recherche clients avec dossiers actifs
$clients = Client::hasActiveDossiers()
    ->withStats()
    ->search($request->search)
    ->get();
```

### Login Client

```php
// Frontend (Login.vue)
<input v-model="form.login" placeholder="ELV-2025-001 ou email@example.com" />

// Backend (LoginRequest.php)
// Détecte automatiquement si client_code (ELV-YYYY-###) ou email
// Trouve le client et authentifie via User->email
```

---

## 🎯 Bénéfices

1. **Code Maintenable** : Logique métier dans Services, pas dans Controllers
2. **Réutilisable** : Services injectables partout (Controllers, Commands, Jobs)
3. **Testable** : Mock Services facilement pour tests unitaires
4. **Type-Safe** : DTOs avec readonly properties et validation
5. **Queryable** : Scopes réutilisables pour queries complexes
6. **Extensible** : Ajout de features sans toucher aux Controllers

---

## 🔄 Migration des Controllers Existants

Pour refactorer un Controller :

```php
// 1. Injecter le Service
public function __construct(protected DossierService $dossierService) {}

// 2. Remplacer la logique directe
// Avant:
$dossier = Dossier::with(['client', 'documents'])->find($id);

// Après:
$dossier = $this->dossierService->getDossierWithRelations($id);

// 3. Utiliser les méthodes du Service
// Avant:
$dossier->update(['statut' => 'approuve']);
activity()->performedOn($dossier)->log('Approuvé');

// Après:
$this->dossierService->updateStatus($dossier->id, 'approuve', 'Validation complète');
```

---

## ✅ Checklist Complète

- [x] Client code format ELV-YYYY-###
- [x] Login par client_code ou email
- [x] ClientService créé et fonctionnel
- [x] DossierService créé et fonctionnel
- [x] Controllers refactorés (DossierController, DashboardController)
- [x] Client model : scopes + accessors + relations
- [x] Dossier model : scopes + accessors + méthodes
- [x] User model : relations client + méthodes helpers
- [x] DTOs créés : ClientData, DossierData, DocumentData
- [x] HandleInertiaRequests utilise User methods
- [x] Frontend compilé et prêt

---

## 🚀 Prochaines Étapes Recommandées

1. **Tests Unitaires** : Créer tests pour Services
2. **Repository Pattern** : Abstraire accès DB si besoin
3. **Events & Listeners** : Remplacer activity logs directs
4. **API Resources** : Transformer responses avec JsonResource
5. **Validation Rules** : Créer Rule classes réutilisables
