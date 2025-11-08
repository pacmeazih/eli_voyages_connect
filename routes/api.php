<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Public webhooks (no auth required, but with rate limiting)
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/webhooks/docuseal', [WebhookController::class, 'docuseal'])->name('webhooks.docuseal');
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Users management
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
    // Clients and Packages
    Route::apiResource('clients', App\Http\Controllers\Api\ClientController::class);
    Route::apiResource('packages', App\Http\Controllers\Api\PackageController::class);
    // Dossiers and documents
    Route::apiResource('dossiers', App\Http\Controllers\Api\DossierController::class)->except(['create','edit']);
    Route::post('dossiers/{dossier}/documents', [App\Http\Controllers\Api\DocumentController::class, 'store']);
    Route::get('dossiers/{dossier}/documents/{document}', [App\Http\Controllers\Api\DocumentController::class, 'download']);
});
