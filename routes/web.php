<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ClientTrackingController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AppointmentController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Public invitation routes
Route::get('/invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Local debug: list logged-in user permissions & roles (remove in production)
    if (app()->environment(['local', 'development'])) {
        Route::get('/debug/permissions', function () {
            return response()->json([
                'user' => auth()->user()?->only(['id','email','name']),
                'roles' => auth()->user()?->getRoleNames(),
                'permissions' => auth()->user()?->getPermissionNames(),
            ]);
        });
    }
    
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile (missing previously) - convert to Inertia
    Route::get('/profile', function() { return inertia('Profile/Edit', [ 'user' => auth()->user() ]); })->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Preferences
    Route::post('/preferences/dark-mode', [PreferencesController::class, 'updateDarkMode'])->name('preferences.darkMode');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/page', [NotificationController::class, 'page'])->name('notifications.page');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Search
    Route::get('/search', [SearchController::class, 'search'])->name('search')->middleware('throttle:100,1');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.data')->middleware('throttle:100,1');
    Route::get('/analytics/page', function () {
        return inertia('Analytics/Index');
    })->name('analytics.page');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/data', [AppointmentController::class, 'getAppointments'])->name('appointments.data');
    Route::get('/appointments/slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.slots');
    Route::get('/appointments/agents', [AppointmentController::class, 'getAgents'])->name('appointments.agents');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    // Client Tracking - Special dashboard for clients
    Route::get('/client-tracking', [ClientTrackingController::class, 'index'])->name('client.tracking');
    Route::get('/client-tracking/{dossier}', [ClientTrackingController::class, 'show'])->name('client.tracking.show');

    // Invitations (for users with permission)
    Route::middleware('can:invite users')->group(function () {
        Route::resource('invitations', InvitationController::class)->except(['show', 'edit', 'update']);
        Route::post('invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');
    });

    // Dossiers
    Route::resource('dossiers', DossierController::class);
    Route::post('dossiers/{dossier}/validate', [DossierController::class, 'validate'])->name('dossiers.validate');
    Route::post('dossiers/{dossier}/approve', [DossierController::class, 'approve'])->name('dossiers.approve');
    Route::post('dossiers/{dossier}/change-status', [DossierController::class, 'changeStatus'])->name('dossiers.changeStatus');

    // Documents (nested under dossiers)
    Route::prefix('dossiers/{dossier}')->group(function () {
        Route::get('/documents', [DocumentController::class, 'index'])->name('dossiers.documents.index');
        Route::post('/documents', [DocumentController::class, 'store'])->name('dossiers.documents.store');
    });

    // Documents (direct access)
    Route::resource('documents', DocumentController::class)->except(['index', 'create', 'store']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/version', [DocumentController::class, 'version'])->name('documents.version');

    // Contracts
    Route::prefix('dossiers/{dossier}')->group(function () {
        Route::get('/contracts/create', [ContractController::class, 'create'])->name('dossiers.contracts.create');
        Route::post('/contracts/generate', [ContractController::class, 'generate'])->name('dossiers.contracts.generate');
        Route::get('/contracts/{document}/download', [ContractController::class, 'download'])->name('dossiers.contracts.download');
    });
    Route::post('/contracts/preview', [ContractController::class, 'preview'])->name('contracts.preview');
});

require __DIR__.'/auth.php';

