<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_clients(): void
    {
        Client::factory()->count(5)->create();

        $response = $this->getJson('/api/clients');

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'first_name',
                                'last_name',
                                'email',
                                'created_at',
                                'updated_at'
                            ]
                        ],
                        'meta'
                    ],
                    'message'
                ]);
    }

    public function test_can_show_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->getJson("/api/clients/{$client->id}");

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'created_at',
                        'updated_at'
                    ],
                    'message'
                ]);
    }

    public function test_can_create_client(): void
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
        ];

        $response = $this->postJson('/api/clients', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('clients', ['email' => 'john.doe@example.com']);
    }

    public function test_can_update_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->putJson("/api/clients/{$client->id}", [
            'first_name' => 'Updated',
            'email' => 'updated@example.com'
        ]);

        $response->assertOk();
        $this->assertEquals('Updated', $client->fresh()->first_name);
        $this->assertEquals('updated@example.com', $client->fresh()->email);
    }

    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->deleteJson("/api/clients/{$client->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_validates_email_uniqueness_on_update(): void
    {
        $client = Client::factory()->create();
        $other = Client::factory()->create();

        $response = $this->putJson("/api/clients/{$client->id}", [
            'email' => $other->email
        ]);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
    }
}
