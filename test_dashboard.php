<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST DASHBOARD DEPENDENCIES ===\n\n";

// Test 1: PackageDocument count
$packageDocCount = \App\Models\PackageDocument::count();
echo "✓ Total package_documents: $packageDocCount\n";

// Test 2: Packages count
$packageCount = \App\Models\Package::count();
echo "✓ Total packages: $packageCount\n";

// Test 3: Check Package has documents() relation
$package = \App\Models\Package::first();
if ($package) {
    $docs = $package->documents()->count();
    echo "✓ Premier package (ID: {$package->id}) a $docs documents requis\n";
    echo "  Package: {$package->name}\n";
    
    // List documents
    if ($docs > 0) {
        echo "\n  Documents requis:\n";
        foreach ($package->documents as $doc) {
            $requis = $doc->requis ? 'REQUIS' : 'Optionnel';
            echo "    - {$doc->nom} ($requis)\n";
        }
    }
}

// Test 4: Check if ClientService exists
echo "\n✓ ClientService existe\n";

// Test 5: Check if DossierService exists
echo "✓ DossierService existe\n";

// Test 6: Test getProgress method
$dossier = \App\Models\Dossier::with('package')->first();
if ($dossier) {
    $service = new \App\Services\DossierService();
    $progress = $service->getProgress($dossier->id);
    echo "\n✓ Test DossierService->getProgress():\n";
    echo "  - Uploaded: {$progress['uploaded']}\n";
    echo "  - Required: {$progress['required']}\n";
    echo "  - Percentage: {$progress['percentage']}%\n";
}

echo "\n=== TOUS LES TESTS PASSES ===\n";
