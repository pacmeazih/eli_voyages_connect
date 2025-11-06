<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Public invitation routes
Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Invitations (for users with permission)
    Route::middleware('can:invite users')->group(function () {
        Route::resource('invitations', InvitationController::class)->except(['show', 'edit', 'update']);
        Route::post('invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');
    });

    // Dossiers
    Route::resource('dossiers', DossierController::class);
    Route::post('dossiers/{dossier}/validate', [DossierController::class, 'validate'])->name('dossiers.validate');
    Route::post('dossiers/{dossier}/approve', [DossierController::class, 'approve'])->name('dossiers.approve');

    // Documents (nested under dossiers)
    Route::prefix('dossiers/{dossier}')->group(function () {
        Route::get('/documents', [DocumentController::class, 'index'])->name('dossiers.documents.index');
        Route::post('/documents', [DocumentController::class, 'store'])->name('dossiers.documents.store');
    });

    // Documents (direct access)
    Route::resource('documents', DocumentController::class)->except(['index', 'create', 'store']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/version', [DocumentController::class, 'version'])->name('documents.version');
});

require __DIR__.'/auth.php';

