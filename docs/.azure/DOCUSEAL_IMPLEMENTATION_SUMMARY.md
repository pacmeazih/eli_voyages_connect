# 📋 DocuSeal Implementation - Summary

## ✅ What Has Been Done

### 1. API Configuration ✅
- **API Key**: `NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg`
- **API URL**: `https://api.docuseal.com`
- **Configuration**: Updated in `.env.example` and `config/services.php`

### 2. Backend Code Created ✅

| File | Status | Description |
|------|--------|-------------|
| `app/Services/DocuSealService.php` | ✅ Complete | Full API integration service with all methods |
| `app/Models/ContractSignature.php` | ✅ Complete | Model for tracking individual signatures |
| `app/Http/Controllers/ContractControllerExample.php` | ✅ Complete | Complete controller example with all endpoints |
| `database/migrations/2025_11_10_000001_add_docuseal_fields_to_contracts.php` | ✅ Complete | Database schema for DocuSeal integration |
| `routes/docuseal_routes_example.php` | ✅ Complete | All required routes (web + API + webhook) |

### 3. Frontend Code Created ✅

| File | Status | Description |
|------|--------|-------------|
| `resources/js/Pages/Contracts/GenerateEnhanced.vue` | ✅ Complete | 3-step contract generation wizard |
| `resources/js/Pages/Contracts/Sign.vue` | ✅ Complete | E-signature page with DocuSeal iframe |

### 4. Documentation Created ✅

| File | Status | Description |
|------|--------|-------------|
| `.azure/DOCUSEAL_SETUP_GUIDE.md` | ✅ Complete | Comprehensive API documentation with examples |
| `.azure/DOCUSEAL_QUICKSTART.md` | ✅ Complete | Quick 5-minute setup guide |
| `.azure/IMPLEMENTATION_CHECKLIST.md` | ✅ Updated | Full backend integration checklist |

---

## 🔧 What You Need to Do

### Immediate Steps (10 minutes)

#### 1. Update `.env` File
```env
DOCUSEAL_API_KEY=NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
DOCUSEAL_API_URL=https://api.docuseal.com
```

#### 2. Run Migrations
```bash
php artisan migrate
```

This creates:
- `docuseal_submission_id` column in `contracts`
- `docuseal_template_id` column in `contracts`
- `docuseal_signers` JSON column in `contracts`
- `sent_for_signature_at` timestamp in `contracts`
- `completed_at` timestamp in `contracts`
- `signed_document_path` column in `contracts`
- New `contract_signatures` table

#### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

### Backend Integration (30-45 minutes)

#### 1. Update Contract Model (`app/Models/Contract.php`)

Add these properties and methods:

```php
protected $casts = [
    'docuseal_signers' => 'array',
    'sent_for_signature_at' => 'datetime',
    'completed_at' => 'datetime',
];

public function signatures()
{
    return $this->hasMany(ContractSignature::class);
}
```

#### 2. Add Routes

**In `routes/web.php`**:
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/contracts/generate/{dossier}', [ContractController::class, 'generate'])->name('contracts.generate');
    Route::post('/contracts/generate/{dossier}', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::post('/contracts/{contract}/remind', [ContractController::class, 'remind'])->name('contracts.remind');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
});
```

**In `routes/api.php`**:
```php
Route::post('/webhooks/docuseal', [ContractController::class, 'webhook']);
```

**In `app/Http/Middleware/VerifyCsrfToken.php`**:
```php
protected $except = [
    'api/webhooks/docuseal',
];
```

#### 3. Update ContractController

Copy these methods from `ContractControllerExample.php` to your `ContractController.php`:

**Essential Methods**:
1. `store()` - Create and send contract for signature
2. `show()` - Display contract with signature status
3. `remind()` - Send reminder emails
4. `download()` - Download signed PDF
5. `webhook()` - Handle DocuSeal webhook events

**Helper Methods**:
6. `handleFormCompleted()` - Update signature when form completed
7. `handleFormDeclined()` - Handle declined signatures
8. `handleSubmissionCompleted()` - Store signed document when all parties signed
9. `prepareFields()` - Prepare contract fields with values
10. `getEmbedUrl()` - Get DocuSeal iframe URL
11. `syncSubmissionStatus()` - Sync status from DocuSeal

#### 4. Configure Webhook

1. Go to: https://console.docuseal.com/webhooks
2. Add webhook URL: `https://yourdomain.com/api/webhooks/docuseal`
3. Select events:
   - ✅ `form.viewed`
   - ✅ `form.started`
   - ✅ `form.completed`
   - ✅ `form.declined`
   - ✅ `submission.completed`
   - ✅ `submission.expired`
4. Save configuration

### Testing (15 minutes)

#### 1. Test API Connection
```bash
php artisan tinker
```

```php
$service = app(\App\Services\DocuSealService::class);
$service->testConnection(); // Should return true
```

#### 2. Create a Template in DocuSeal

1. Go to: https://console.docuseal.com/templates
2. Upload a PDF or create from HTML
3. Add signature fields
4. Note the `template_id` (you'll need this)

#### 3. Test Contract Creation

Navigate to: `https://yourdomain.com/contracts/generate/{dossier_id}`

Fill in the form and click "Generate Contract"

---

## 📊 API Workflow

### Contract Generation Flow

```
1. User fills contract form → GenerateEnhanced.vue
   ↓
2. POST /contracts/generate/{dossier}
   ↓
3. ContractController@store()
   ↓
4. DocuSealService->createSubmission()
   ↓
5. DocuSeal API (POST /submissions)
   ↓
6. Store submission_id in contracts table
   ↓
7. Create contract_signatures records
   ↓
8. Send emails to signers (by DocuSeal)
```

### Signature Flow

```
1. Signer receives email from DocuSeal
   ↓
2. Clicks link → Opens signing form
   ↓
3. Signs document
   ↓
4. DocuSeal sends webhook → form.completed
   ↓
5. ContractController@webhook()
   ↓
6. Update contract_signatures.status = 'signed'
   ↓
7. If all signed → submission.completed webhook
   ↓
8. Download and store signed PDF
   ↓
9. Update contract.status = 'completed'
```

---

## 🎯 Key DocuSeal API Methods

### DocuSealService Methods Available

| Method | Purpose | Usage |
|--------|---------|-------|
| `createSubmission()` | Send contract for signature | Main method to initiate signing |
| `getSubmission()` | Get submission status | Check progress |
| `listSubmissions()` | List all submissions | Admin view |
| `getSubmissionDocuments()` | Get signed PDFs | Download signed documents |
| `downloadDocument()` | Download file from URL | Store locally |
| `updateSubmitter()` | Update/resend to signer | Reminders, updates |
| `getSubmitter()` | Get signer details | Check individual status |
| `handleWebhook()` | Process webhook events | Real-time updates |
| `testConnection()` | Test API connection | Troubleshooting |

---

## 📝 Example Usage

### Send Contract for Signature

```php
use App\Services\DocuSealService;

$docuSealService = app(DocuSealService::class);

$response = $docuSealService->createSubmission(
    templateId: 123456, // Your DocuSeal template ID
    submitters: [
        [
            'role' => 'Client',
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'phone' => '+33612345678',
            'external_id' => 'CLIENT_123',
            'metadata' => [
                'client_id' => 123,
                'dossier_id' => 456,
            ],
            'fields' => [
                ['name' => 'Client Name', 'default_value' => 'Jean Dupont', 'readonly' => true],
                ['name' => 'Package', 'default_value' => 'Hajj 2025', 'readonly' => true],
                ['name' => 'Amount', 'default_value' => '8500 €', 'readonly' => true],
            ],
        ],
        [
            'role' => 'Guarantor',
            'name' => 'Marie Dupont',
            'email' => 'marie@example.com',
            'order' => 1, // Signs after client
        ],
    ],
    options: [
        'send_email' => true,
        'order' => 'preserved', // Client signs first, then guarantor
        'expire_at' => now()->addDays(30)->format('Y-m-d H:i:s') . ' UTC',
        'completed_redirect_url' => route('contracts.completed', $contract),
        'message' => [
            'subject' => 'Signature requise - Contrat Hajj 2025',
            'body' => 'Veuillez signer votre contrat...',
        ],
    ]
);

// Response contains submitters with embed_src URLs
// [
//   {
//     "id": 1,
//     "submission_id": 12,
//     "email": "jean@example.com",
//     "slug": "ABC123",
//     "status": "sent",
//     "embed_src": "https://docuseal.com/s/ABC123"
//   }
// ]
```

### Check Signature Status

```php
$submission = $docuSealService->getSubmission($submissionId);

echo "Status: " . $submission['status']; // pending, completed, declined, expired

foreach ($submission['submitters'] as $submitter) {
    echo $submitter['name'] . ": " . $submitter['status'];
    // Jean Dupont: completed
    // Marie Dupont: pending
}
```

### Download Signed Document

```php
$documents = $docuSealService->getSubmissionDocuments($submissionId);

foreach ($documents['documents'] as $document) {
    $content = $docuSealService->downloadDocument($document['url']);
    Storage::put('contracts/signed_' . time() . '.pdf', $content);
}
```

---

## 🚨 Common Issues & Solutions

### Issue: "API key not found"
**Solution**: Make sure you ran `php artisan config:clear` after updating `.env`

### Issue: "Webhook not receiving events"
**Solution**: 
1. Check webhook URL is accessible (HTTPS required in production)
2. Verify URL in DocuSeal console matches your route
3. Check `storage/logs/laravel.log` for webhook errors

### Issue: "Template not found"
**Solution**: 
1. Create template in DocuSeal console first
2. Get template ID from DocuSeal dashboard
3. Pass correct template_id to createSubmission()

### Issue: "Signature iframe not loading"
**Solution**:
1. Check `embed_src` is being passed to Sign.vue
2. Verify user's email matches one of the submitters
3. Check browser console for CORS errors

---

## 📞 Support & Resources

- **DocuSeal Documentation**: https://www.docuseal.com/docs/api
- **DocuSeal Console**: https://console.docuseal.com
- **Support Email**: support@docuseal.co
- **Status Page**: https://status.docuseal.co

---

## ✅ Implementation Checklist

- [ ] Add API key to `.env`
- [ ] Run database migrations
- [ ] Clear configuration cache
- [ ] Update Contract model
- [ ] Add routes (web + API)
- [ ] Exclude webhook from CSRF
- [ ] Copy controller methods
- [ ] Create template in DocuSeal
- [ ] Configure webhook URL
- [ ] Test API connection
- [ ] Test contract creation
- [ ] Test signature flow
- [ ] Test webhook events
- [ ] Test document download
- [ ] Deploy to production
- [ ] Monitor logs for 24 hours

---

**Status**: 🟢 Ready for Implementation  
**Estimated Time**: 60-90 minutes  
**Priority**: High (Core feature)  
**Dependencies**: None (all files created)
