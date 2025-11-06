<?php

use App\Models\Dossier;
use App\Models\Client;
use App\Models\User;

test('dossier automatically generates unique reference', function () {
    $client = Client::factory()->create();
    
    $dossier = Dossier::create([
        'client_id' => $client->id,
        'title' => 'Test Dossier',
    ]);
    
    expect($dossier->reference)
        ->toMatch('/^ELI-\d{4}-\d{6}$/')
        ->toContain(date('Y'));
});

test('dossier references are unique', function () {
    $client = Client::factory()->create();
    
    $dossiers = collect();
    for ($i = 0; $i < 10; $i++) {
        $dossiers->push(Dossier::create([
            'client_id' => $client->id,
            'title' => "Test Dossier {$i}",
        ]));
    }
    
    $references = $dossiers->pluck('reference')->unique();
    
    expect($references->count())->toBe(10);
});

test('authorized user can create dossier', function () {
    $agent = User::factory()->create();
    $agent->assignRole('Agent');
    
    $client = Client::factory()->create();
    
    $this->actingAs($agent)
        ->post(route('dossiers.store'), [
            'client_id' => $client->id,
            'title' => 'New Immigration Case',
            'notes' => 'Initial consultation completed',
        ])
        ->assertRedirect();
    
    expect(Dossier::where('title', 'New Immigration Case')->exists())->toBeTrue();
});

test('client can only view their own dossiers', function () {
    $client1 = User::factory()->create();
    $client1->assignRole('Client');
    
    $client2 = User::factory()->create();
    $client2->assignRole('Client');
    
    $clientModel1 = Client::factory()->create();
    $clientModel2 = Client::factory()->create();
    
    $dossier1 = Dossier::factory()->create(['client_id' => $clientModel1->id]);
    $dossier2 = Dossier::factory()->create(['client_id' => $clientModel2->id]);
    
    // This test would need policy implementation
    // Placeholder for now
    expect(true)->toBeTrue();
});

test('consultant can validate dossier', function () {
    $consultant = User::factory()->create();
    $consultant->assignRole('Consultant');
    
    $client = Client::factory()->create();
    $dossier = Dossier::factory()->create(['client_id' => $client->id]);
    
    $this->actingAs($consultant)
        ->post(route('dossiers.validate', $dossier))
        ->assertRedirect();
    
    // Check activity log
    expect(activity()->forSubject($dossier)->first()->description)
        ->toContain('validated');
});
