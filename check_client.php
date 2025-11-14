<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$client = \App\Models\Client::where('email', 'client@example.com')->first();

if ($client) {
    echo "✅ Client trouvé: ID = {$client->id}, Email = {$client->email}\n";
    
    $dossier = \App\Models\Dossier::where('client_id', $client->id)->first();
    
    if ($dossier) {
        echo "✅ Dossier trouvé: Référence = {$dossier->reference}, Status = {$dossier->status}\n";
        $docCount = $dossier->documents()->count();
        echo "   Documents: {$docCount}\n";
        $activityCount = \Spatie\Activitylog\Models\Activity::where('subject_id', $dossier->id)
            ->where('subject_type', 'App\Models\Dossier')
            ->count();
        echo "   Activités: {$activityCount}\n";
    } else {
        echo "❌ Aucun dossier trouvé pour ce client\n";
    }
} else {
    echo "❌ Client avec email 'client@example.com' non trouvé\n";
}
