<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
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

    public function test_can_show_user(): void
    {
        $response = $this->getJson("/api/users/{$this->user->id}");

        $response->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ],
                    'message'
                ]);
    }

    public function test_can_update_user(): void
    {
        $response = $this->putJson("/api/users/{$this->user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        $response->assertOk();
        $this->assertEquals('Updated Name', $this->user->fresh()->name);
        $this->assertEquals('updated@example.com', $this->user->fresh()->email);
    }

    public function test_can_delete_user(): void
    {
        $response = $this->deleteJson("/api/users/{$this->user->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_validates_email_uniqueness_on_update(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->putJson("/api/users/{$this->user->id}", [
            'email' => $otherUser->email
        ]);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
    }
}