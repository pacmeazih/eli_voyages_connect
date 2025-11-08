# 🔐 Two-Factor Authentication (2FA) - Guide d'Implémentation

## ⚠️ Statut Actuel

**Implémentation:** Bloquée par conflits de dépendances Composer  
**Package souhaité:** `laravel/fortify` (bloqué)  
**Alternative recommandée:** `pragmarx/google2fa-laravel`  
**Priorité:** Moyenne (sécurité importante mais non critique pour MVP)

---

## 📋 Approches Possibles

### Option 1: Laravel Fortify (Recommandé - Bloqué)

**Package:** `laravel/fortify`

**Avantages:**
- ✅ Solution officielle Laravel
- ✅ Intégration native avec Laravel Breeze/Jetstream
- ✅ Support 2FA par défaut
- ✅ Recovery codes inclus
- ✅ Documentation complète

**Installation (bloquée):**
```bash
composer require laravel/fortify
```

**Erreur rencontrée:**
```
Your requirements could not be resolved to an installable set of packages.
Problem: laravel/fortify conflicts with ...
```

**Résolution possible:**
1. Mettre à jour Laravel vers version compatible
2. Résoudre manuellement les conflits de dépendances
3. Contacter le support Composer
4. Utiliser une alternative

---

### Option 2: PragmaRX Google2FA (Alternative Recommandée)

**Package:** `pragmarx/google2fa-laravel`

**Avantages:**
- ✅ Pas de conflits connus avec Laravel 11
- ✅ Support Google Authenticator
- ✅ Lightweight (pas de dépendances lourdes)
- ✅ Bien maintenu et documenté
- ✅ Compatible avec Authy, Microsoft Authenticator

**Installation:**
```bash
composer require pragmarx/google2fa-laravel
```

**Configuration:**
```bash
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
```

---

### Option 3: Implémentation Manuelle avec BaconQrCode

**Packages:**
- `bacon/bacon-qr-code` - Génération de QR codes
- `pragmarx/google2fa` - Logique 2FA (sans Laravel)

**Avantages:**
- ✅ Contrôle total sur l'implémentation
- ✅ Pas de conflits de dépendances
- ✅ Léger et flexible

**Installation:**
```bash
composer require bacon/bacon-qr-code pragmarx/google2fa
```

---

## 🏗️ Architecture Proposée (Option 2)

### 1. Migration Database

**Créer migration:**
```bash
php artisan make:migration add_two_factor_to_users_table
```

**Contenu:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable()->after('password');
            $table->boolean('google2fa_enabled')->default(false)->after('google2fa_secret');
            $table->text('recovery_codes')->nullable()->after('google2fa_enabled');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google2fa_secret',
                'google2fa_enabled',
                'recovery_codes',
                'two_factor_confirmed_at'
            ]);
        });
    }
};
```

**Exécuter:**
```bash
php artisan migrate
```

---

### 2. Modifier le Model User

**`app/Models/User.php` :**

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google2fa_secret',
        'google2fa_enabled',
        'recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
        'recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'google2fa_enabled' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Check if 2FA is enabled for this user
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->google2fa_enabled && !empty($this->google2fa_secret);
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        
        $this->recovery_codes = encrypt(json_encode($codes));
        $this->save();
        
        return $codes;
    }

    /**
     * Verify recovery code
     */
    public function useRecoveryCode(string $code): bool
    {
        if (!$this->recovery_codes) {
            return false;
        }

        $codes = json_decode(decrypt($this->recovery_codes), true);
        
        if (!in_array($code, $codes)) {
            return false;
        }

        // Remove used code
        $codes = array_diff($codes, [$code]);
        $this->recovery_codes = encrypt(json_encode(array_values($codes)));
        $this->save();

        return true;
    }
}
```

---

### 3. Controller TwoFactorAuthController

**Créer:**
```bash
php artisan make:controller TwoFactorAuthController
```

**`app/Http/Controllers/TwoFactorAuthController.php` :**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show 2FA settings page
     */
    public function index()
    {
        $user = auth()->user();
        
        return Inertia::render('TwoFactor/Index', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirmed' => $user->two_factor_confirmed_at !== null,
        ]);
    }

    /**
     * Enable 2FA - Generate secret and QR code
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        // Generate secret
        $secret = $this->google2fa->generateSecretKey();
        $user->google2fa_secret = encrypt($secret);
        $user->save();

        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate QR code SVG
        $qrCodeSvg = $this->generateQrCodeSvg($qrCodeUrl);

        // Generate recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();

        return back()->with([
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Confirm 2FA with OTP code
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $secret = decrypt($user->google2fa_secret);

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Code invalide. Veuillez réessayer.']);
        }

        $user->google2fa_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->save();

        return back()->with('success', 'Authentification à deux facteurs activée avec succès !');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();
        $user->google2fa_secret = null;
        $user->google2fa_enabled = false;
        $user->recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return back()->with('success', 'Authentification à deux facteurs désactivée.');
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();
        $recoveryCodes = $user->generateRecoveryCodes();

        return back()->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * Generate QR code SVG
     */
    private function generateQrCodeSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }

    /**
     * Verify OTP during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        
        // Try recovery code first
        if (strlen($request->code) === 8) {
            if ($user->useRecoveryCode(strtoupper($request->code))) {
                return redirect()->intended(route('dashboard'));
            }
        }

        // Verify OTP
        $secret = decrypt($user->google2fa_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        // Mark as verified in session
        session(['2fa_verified' => true]);

        return redirect()->intended(route('dashboard'));
    }
}
```

---

### 4. Middleware TwoFactorVerification

**Créer:**
```bash
php artisan make:middleware TwoFactorVerification
```

**`app/Http/Middleware/TwoFactorVerification.php` :**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorVerification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // User not authenticated or 2FA not enabled
        if (!$user || !$user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Already verified in this session
        if (session('2fa_verified')) {
            return $next($request);
        }

        // Redirect to 2FA verification page
        return redirect()->route('2fa.verify.show');
    }
}
```

**Enregistrer dans `bootstrap/app.php` :**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        '2fa' => \App\Http\Middleware\TwoFactorVerification::class,
    ]);
})
```

---

### 5. Routes

**`routes/web.php` :**

```php
use App\Http\Controllers\TwoFactorAuthController;

Route::middleware(['auth'])->group(function () {
    // 2FA Settings
    Route::get('/two-factor', [TwoFactorAuthController::class, 'index'])
        ->name('2fa.index');
    
    Route::post('/two-factor/enable', [TwoFactorAuthController::class, 'enable'])
        ->name('2fa.enable');
    
    Route::post('/two-factor/confirm', [TwoFactorAuthController::class, 'confirm'])
        ->name('2fa.confirm');
    
    Route::delete('/two-factor/disable', [TwoFactorAuthController::class, 'disable'])
        ->name('2fa.disable');
    
    Route::post('/two-factor/recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])
        ->name('2fa.recovery-codes');
    
    // 2FA Verification (during login)
    Route::get('/two-factor/verify', function () {
        return Inertia::render('TwoFactor/Verify');
    })->name('2fa.verify.show');
    
    Route::post('/two-factor/verify', [TwoFactorAuthController::class, 'verify'])
        ->name('2fa.verify');
});

// Protected routes with 2FA
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... other protected routes
});
```

---

### 6. Vue Components

#### **`resources/js/Pages/TwoFactor/Index.vue`**

```vue
<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    enabled: Boolean,
    confirmed: Boolean,
});

const showQrCode = ref(false);
const showRecoveryCodes = ref(false);
const confirmCode = ref('');

const enableForm = useForm({});
const confirmForm = useForm({
    code: '',
});
const disableForm = useForm({
    password: '',
});

const enable2FA = () => {
    enableForm.post(route('2fa.enable'), {
        onSuccess: () => {
            showQrCode.value = true;
        },
    });
};

const confirm2FA = () => {
    confirmForm.post(route('2fa.confirm'), {
        onSuccess: () => {
            showQrCode.value = false;
            confirmForm.reset();
        },
    });
};

const disable2FA = () => {
    if (confirm('Voulez-vous vraiment désactiver l\'authentification à deux facteurs ?')) {
        disableForm.delete(route('2fa.disable'), {
            onSuccess: () => {
                disableForm.reset();
            },
        });
    }
};
</script>

<template>
    <AppLayout title="Authentification à Deux Facteurs">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4 dark:text-white">
                    Authentification à Deux Facteurs (2FA)
                </h2>

                <!-- Status -->
                <div class="mb-6">
                    <div v-if="enabled && confirmed" class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">2FA Activé</span>
                    </div>
                    <div v-else class="flex items-center text-gray-500 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">2FA Désactivé</span>
                    </div>
                </div>

                <!-- Enable 2FA -->
                <div v-if="!enabled" class="space-y-4">
                    <p class="text-gray-600 dark:text-gray-300">
                        L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte.
                    </p>
                    <button
                        @click="enable2FA"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Activer 2FA
                    </button>
                </div>

                <!-- QR Code Setup -->
                <div v-if="showQrCode" class="mt-6 space-y-4">
                    <h3 class="text-lg font-semibold dark:text-white">
                        Scannez le QR Code
                    </h3>
                    <div v-html="$page.props.flash.qrCodeSvg" class="border rounded p-4 inline-block bg-white"></div>
                    
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Secret manuel (si le QR ne fonctionne pas) :
                        </p>
                        <code class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded text-sm">
                            {{ $page.props.flash.secret }}
                        </code>
                    </div>

                    <!-- Confirm Code -->
                    <form @submit.prevent="confirm2FA" class="mt-4">
                        <label class="block text-sm font-medium mb-2 dark:text-white">
                            Entrez le code à 6 chiffres :
                        </label>
                        <input
                            v-model="confirmForm.code"
                            type="text"
                            maxlength="6"
                            class="border rounded px-3 py-2 w-32"
                            placeholder="123456"
                        />
                        <button
                            type="submit"
                            class="ml-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                            :disabled="confirmForm.processing"
                        >
                            Confirmer
                        </button>
                    </form>

                    <!-- Recovery Codes -->
                    <div v-if="$page.props.flash.recoveryCodes" class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                            ⚠️ Codes de Récupération (Sauvegardez-les!)
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <code
                                v-for="code in $page.props.flash.recoveryCodes"
                                :key="code"
                                class="px-2 py-1 bg-white dark:bg-gray-800 rounded text-sm"
                            >
                                {{ code }}
                            </code>
                        </div>
                    </div>
                </div>

                <!-- Disable 2FA -->
                <div v-if="enabled && confirmed" class="mt-6 space-y-4">
                    <h3 class="text-lg font-semibold dark:text-white">
                        Désactiver 2FA
                    </h3>
                    <form @submit.prevent="disable2FA" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-2 dark:text-white">
                                Confirmez avec votre mot de passe :
                            </label>
                            <input
                                v-model="disableForm.password"
                                type="password"
                                class="border rounded px-3 py-2 w-full max-w-sm"
                            />
                        </div>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                            :disabled="disableForm.processing"
                        >
                            Désactiver 2FA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
```

#### **`resources/js/Pages/TwoFactor/Verify.vue`**

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('2fa.verify'));
};
</script>

<template>
    <GuestLayout title="Vérification 2FA">
        <div class="max-w-md mx-auto mt-12 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold dark:text-white">
                        Vérification 2FA
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Entrez le code de votre application d'authentification
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2 dark:text-white">
                            Code à 6 chiffres
                        </label>
                        <input
                            v-model="form.code"
                            type="text"
                            maxlength="8"
                            class="w-full border rounded-lg px-4 py-3 text-center text-2xl tracking-widest"
                            placeholder="123456"
                            autofocus
                        />
                        <p class="text-xs text-gray-500 mt-2">
                            Ou utilisez un code de récupération à 8 caractères
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                        :disabled="form.processing"
                    >
                        Vérifier
                    </button>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
```

---

## 📱 Applications Compatibles

- ✅ **Google Authenticator** (iOS/Android)
- ✅ **Microsoft Authenticator** (iOS/Android)
- ✅ **Authy** (iOS/Android/Desktop)
- ✅ **1Password** (avec abonnement)
- ✅ **LastPass Authenticator**
- ✅ **FreeOTP** (Open source)

---

## 🧪 Tests

### 1. Test Activation 2FA

```bash
# 1. Créer un compte utilisateur
php artisan tinker
>>> $user = User::factory()->create();

# 2. Se connecter et accéder à /two-factor
# 3. Cliquer sur "Activer 2FA"
# 4. Scanner le QR code avec Google Authenticator
# 5. Entrer le code à 6 chiffres
# 6. Sauvegarder les codes de récupération
```

### 2. Test Connexion avec 2FA

```bash
# 1. Se déconnecter
# 2. Se reconnecter avec email/password
# 3. Être redirigé vers /two-factor/verify
# 4. Entrer le code de l'app
# 5. Accéder au dashboard
```

### 3. Test Recovery Code

```bash
# 1. Se connecter
# 2. Aller sur /two-factor/verify
# 3. Entrer un code de récupération (8 caractères)
# 4. Le code devrait être accepté et supprimé de la liste
```

---

## 🔐 Sécurité

### Bonnes Pratiques

1. **Chiffrement du Secret:**
   - Toujours utiliser `encrypt()` / `decrypt()`
   - Jamais stocker en clair dans la base

2. **Recovery Codes:**
   - Générer 8 codes minimum
   - Supprimer après utilisation
   - Permettre régénération

3. **Rate Limiting:**
   - Limiter les tentatives de vérification
   - Bloquer après 5 échecs
   - Délai exponentiel

4. **Session Management:**
   - Invalider session après déconnexion
   - Pas de "remember me" avec 2FA actif

---

## 📋 Checklist Implémentation

- [ ] Installer package: `composer require pragmarx/google2fa-laravel bacon/bacon-qr-code`
- [ ] Créer migration `add_two_factor_to_users_table`
- [ ] Exécuter: `php artisan migrate`
- [ ] Modifier `app/Models/User.php`
- [ ] Créer `TwoFactorAuthController`
- [ ] Créer middleware `TwoFactorVerification`
- [ ] Ajouter routes dans `web.php`
- [ ] Créer composants Vue (`Index.vue`, `Verify.vue`)
- [ ] Tester activation 2FA
- [ ] Tester connexion avec 2FA
- [ ] Tester recovery codes
- [ ] Documentation utilisateur

---

## 🎓 Résumé

**Statut:** Configuration documentée, en attente de résolution des dépendances

**Résolution recommandée:**
1. Essayer `composer require pragmarx/google2fa-laravel`
2. Si échec, utiliser implémentation manuelle avec `pragmarx/google2fa`
3. Suivre le guide ci-dessus pour l'implémentation complète

**Estimation temps:** 3-4 heures une fois les dépendances résolues

---

**Mis à jour le:** 2025-11-08  
**Version:** 1.0.0 (Planification)  
**Statut:** ⏸️ En Attente
