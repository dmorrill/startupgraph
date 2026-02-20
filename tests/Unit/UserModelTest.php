<?php

use App\Models\User;
use App\Models\SavedSearch;

test('user has fillable attributes', function () {
    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
    expect($user->name)->toBe('Jane Doe');
    expect($user->email)->toBe('jane@example.com');
});

test('user has many saved searches', function () {
    $user = User::factory()->create();
    expect($user->savedSearches())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user casts email_verified_at to datetime', function () {
    $user = User::factory()->create();
    expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('user hides password and remember_token', function () {
    $user = User::factory()->create();
    $array = $user->toArray();
    expect($array)->not->toHaveKey('password');
    expect($array)->not->toHaveKey('remember_token');
});

test('user password is hashed', function () {
    $user = User::factory()->create(['password' => 'secret123']);
    expect($user->password)->not->toBe('secret123');
    expect(\Illuminate\Support\Facades\Hash::check('secret123', $user->password))->toBeTrue();
});

test('user email must be unique', function () {
    User::factory()->create(['email' => 'dupe@example.com']);
    expect(fn () => User::factory()->create(['email' => 'dupe@example.com']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
