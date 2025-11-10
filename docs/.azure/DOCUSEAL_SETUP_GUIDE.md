# DocuSeal Integration Setup Guide

## 🔐 API Key Configuration

Your DocuSeal API key has been configured. Follow these steps to complete the setup:

### Step 1: Update Your `.env` File

⚠️ **IMPORTANT**: Never commit your `.env` file to version control!

1. Open your `.env` file (if it doesn't exist, copy from `.env.example`)
2. Add or update the DocuSeal configuration:

```env
# DocuSeal Configuration (required for e-signature)
DOCUSEAL_API_KEY=NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
DOCUSEAL_API_URL=https://api.docuseal.co
```

3. Save the file and restart your Laravel server:
```bash
php artisan config:clear
php artisan cache:clear
```

### Step 2: Verify Configuration

Test that the API key is loaded correctly:

```bash
php artisan tinker
```

Then run:
```php
config('services.docuseal.api_key')
// Should output: "NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg"
```

## 📚 DocuSeal API Documentation

### Base URL
```
https://api.docuseal.com
```

### Authentication

All API requests require the API key in the `X-Auth-Token` header:

```http
X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
```

### Key Endpoints for ELI-Voyages

#### 1. **Create Submission** (Send contract for signature)
```http
POST /submissions
Content-Type: application/json
X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
```

**Request Body**:
```json
{
  "template_id": 1000001,
  "send_email": true,
  "send_sms": false,
  "order": "preserved",
  "completed_redirect_url": "https://yourdomain.com/contracts/completed",
  "bcc_completed": "documents@eli-voyages.com",
  "expire_at": "2025-12-31 23:59:59 UTC",
  "message": {
    "subject": "Signature requise pour votre contrat de voyage",
    "body": "Bonjour {{submitter.name}},\n\nVeuillez signer votre contrat en cliquant sur le lien suivant:\n{{submitter.link}}\n\nCordialement,\nÉquipe ELI-Voyages"
  },
  "submitters": [
    {
      "role": "Client",
      "name": "Jean Dupont",
      "email": "jean.dupont@example.com",
      "phone": "+33612345678",
      "external_id": "CLIENT_123",
      "metadata": {
        "client_id": 123,
        "dossier_id": 456,
        "package_name": "Hajj 2025"
      },
      "fields": [
        {
          "name": "Client Name",
          "default_value": "Jean Dupont",
          "readonly": false
        },
        {
          "name": "Package",
          "default_value": "Hajj Premium 2025",
          "readonly": true
        },
        {
          "name": "Total Amount",
          "default_value": "8500 €",
          "readonly": true
        }
      ]
    },
    {
      "role": "Guarantor",
      "name": "Marie Dupont",
      "email": "marie.dupont@example.com",
      "phone": "+33623456789",
      "external_id": "GUARANTOR_789",
      "order": 1
    }
  ]
}
```

**Response**:
```json
[
  {
    "id": 1,
    "submission_id": 12,
    "uuid": "884d545b-3396-49f1-8c07-05b8b2a78755",
    "email": "jean.dupont@example.com",
    "slug": "pAMimKcyrLjqVt",
    "sent_at": "2025-11-10T10:00:00Z",
    "status": "sent",
    "role": "Client",
    "embed_src": "https://docuseal.com/s/pAMimKcyrLjqVt"
  }
]
```

#### 2. **Get Submission Status** (Check signature progress)
```http
GET /submissions/{id}
X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
```

**Response**:
```json
{
  "id": 12,
  "status": "completed",
  "completed_at": "2025-11-10T15:30:00Z",
  "audit_log_url": "https://docuseal.com/blobs/proxy/hash/audit-log.pdf",
  "combined_document_url": "https://docuseal.com/blobs/proxy/hash/document.pdf",
  "submitters": [
    {
      "id": 1,
      "email": "jean.dupont@example.com",
      "name": "Jean Dupont",
      "status": "completed",
      "completed_at": "2025-11-10T14:15:00Z",
      "role": "Client",
      "values": [
        {"field": "Client Name", "value": "Jean Dupont"},
        {"field": "Signature", "value": "https://docuseal.com/blobs/proxy/hash/signature.png"}
      ]
    }
  ]
}
```

#### 3. **Download Signed Documents**
```http
GET /submissions/{id}/documents
X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
```

**Response**:
```json
{
  "id": 12,
  "documents": [
    {
      "name": "Contrat_Hajj_2025_Jean_Dupont",
      "url": "https://docuseal.com/file/hash/signed-contract.pdf"
    }
  ]
}
```

#### 4. **Update Submitter** (Pre-fill or resend)
```http
PUT /submitters/{id}
X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
```

**Request Body**:
```json
{
  "email": "newemail@example.com",
  "send_email": true,
  "values": {
    "Client Name": "Jean Dupont Updated",
    "Phone Number": "+33612345678"
  }
}
```

#### 5. **List Submissions** (With filters)
```http
GET /submissions?template_id=123&status=pending&limit=50
X-Auth-Token: NGRBMcmw27kEpsrAvhSV4xPxa1imG3UwTd5MFJYgrcg
```

**Query Parameters**:
- `template_id` - Filter by template
- `status` - Filter by status: `pending`, `completed`, `declined`, `expired`
- `q` - Search by name/email
- `limit` - Number of results (max 100)
- `after` - Pagination cursor

### Submission Statuses

| Status | Description |
|--------|-------------|
| `pending` | En attente de signatures |
| `completed` | Toutes les signatures complétées |
| `declined` | Refusé par un signataire |
| `expired` | Expiré (dépassé expire_at) |

### Submitter Statuses

| Status | Description |
|--------|-------------|
| `awaiting` | En attente (ordre préservé) |
| `sent` | Email envoyé |
| `opened` | Formulaire ouvert |
| `completed` | Signature complétée |
| `declined` | Signature refusée |

### Webhooks (Real-time notifications)

**Configure webhook URL in DocuSeal dashboard**:
```
URL: https://yourdomain.com/api/webhooks/docuseal
Secret: [Generate and store in .env as DOCUSEAL_WEBHOOK_SECRET]
```

**Available Events**:

1. **Form Events** (Per submitter):
   - `form.viewed` - Formulaire consulté
   - `form.started` - Remplissage commencé
   - `form.completed` - Signature complétée
   - `form.declined` - Signature refusée

2. **Submission Events** (Global):
   - `submission.created` - Submission créée
   - `submission.completed` - Toutes signatures complétées
   - `submission.expired` - Submission expirée
   - `submission.archived` - Submission archivée

**Webhook Payload Example** (`form.completed`):
```json
{
  "event_type": "form.completed",
  "timestamp": "2025-11-10T15:30:00Z",
  "data": {
    "id": 1,
    "submission_id": 12,
    "email": "jean.dupont@example.com",
    "name": "Jean Dupont",
    "role": "Client",
    "status": "completed",
    "completed_at": "2025-11-10T15:30:00Z",
    "external_id": "CLIENT_123",
    "metadata": {
      "client_id": 123,
      "dossier_id": 456,
      "package_name": "Hajj 2025"
    },
    "values": [
      {"field": "Client Name", "value": "Jean Dupont"},
      {"field": "Signature", "value": "https://docuseal.com/blobs/proxy/hash/signature.png"}
    ],
    "documents": [
      {
        "name": "Contrat_Hajj_2025",
        "url": "https://docuseal.com/file/hash/signed.pdf"
      }
    ],
    "audit_log_url": "https://docuseal.com/blobs/proxy/hash/audit.pdf",
    "submission": {
      "id": 12,
      "status": "pending",
      "audit_log_url": null
    }
  }
}
```

## 🔨 Implementation Checklist

### Phase 1: Enhanced DocuSealService (Backend)

- [ ] Install Guzzle HTTP client: `composer require guzzlehttp/guzzle`
- [ ] Implement `DocuSealService.php` methods:
  - [ ] `createSubmission()` - Create document submission
  - [ ] `getSubmission()` - Get submission status
  - [ ] `listSubmissions()` - List all submissions
  - [ ] `downloadDocument()` - Download signed PDF
  - [ ] `handleWebhook()` - Process webhook events
- [ ] Add error handling and logging
- [ ] Create unit tests for DocuSealService

### Phase 2: Database Schema

- [ ] Add DocuSeal columns to `contracts` table:
```php
$table->string('docuseal_submission_id')->nullable();
$table->string('docuseal_template_id')->nullable();
$table->json('docuseal_signers')->nullable();
$table->timestamp('sent_for_signature_at')->nullable();
$table->timestamp('completed_at')->nullable();
```

- [ ] Create `contract_signatures` table:
```php
Schema::create('contract_signatures', function (Blueprint $table) {
    $table->id();
    $table->foreignId('contract_id')->constrained()->onDelete('cascade');
    $table->string('signer_email');
    $table->string('signer_name');
    $table->string('signer_role'); // 'client', 'guarantor', 'agent'
    $table->enum('status', ['pending', 'viewed', 'signed', 'declined'])->default('pending');
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('viewed_at')->nullable();
    $table->timestamp('signed_at')->nullable();
    $table->string('ip_address')->nullable();
    $table->timestamps();
});
```

### Phase 3: Contract Model Updates

- [ ] Add relationships to `Contract` model:
```php
public function signatures()
{
    return $this->hasMany(ContractSignature::class);
}

public function sendForSignature(array $signers)
{
    // Implementation in ContractController
}
```

### Phase 4: API Routes

- [ ] `POST /api/contracts/{id}/send-for-signature` - Send contract via DocuSeal
- [ ] `POST /api/contracts/{id}/remind-signers` - Send reminder to pending signers
- [ ] `GET /api/contracts/{id}/download` - Download signed PDF
- [ ] `POST /api/webhooks/docuseal` - Webhook endpoint (exclude from CSRF)

### Phase 5: Frontend Integration

✅ Already implemented in `Pages/Contracts/Sign.vue`:
- DocuSeal iframe embedding
- Signature status tracking
- Multi-signer support
- Event listener for completion

### Phase 6: Webhook Configuration

- [ ] Configure webhook URL in DocuSeal dashboard:
  - URL: `https://yourdomain.com/api/webhooks/docuseal`
  - Events: Select all submission events
  - Secret: Generate and store in `.env` as `DOCUSEAL_WEBHOOK_SECRET`

- [ ] Implement webhook verification in controller
- [ ] Update contract status when webhook received
- [ ] Send email notifications on signature completion

## 🧪 Testing Workflow

### 1. Test API Connection
```bash
php artisan tinker
```
```php
$service = app(\App\Services\DocuSealService::class);
$service->testConnection(); // Should return success
```

### 2. Test Contract Creation
1. Create a test contract in the admin panel
2. Upload a PDF template to DocuSeal dashboard
3. Note the template ID
4. Send contract for signature via API
5. Check that submission is created in DocuSeal

### 3. Test Signature Flow
1. Open the signing URL as a test signer
2. Complete the signature
3. Verify webhook updates contract status
4. Download signed PDF

### 4. Test Multi-Signer Flow
1. Create contract with 2+ signers (client + guarantor)
2. Verify each signer receives email
3. Complete signatures in order
4. Verify all signatures are tracked

## 🔒 Security Best Practices

1. **Never expose API key in frontend code** - Only use in backend
2. **Verify webhook signatures** - Validate requests are from DocuSeal
3. **Use HTTPS only** - All webhook URLs must use HTTPS
4. **Store sensitive data encrypted** - Use Laravel encryption for PII
5. **Implement rate limiting** - Protect webhook endpoint from abuse
6. **Log all API calls** - For debugging and audit trails

## 📊 Monitoring & Logging

### Key Metrics to Track
- Total submissions created
- Signature completion rate
- Average time to complete signature
- Failed submissions / errors
- Webhook processing time

### Logging Strategy
```php
// Log all DocuSeal API calls
Log::info('DocuSeal API Call', [
    'method' => 'createSubmission',
    'contract_id' => $contractId,
    'signers' => count($signers),
    'response_status' => $response->status()
]);

// Log webhook events
Log::info('DocuSeal Webhook Received', [
    'event' => $event,
    'submission_id' => $submissionId,
    'status' => $status
]);
```

## 🚀 Production Deployment

### Pre-Deployment Checklist
- [ ] API key configured in production `.env`
- [ ] Webhook URL configured in DocuSeal dashboard
- [ ] Database migrations run
- [ ] Queue worker running (for async operations)
- [ ] Email notifications configured
- [ ] Backup strategy for signed documents

### Post-Deployment Verification
- [ ] Test submission creation
- [ ] Test signature completion
- [ ] Verify webhook processing
- [ ] Check email notifications
- [ ] Monitor error logs for 24 hours

## 📞 Support & Resources

- **DocuSeal Documentation**: https://docs.docuseal.co
- **API Reference**: https://docs.docuseal.co/api
- **Status Page**: https://status.docuseal.co
- **Support Email**: support@docuseal.co

## 🔗 Related Files

- Backend Service: `app/Services/DocuSealService.php`
- Configuration: `config/services.php`
- Frontend Component: `resources/js/Pages/Contracts/Sign.vue`
- Implementation Checklist: `.azure/IMPLEMENTATION_CHECKLIST.md`
- Main Summary: `.azure/REFACTORING_SUMMARY.md`

---

**Last Updated**: November 10, 2025  
**API Key Configured**: ✅ Yes  
**Production Ready**: ⏳ Pending backend implementation
