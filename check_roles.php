<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Vérification des rôles utilisateurs ===\n\n";

// Récupérer tous les utilisateurs avec leurs rôles
$users = \App\Models\User::with('roles')->get();

foreach ($users as $user) {
    echo "👤 {$user->name} ({$user->email})\n";
    
    if ($user->roles->isEmpty()) {
        echo "   ❌ AUCUN RÔLE ASSIGNÉ\n";
    } else {
        echo "   Rôles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    }
    
    echo "\n";
}

// Vérifier spécifiquement l'utilisateur client
echo "=== Utilisateur client@example.com ===\n";
$client = \App\Models\User::where('email', 'client@example.com')->first();

if ($client) {
    echo "✅ Utilisateur trouvé: ID = {$client->id}\n";
    echo "Nom: {$client->name}\n";
    
    if ($client->roles->isEmpty()) {
        echo "❌ AUCUN RÔLE ASSIGNÉ - C'EST LE PROBLÈME !\n";
        echo "\nPour corriger, exécutez:\n";
        echo "php artisan db:seed --class=RoleSeeder\n";
        echo "Puis assignez le rôle Client manuellement\n";
    } else {
        echo "✅ Rôles: " . $client->roles->pluck('name')->implode(', ') . "\n";
    }
} else {
    echo "❌ Utilisateur client@example.com non trouvé\n";
}
