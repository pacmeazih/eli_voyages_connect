<?php

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;

test('authorized user can create invitation', function () {
    Mail::fake();
    
    $admin = User::factory()->create();
    $admin->assignRole('Agent');
    
    $this->actingAs($admin)
        ->post(route('invitations.store'), [
            'email' => 'newuser@example.com',
            'role' => 'Client',
        ])
        ->assertRedirect(route('invitations.index'));
    
    expect(Invitation::where('email', 'newuser@example.com')->exists())->toBeTrue();
    Mail::assertSent(InvitationMail::class);
});

test('invitation token is unique and secure', function () {
    $invitation1 = Invitation::createInvitation('user1@example.com', 'Client', 1);
    $invitation2 = Invitation::createInvitation('user2@example.com', 'Client', 1);
    
    expect($invitation1->token)
        ->not->toBe($invitation2->token)
        ->toHaveLength(64);
});

test('invitation expires after 7 days', function () {
    $invitation = Invitation::createInvitation('user@example.com', 'Client', 1);
    
    expect($invitation->isValid())->toBeTrue();
    
    $invitation->update(['expires_at' => now()->subDay()]);
    
    expect($invitation->isValid())->toBeFalse();
});

test('user cannot accept expired invitation', function () {
    $invitation = Invitation::createInvitation('user@example.com', 'Client', 1);
    $invitation->update(['expires_at' => now()->subDay()]);
    
    $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'Test User',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('token');
});

test('accepting invitation creates user with correct role', function () {
    $invitation = Invitation::createInvitation('newuser@example.com', 'Client', 1);
    
    $this->post(route('invitations.accept', $invitation->token), [
        'name' => 'New User',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('dashboard'));
    
    $user = User::where('email', 'newuser@example.com')->first();
    
    expect($user)->not->toBeNull()
        ->and($user->hasRole('Client'))->toBeTrue()
        ->and($invitation->fresh()->accepted_at)->not->toBeNull();
});
