Quick start (development)

1. Install PHP, Composer, Node.js and npm.

2. Install PHP dependencies:

```bat
composer install --no-interaction --prefer-dist
```

3. Install JS dependencies and build assets:

```bat
npm ci
npx vite build
```

4. Create local sqlite DB and run migrations/seeds:

```bat
php -r "file_exists('database') || mkdir('database');"
php -r "type nul > database\database.sqlite"
php artisan key:generate --ansi
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force
```

5. Run tests:

```bat
php artisan test
```

Notes
- RBAC: spatie/laravel-permission is included. Run `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag=config` and `php artisan migrate` if needed.
- Documents: basic `Dossier` and `Document` models and endpoints exist. Configure `FILESYSTEM_DRIVER` to use `local` or `s3` in `.env`.
- Integrations: `app/Services/DocuSealService.php` and `app/Services/WhatsAppService.php` are scaffolds; implement provider code and add credentials to `.env`.
