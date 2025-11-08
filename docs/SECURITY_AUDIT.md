# Security Audit – ELI Voyages Connect (2025-11-08)

This report summarizes the current security posture, fixes applied in this session, and recommended actions for production hardening.

## Executive Summary

- Risk posture: Low-to-Medium (most controls in place; a few prod env tweaks required)
- Immediate fixes applied: CORS restrictions, global security headers, HTTPS forcing in production
- No known vulnerable dependencies via `composer audit`
- Upload pipeline: strong validation (11 layers)
- Webhook: HMAC validation implemented (verify provider spec before go-live)

---

## Changes Applied

1. CORS hardening (`config/cors.php`)
   - paths restricted to `['api/*', 'sanctum/csrf-cookie']`
   - allowed_methods limited to explicit verbs
   - allowed_headers reduced to common required headers
   - max_age set to 86400
   - credentials supported with a single trusted origin (`FRONTEND_URL`)

2. Global Security Headers middleware
   - Created `App/Http/Middleware/SecurityHeaders.php`
   - Registered globally in `bootstrap/app.php`
   - Headers added:
     - `X-Frame-Options: SAMEORIGIN`
     - `X-Content-Type-Options: nosniff`
     - `Referrer-Policy: strict-origin-when-cross-origin`
     - `Permissions-Policy: geolocation=(), microphone=(), camera=()`
   - (Optional) CSP can be added later after asset audit

3. HTTPS enforcement in production
   - `AppServiceProvider`: `URL::forceScheme('https')` when `APP_ENV=production`

---

## Configuration Review (Findings & Actions)

- App Debug & Env
  - Ensure `.env` has `APP_ENV=production` and `APP_DEBUG=false` in prod.

- Sessions & Cookies
  - Recommended `.env` settings in production:
    - `SESSION_ENCRYPT=true`
    - `SESSION_SECURE_COOKIE=true` (HTTPS required)
    - `SESSION_SAME_SITE=lax` (or `none` if 3rd-party auth flows; requires HTTPS)

- Sanctum
  - Consider token expiration: set `SANCTUM_EXPIRATION=120` (minutes) if desired.
  - Ensure `FRONTEND_URL` is set to exact SPA origin (https scheme + domain).

- Filesystems
  - Default disk points to `storage/app/private` (good).
  - Backups stored under `storage/app/backups` (non-public). Keep excluded from any symlink.

- CORS
  - Now restricted. Keep `FRONTEND_URL` accurate per environment.

- Security Headers
  - Added as global middleware. CSP intentionally not enforced yet to avoid breaking assets.

---

## Upload Pipeline Review

- Controller: `App\Http\Controllers\DocumentController`
- Service: `App\Services\DocumentService`
- Strengths:
  - 11-layer validation: size by MIME, allowed MIME whitelist, extension vs MIME cross-check, exec/archives blocked, double-extension rejection, invalid chars, null byte detection, basic content checks (PDF magic, image code signatures).
  - Storage path uses default (private) disk; filenames are sanitized slugs with random suffix.
- Recommendations:
  - Confirm `Dossier::reference` is sanitized/slugged; if not, sanitize it before path usage.
  - Optionally, add AV scanning stage (e.g., ClamAV) for PDFs/images in production.
  - Consider allowing ZIP only if there is a business need; otherwise keep blocked.

---

## Webhook Security Review

- Endpoint: `POST /api/webhooks/docuseal` (public)
- `WebhookController` validates signature header `X-Webhook-Signature` using `ContractService::validateWebhookSignature`.
- Production behavior: HMAC-SHA256 with API key; dev env bypasses validation.
- Actions:
  - Verify DocuSeal’s exact signing algorithm & header naming (adjust if needed).
  - Add a rate limiter for the webhook route (optional) to mitigate abuse.

---

## Dependency Security

- Composer audit
  - Result: no advisories found.
  - Note: `nunomaduro/larastan` is abandoned → replace with `larastan/larastan` during next dev-tooling update.

---

## Hardening Checklist (Production)

- [ ] `.env`  
  - `APP_ENV=production`  
  - `APP_DEBUG=false`  
  - `APP_URL=https://your-domain`  
  - `FRONTEND_URL=https://your-frontend-domain`  
  - `SESSION_ENCRYPT=true`  
  - `SESSION_SECURE_COOKIE=true`  
  - `SANCTUM_EXPIRATION=120` (optional)
- [ ] HTTPS and Reverse Proxy  
  - Ensure proxy headers are trusted (e.g., AWS/NGINX)  
  - TLS 1.2+ only, strong ciphers
- [ ] Backups  
  - Set `BACKUP_ARCHIVE_PASSWORD` and test decryption  
  - Exclude `.env` and secrets from backup contents if not needed
- [ ] Monitoring & Logging  
  - Enable central log monitoring (Sentry, Datadog, etc.)  
  - Alert on 4xx/5xx spikes, webhook signature failures
- [ ] CSP (Optional, phased)  
  - Start with `Content-Security-Policy-Report-Only` to collect violations  
  - Move to enforcing CSP once noise is resolved

---

## Appendix – Files Changed

- `config/cors.php` – Restrictive CORS settings
- `app/Http/Middleware/SecurityHeaders.php` – New middleware
- `bootstrap/app.php` – Register global security headers middleware
- `app/Providers/AppServiceProvider.php` – Force HTTPS in production

---

Prepared by: Security Review Bot  
Date: 2025-11-08
