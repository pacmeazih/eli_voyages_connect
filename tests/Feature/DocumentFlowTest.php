<?php

use App\Models\User;
use App\Models\Dossier;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Storage::fake('local');  // Changed from 's3' to 'local' for cPanel compatibility

    // Create role and permissions required by controller authorize checks
    $role = Role::firstOrCreate(['name' => 'Agent']);
    foreach (['upload documents', 'download documents', 'edit documents', 'delete documents', 'view documents', 'view dossiers'] as $permName) {
        Permission::firstOrCreate(['name' => $permName]);
        $role->givePermissionTo($permName);
    }

    $this->user = User::factory()->create();
    $this->user->assignRole($role);
    $this->actingAs($this->user);
});

it('uploads a document to a dossier', function () {
    $dossier = Dossier::factory()->create();

    $response = $this->post(route('dossiers.documents.store', $dossier), [
        'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        'type' => 'contract',
        'description' => 'Signed contract',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $doc = Document::first();
    expect($doc)->not->toBeNull();
    Storage::disk('local')->assertExists($doc->path);

    // Activity Log created
    expect(DB::table('activity_log')->count())->toBeGreaterThan(0);
});

it('creates a new version of a document', function () {
    $dossier = Dossier::factory()->create();

    // initial upload
    $this->post(route('dossiers.documents.store', $dossier), [
        'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        'type' => 'contract',
    ]);
    $original = Document::first();

    // upload new version
    $response = $this->post(route('documents.version', $original), [
        'file' => UploadedFile::fake()->create('contract_v2.pdf', 110, 'application/pdf'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updated = Document::orderByDesc('id')->first();
    expect($updated->previous_version_id)->toBe($original->id);
    Storage::disk('local')->assertExists($updated->path);
});

it('deletes a document and removes the file', function () {
    $dossier = Dossier::factory()->create();
    $this->post(route('dossiers.documents.store', $dossier), [
        'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        'type' => 'contract',
    ]);
    $doc = Document::first();
    Storage::disk('local')->assertExists($doc->path);

    $response = $this->delete(route('documents.destroy', $doc));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Document::find($doc->id))->toBeNull();
    Storage::disk('local')->assertMissing($doc->path);
});
