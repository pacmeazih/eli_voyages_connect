# ELI Voyages Connect - Implementation Checklist

## ✅ Frontend Complete (100%)

All frontend components, pages, and systems have been created and are ready for integration.

## 🔐 DocuSeal API Key Configured

**API Key**: `NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg`  
**Setup Guide**: See `.azure/DOCUSEAL_SETUP_GUIDE.md` for complete implementation instructions

---

## 📋 Backend Implementation Required

### 1. Routes & Controllers

#### Authentication & User Preferences
- [ ] `POST /preferences/language` - Update user language preference
- [ ] `POST /preferences/theme` - Update theme preference
- [ ] `PUT /password.update` - Change user password

#### Clients
- [ ] `GET /clients.index` - List all clients with filters
- [ ] `GET /clients.create` - Show create form (return consultants/agents lists)
- [ ] `POST /clients.store` - Create new client
- [ ] `GET /clients.show/{id}` - Show client details
- [ ] `GET /clients.edit/{id}` - Show edit form
- [ ] `PUT /clients.update/{id}` - Update client
- [ ] `DELETE /clients.destroy/{id}` - Delete client

#### Documents
- [ ] `GET /dossiers/{id}/documents` - List documents with pagination & filters
- [ ] `POST /documents.store` - Upload document (multipart/form-data)
- [ ] `DELETE /documents.destroy/{id}` - Delete document
- [ ] `GET /documents.download/{id}` - Download document

#### Contracts
- [ ] `GET /contracts.generate/{dossier}` - Show contract generation form
- [ ] `POST /contracts.generate/{dossier}` - Generate and save contract
- [ ] `POST /contracts.preview` - AJAX preview of contract
- [ ] `GET /contracts.show/{id}` - Show contract details
- [ ] `GET /contracts.docuseal-embed` - Get DocuSeal iframe URL
- [ ] `POST /contracts.send-for-signature/{id}` - Send to DocuSeal
- [ ] `POST /contracts.remind-signers/{id}` - Send reminder emails
- [ ] `GET /contracts.download/{id}` - Download signed PDF

#### Dossiers
- [ ] `GET /dossiers.show/{id}` - Return dossier with:
  - status_history (timestamps for each status)
  - documents array
  - contracts array
  - activities array (for timeline)

#### Dashboards
- [ ] `GET /dashboard` - Return role-specific data:
  - stats object (varies by role)
  - recentDossiers array
  - recentActivity array
  - upcomingAppointments array
  - pendingActions array

---

### 2. Database Migrations

#### Add Missing Columns
```sql
-- dossiers table
ALTER TABLE dossiers ADD COLUMN status_history JSON;
ALTER TABLE dossiers ADD COLUMN submitted_at TIMESTAMP NULL;
ALTER TABLE dossiers ADD COLUMN deadline TIMESTAMP NULL;

-- contracts table
ALTER TABLE contracts ADD COLUMN docuseal_submission_id VARCHAR(255) NULL;
ALTER TABLE contracts ADD COLUMN docuseal_template_id VARCHAR(255) NULL;
ALTER TABLE contracts ADD COLUMN signed_pdf_url VARCHAR(255) NULL;
ALTER TABLE contracts ADD COLUMN signed_at TIMESTAMP NULL;

-- users table (if not exists)
ALTER TABLE users ADD COLUMN language VARCHAR(2) DEFAULT 'fr';
ALTER TABLE users ADD COLUMN theme VARCHAR(10) DEFAULT 'light';

-- Add activities table for timeline
CREATE TABLE activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    metadata JSON,
    user_id BIGINT UNSIGNED,
    dossier_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE
);

-- Add signatures table for contracts
CREATE TABLE contract_signatures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contract_id BIGINT UNSIGNED NOT NULL,
    signer_name VARCHAR(255) NOT NULL,
    signer_email VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    signed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
);
```

---

### 3. Models & Relationships

#### Update Models

**User.php**
```php
protected $fillable = [..., 'language', 'theme'];

public function activities() {
    return $this->hasMany(Activity::class);
}
```

**Dossier.php**
```php
protected $casts = [
    'status_history' => 'array',
];

public function activities() {
    return $this->hasMany(Activity::class);
}

public function updateStatus($newStatus) {
    $history = $this->status_history ?? [];
    $history[$newStatus] = now()->toIso8601String();
    $this->update([
        'status' => $newStatus,
        'status_history' => $history
    ]);
    
    // Create activity
    $this->activities()->create([
        'type' => 'status_changed',
        'title' => 'Statut modifié',
        'metadata' => [
            'old_status' => $this->getOriginal('status'),
            'new_status' => $newStatus
        ],
        'user_id' => auth()->id()
    ]);
}
```

**Contract.php**
```php
public function signatures() {
    return $this->hasMany(ContractSignature::class);
}

public function sendForSignature() {
    // DocuSeal integration logic
}
```

**Create Activity.php Model**
```php
class Activity extends Model {
    protected $fillable = [
        'type', 'title', 'description', 
        'metadata', 'user_id', 'dossier_id'
    ];
    
    protected $casts = [
        'metadata' => 'array'
    ];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function dossier() {
        return $this->belongsTo(Dossier::class);
    }
}
```

---

### 4. Services

#### DocuSealService.php
```php
class DocuSealService {
    public function createSubmission($contract) {
        // Call DocuSeal API to create submission
        // Return submission_id and embed_url
    }
    
    public function getEmbedUrl($submissionId) {
        // Get DocuSeal embed URL for iframe
    }
    
    public function sendReminder($submissionId) {
        // Send reminder via DocuSeal API
    }
    
    public function handleWebhook($payload) {
        // Handle DocuSeal webhook (signature completion)
    }
}
```

#### ActivityLogger.php
```php
class ActivityLogger {
    public static function log($dossierId, $type, $title, $metadata = []) {
        Activity::create([
            'type' => $type,
            'title' => $title,
            'metadata' => $metadata,
            'user_id' => auth()->id(),
            'dossier_id' => $dossierId
        ]);
    }
}
```

---

### 5. Form Requests

#### StoreClientRequest.php
```php
public function rules() {
    return [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'birth_date' => 'required|date',
        'birth_place' => 'nullable|string|max:255',
        'nationality' => 'required|string|max:3',
        'gender' => 'nullable|string|in:M,F,other',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:255',
        'postal_code' => 'required|string|max:10',
        'city' => 'required|string|max:255',
        'country' => 'required|string|max:3',
        'id_type' => 'required|string|in:passport,id_card,residence_permit',
        'id_number' => 'required|string|max:50',
        'id_issue_date' => 'nullable|date',
        'id_expiry_date' => 'nullable|date|after:today',
        'consultant_id' => 'nullable|exists:users,id',
        'agent_id' => 'nullable|exists:users,id',
        'source' => 'nullable|string',
        'notes' => 'nullable|string'
    ];
}
```

#### StoreDocumentRequest.php
```php
public function rules() {
    return [
        'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        'type' => 'required|string',
        'dossier_id' => 'required|exists:dossiers,id'
    ];
}
```

#### GenerateContractRequest.php
```php
public function rules() {
    return [
        'contract_type' => 'required|string',
        'language' => 'required|in:fr,en',
        'package_id' => 'nullable|exists:packages,id',
        'variables' => 'required|array',
        'notes' => 'nullable|string'
    ];
}
```

---

### 6. Policies

Update existing policies to use new permission checks:

```php
// DossierPolicy.php
public function view(User $user, Dossier $dossier) {
    return $user->hasRole('SuperAdmin') 
        || $user->id === $dossier->consultant_id
        || $user->id === $dossier->agent_id
        || $user->id === $dossier->client_id;
}

public function update(User $user, Dossier $dossier) {
    return $user->can('dossiers.edit')
        && ($user->hasRole(['SuperAdmin', 'Consultant']) 
            || $user->id === $dossier->agent_id);
}
```

---

### 7. Seeder Updates

#### Add Test Data
```php
// ClientSeeder.php - Add clients with complete data
// DossierSeeder.php - Add dossiers with status_history
// ActivitySeeder.php - Add sample activities for timeline
// ContractSeeder.php - Add contracts with signatures
```

---

### 8. API Documentation

Document all endpoints with:
- Request parameters
- Response format
- Error codes
- Authentication requirements
- Example requests/responses

---

## 🧪 Testing Checklist

### Unit Tests
- [ ] Test all Pinia stores (user, preferences, ui)
- [ ] Test composables (useTranslation, useDashboard)
- [ ] Test ActivityLogger service
- [ ] Test DocuSealService

### Component Tests
- [ ] Test FormField validation states
- [ ] Test StatusBadge color rendering
- [ ] Test StatusStepper progress calculation
- [ ] Test ActivityTimeline formatting
- [ ] Test file upload in Documents/Index

### Integration Tests
- [ ] Test client creation flow (4 steps)
- [ ] Test contract generation (3 steps)
- [ ] Test document upload with drag & drop
- [ ] Test status changes and activity logging
- [ ] Test role-based dashboard data

### E2E Tests
- [ ] Complete client onboarding workflow
- [ ] Complete contract generation and signing
- [ ] Document management workflow
- [ ] Dossier lifecycle (draft → closed)
- [ ] Multi-language switching
- [ ] Dark mode persistence

---

## 🚀 Deployment Checklist

### Pre-Deployment
- [ ] Run `npm run build` for production assets
- [ ] Clear all caches (`php artisan cache:clear`)
- [ ] Run migrations on production database
- [ ] Set up DocuSeal API keys in .env
- [ ] Configure CORS settings
- [ ] Test email sending (contract notifications)

### Post-Deployment
- [ ] Verify all assets load correctly
- [ ] Test critical user workflows
- [ ] Monitor error logs for 24 hours
- [ ] Verify DocuSeal webhook reception
- [ ] Test on multiple browsers and devices
- [ ] Performance audit (Lighthouse)

---

## 📝 Documentation Updates

### Developer Docs
- [ ] Component API documentation
- [ ] Props and events for each component
- [ ] Composable usage examples
- [ ] Store actions and getters
- [ ] Backend API endpoints

### User Guides
- [ ] SuperAdmin guide (user management, system overview)
- [ ] Consultant guide (dossier validation, client management)
- [ ] Agent guide (daily workflows, document handling)
- [ ] Client guide (tracking dossier, uploading documents)
- [ ] Guarantor guide (signing documents)

### Admin Documentation
- [ ] Deployment instructions
- [ ] Environment configuration
- [ ] Troubleshooting guide
- [ ] Backup and recovery procedures

---

## 🎯 Success Metrics

Track these metrics post-deployment:

- **User Adoption**: % of users using new dashboards
- **Task Completion**: Time to complete key workflows
- **Error Rate**: Frontend and backend errors per day
- **User Satisfaction**: Feedback scores from each role
- **Performance**: Page load times, API response times
- **Contract Signing**: % of contracts signed within 48h

---

## 🆘 Support Resources

- **Frontend Issues**: Check browser console, verify props
- **Backend Issues**: Check Laravel logs, verify permissions
- **DocuSeal Issues**: Check webhook logs, API responses
- **Translation Issues**: Verify lang files, check useTranslation
- **Style Issues**: Check Tailwind compilation, dark mode classes

---

**Last Updated**: November 10, 2025
**Status**: ✅ Frontend Complete - Awaiting Backend Integration
