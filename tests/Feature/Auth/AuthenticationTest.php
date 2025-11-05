<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(Auth::check())->toBeTrue();
    $response->assertSuccessful();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertNoContent();
});
