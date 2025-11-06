# ELI Voyages Connect

Client Document Management System for travel and immigration agencies built with Laravel 11.

## Features

✅ **Implemented**
- Role-based access control (SuperAdmin, Consultant, Agent, Client, Guarantor)
- User invitation system with secure tokens
- Document upload and management with S3 storage
- Unique dossier reference generation (ELI-YYYY-XXXXXX)
- Comprehensive activity logging and audit trails
- Document versioning support

🚧 **In Progress**
- DocuSeal integration for e-signatures
- WhatsApp notifications
- Frontend Vue.js components

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Database**: PostgreSQL (SQLite for dev)
- **Frontend**: Inertia.js + Vue.js 3
- **Styling**: Tailwind CSS
- **Storage**: S3-compatible storage
- **Authentication**: Laravel Sanctum + Spatie Permissions

## Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL or PostgreSQL (or SQLite for development)

### Local Development Installation

1. Clone the repository
```bash
git clone https://github.com/pacmeazih/eli_voyages_connect.git
cd eli_voyages_connect
```

2. Install dependencies
```bash
composer install
npm install
```

3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your `.env` file
```env
DB_CONNECTION=sqlite  # or pgsql for production
FILESYSTEM_DISK=local # or s3 for production
MAIL_MAILER=log       # configure for production

# S3 Configuration (optional for local dev)
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket

# DocuSeal (optional)
DOCUSEAL_API_KEY=your_key
```

5. Run migrations and seed
```bash
php artisan migrate --seed
```

6. Start the development server
```bash
php artisan serve
npm run dev
```

Visit `http://localhost:8000`

### cPanel Hosting Installation

**Pour installer sur un hébergement cPanel, consultez le guide complet :**
👉 **[INSTALLATION_CPANEL.md](INSTALLATION_CPANEL.md)**

Points clés :
- Stockage local (pas besoin de S3/AWS)
- Configuration MySQL via cPanel
- Lien symbolique `public_html` → `public/`
- Email SMTP intégré cPanel
- Guide pas à pas avec screenshots

### Default Test User
After seeding, you can login with:
- Email: `test@example.com`
- Password: (set during seeding)
- Role: SuperAdmin

## Architecture

### Models
- **User**: System users with role-based permissions
- **Client**: Client information
- **Dossier**: Immigration case files with unique references
- **Document**: File attachments with versioning
- **Invitation**: User invitation tokens
- **Package**: Service packages

### Permissions System

The application uses Spatie Laravel Permission for RBAC:

**Roles:**
- SuperAdmin: Full system access
- Consultant: Review and approval permissions
- Agent: Core operational access
- Client: Limited read and upload
- Guarantor: Related dossier access

**Key Permissions:**
- User management: `manage users`, `invite users`
- Dossier management: `create/edit/view/delete/validate/approve dossiers`
- Document management: `upload/view/edit/delete/download documents`
- Contract management: `generate/send/view/sign contracts`

### Document Storage

Documents are organized hierarchically:
```
s3://bucket/dossiers/{reference}/{type}/{filename}
```

Example: `dossiers/ELI-2025-000123/passport/john_doe_passport_1699200000_a1b2c3d4.pdf`

### Activity Logging

All important actions are logged using Spatie Activity Log:
- Dossier creation/updates
- Document uploads/downloads
- Permission changes
- User actions

## Testing

The project uses Pest for feature and unit testing.

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test --filter=InvitationAcceptTest
```

### Test Suites

- **Feature Tests**: End-to-end flows for invitations, document uploads, authentication
- **Unit Tests**: Model methods, services, helpers

### Known Issues
- `AuthenticationTest::users can authenticate using the login screen` currently fails; login flow requires additional session handling for Inertia. Issue tracked in #42.

## CI/CD

The project uses GitHub Actions for continuous integration.

### Workflow

On every push and pull request to `main`:
1. **PHP Tests**: Composer install, migrate, run Pest suite
2. **Frontend Build**: npm ci, Vite build

See `.github/workflows/ci.yml` for full configuration.

### Environment Variables for CI

The CI workflow uses MySQL service and requires:
- `DB_CONNECTION=mysql`
- S3 credentials (can be mocked with `Storage::fake('s3')` in tests)

## Documents Management & Filtering

The Documents Index page (`/dossiers/{dossier}/documents`) supports:

### Filters
- **Search**: Filter by document name
- **Type**: Filter by document type (passport, contract, invoice, etc.)
- **Uploader**: Filter by user who uploaded the document

### Sorting
Click any column header to sort:
- Name (▲/▼)
- Type
- Size
- Uploaded By

### Pagination
15 documents per page; links appear at bottom when results exceed one page.

### Implementation
- Backend: `DocumentController@index` with query string filters
- Frontend: `resources/js/Pages/Documents/Index.vue` with reactive form

## Environment Variables

A comprehensive `.env.example` is provided. Key variables:

### Required for Basic Functionality
```env
APP_NAME="ELI Voyages Connect"
APP_KEY=                      # Run: php artisan key:generate
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=eli_voyages_connect
DB_USERNAME=root
DB_PASSWORD=
```

### Required for Document Storage
```env
# Pour cPanel : stockage local (par défaut)
FILESYSTEM_DISK=local

# Pour Cloud/AWS S3 (optionnel)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=eli-voyages-documents
```

**Note** : Sur cPanel, utilisez `FILESYSTEM_DISK=local`. Les documents seront stockés dans `storage/app/dossiers/`. Assurez-vous que le dossier `storage/` a les bonnes permissions (775).

### Required for E-Signatures (DocuSeal)
```env
DOCUSEAL_API_KEY=your_docuseal_api_key
DOCUSEAL_API_URL=https://api.docuseal.co
```

### Optional (WhatsApp Notifications)
```env
WHATSAPP_API_TOKEN=your_whatsapp_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
```

For local development, you can use `FILESYSTEM_DISK=local` and `MAIL_MAILER=log`.



## Development

### Code Quality
```bash
# PHP static analysis
./vendor/bin/phpstan analyse

# Code formatting
./vendor/bin/pint
```

### Database
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name
```

## Deployment

### Production Checklist
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure PostgreSQL database
- [ ] Set up S3 storage
- [ ] Configure mail service (AWS SES, Mailgun, etc.)
- [ ] Set up queue worker: `php artisan queue:work`
- [ ] Enable Laravel Telescope for monitoring (optional)
- [ ] Configure backup strategy
- [ ] Set up SSL certificate

### Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

## API Documentation

API routes are defined in `routes/api.php`. Authentication uses Laravel Sanctum.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security issues, please email security@example.com instead of using the issue tracker.

## License

This project is proprietary software. All rights reserved.

## Support

For support, email support@elivoyages.com or create an issue in the repository.


In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
