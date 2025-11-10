<?php

/**
 * DOCUSEAL INTEGRATION - ROUTES EXAMPLE
 * 
 * Add these routes to your routes/web.php and routes/api.php files
 */

// ========== WEB ROUTES (routes/web.php) ==========

use App\Http\Controllers\ContractController;

// Contract Generation & Management
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Show contract generation page
    Route::get('/contracts/generate/{dossier}', [ContractController::class, 'generate'])
        ->name('contracts.generate');
    
    // Create and send contract for signature
    Route::post('/contracts/generate/{dossier}', [ContractController::class, 'store'])
        ->name('contracts.store');
    
    // Show contract details and signature status
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])
        ->name('contracts.show');
    
    // Send reminder to pending signers
    Route::post('/contracts/{contract}/remind', [ContractController::class, 'remind'])
        ->name('contracts.remind');
    
    // Download signed contract PDF
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])
        ->name('contracts.download');
    
    // Contract completion redirect page
    Route::get('/contracts/{contract}/completed', function (Contract $contract) {
        return Inertia::render('Contracts/Completed', [
            'contract' => $contract->load('dossier.client'),
        ]);
    })->name('contracts.completed');
});

// ========== API ROUTES (routes/api.php) ==========

// Note: ContractController is already imported above, no need to import again

// DocuSeal Webhook Endpoint (exclude from CSRF protection)
Route::post('/webhooks/docuseal', [ContractController::class, 'webhook'])
    ->name('webhooks.docuseal');

// API endpoints for AJAX requests
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Get contract status
    Route::get('/api/contracts/{contract}/status', function (Contract $contract) {
        return response()->json([
            'status' => $contract->status,
            'completed_at' => $contract->completed_at,
            'signatures' => $contract->signatures->map(fn($sig) => [
                'signer_name' => $sig->signer_name,
                'signer_email' => $sig->signer_email,
                'status' => $sig->status,
                'signed_at' => $sig->signed_at,
            ]),
        ]);
    });
    
    // Get embed URL for current user
    Route::get('/api/contracts/{contract}/embed-url', function (Contract $contract) {
        $userEmail = auth()->user()->email;
        $embedUrl = null;
        
        if ($contract->docuseal_signers) {
            foreach ($contract->docuseal_signers as $signer) {
                if ($signer['email'] === $userEmail) {
                    $embedUrl = $signer['embed_src'] ?? null;
                    break;
                }
            }
        }
        
        return response()->json(['embed_url' => $embedUrl]);
    });
});

// ========== EXCLUDE FROM CSRF PROTECTION ==========

/**
 * Add this to app/Http/Middleware/VerifyCsrfToken.php
 */

// protected $except = [
//     'api/webhooks/docuseal',
// ];
