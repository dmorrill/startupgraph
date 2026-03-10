<?php

use App\Models\Feedback;
use App\Models\User;

test('feedback has fillable attributes', function () {
    $feedback = new Feedback;
    expect($feedback->getFillable())->toBe(['user_id', 'page_url', 'message']);
});

test('feedback belongs to a user', function () {
    $feedback = Feedback::factory()->create();
    expect($feedback->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($feedback->user)->toBeInstanceOf(User::class);
});

test('feedback can be created without a user', function () {
    $feedback = Feedback::factory()->anonymous()->create();
    expect($feedback->user_id)->toBeNull();
    expect($feedback->user)->toBeNull();
});

test('feedback uses correct table name', function () {
    $feedback = new Feedback;
    expect($feedback->getTable())->toBe('feedback');
});

test('feedback message is required', function () {
    expect(fn () => Feedback::factory()->create(['message' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
