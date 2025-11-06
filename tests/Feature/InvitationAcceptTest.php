<?php

use App\Models\Invitation;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Ensure role exists for assignment
    Role::firstOrCreate(['name' => 'Agent']);
});

it('accepts a valid invitation', function () {
    $inviter = User::factory()->create();
    $invitation = Invitation::factory()->create([
        'role' => 'Agent',
        'invited_by' => $inviter->id,
    ]);

    $response = $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'New User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $invitation->refresh();
    expect($invitation->accepted_at)->not->toBeNull();
    $user = User::where('email', $invitation->email)->first();
    expect($user)->not->toBeNull();
    expect(auth()->check())->toBeTrue();
    expect($user->hasRole('Agent'))->toBeTrue();
});

it('cannot accept an expired invitation', function () {
    $inviter = User::factory()->create();
    $invitation = Invitation::factory()->expired()->create([
        'role' => 'Agent',
        'invited_by' => $inviter->id,
    ]);

    $response = $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'Other User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $invitation->refresh();
    expect($invitation->accepted_at)->toBeNull();
    $response->assertSessionHasErrors('token');
});

it('returns 404 for an invalid token', function () {
    $response = $this->get(route('invitations.show', 'does-not-exist'));
    $response->assertStatus(404);
});

it('cannot reuse an accepted invitation', function () {
    $inviter = User::factory()->create();
    $invitation = Invitation::factory()->create([
        'role' => 'Agent',
        'invited_by' => $inviter->id,
    ]);

    $first = $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'First User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $first->assertRedirect(route('dashboard'));
    $invitation->refresh();
    expect($invitation->accepted_at)->not->toBeNull();

    $second = $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'Second User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    $second->assertSessionHasErrors('token');
});
