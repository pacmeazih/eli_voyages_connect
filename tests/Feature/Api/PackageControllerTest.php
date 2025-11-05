<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Package;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PackageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_packages(): void
    {
        Package::factory()->count(4)->create();

        $response = $this->getJson('/api/packages');

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'destination',
                                'price',
                                'status'
                            ]
                        ],
                        'meta'
                    ],
                    'message'
                ]);
    }

    public function test_can_show_package(): void
    {
        $package = Package::factory()->create();

        $response = $this->getJson("/api/packages/{$package->id}");

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'destination',
                        'duration',
                        'price',
                        'status'
                    ],
                    'message'
                ]);
    }

    public function test_can_create_package(): void
    {
        $payload = [
            'name' => 'Sample Package',
            'description' => 'Nice trip',
            'destination' => 'Paris, France',
            'duration' => 5,
            'price' => 1200.50,
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/packages', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('packages', ['name' => 'Sample Package']);
    }

    public function test_can_update_package(): void
    {
        $package = Package::factory()->create();

        $response = $this->putJson("/api/packages/{$package->id}", [
            'name' => 'Updated Name',
            'price' => 999.99
        ]);

        $response->assertOk();
        $this->assertEquals('Updated Name', $package->fresh()->name);
        $this->assertEquals(999.99, $package->fresh()->price);
    }

    public function test_can_delete_package(): void
    {
        $package = Package::factory()->create();

        $response = $this->deleteJson("/api/packages/{$package->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }
}
