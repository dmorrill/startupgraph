<?php

use App\Models\User;

test('profile update requires auth', function () {
    $response = $this->patch('/profile', ['name' => 'New Name', 'email' => 'new@example.com']);
    $response->assertRedirect('/login');
});

test('user can update their profile name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ]);

    $response->assertRedirect(route('profile.edit'));
    expect($user->fresh()->name)->toBe('Updated Name');
});

test('changing email clears email verification', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->patch('/profile', [
        'name' => $user->name,
        'email' => 'newemail@example.com',
    ]);

    expect($user->fresh()->email_verified_at)->toBeNull();
});

test('keeping same email preserves email verification', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->patch('/profile', [
        'name' => 'New Name',
        'email' => $user->email,
    ]);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('profile update requires valid email', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/profile', [
        'name' => 'Test',
        'email' => 'not-an-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user can delete their account with correct password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::find($user->id))->toBeNull();
});

test('account deletion fails with wrong password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete('/profile', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('userDeletion', 'password');
    expect(User::find($user->id))->not->toBeNull();
});
