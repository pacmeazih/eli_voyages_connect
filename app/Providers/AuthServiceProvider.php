<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Document;
use App\Policies\DocumentPolicy;
use App\Models\Dossier;
use App\Policies\DossierPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Document::class => DocumentPolicy::class,
        Dossier::class => DossierPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate pour l'upload de documents dans un dossier
        Gate::define('upload-document-to-dossier', function ($user, $dossier) {
            // SuperAdmin, Admin, Agent, Consultant can upload
            if ($user->hasRole(['SuperAdmin', 'Admin', 'Agent', 'Consultant'])) {
                return true;
            }

            if ($user->can('manage documents')) {
                return true;
            }

            // Allow clients to upload to their own dossier
            if ($user->hasRole('Client')) {
                $client = \App\Models\Client::where('email', $user->email)->first();
                if ($client && $dossier->client_id === $client->id) {
                    return true;
                }
            }

            return false;
        });
    }
}
