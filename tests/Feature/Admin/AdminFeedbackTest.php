<?php

use App\Models\Feedback;
use App\Models\User;

beforeEach(function () {
    config(['admin.username' => 'admin', 'admin.password' => 'secret']);
});

$admin = ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'secret'];

test('admin feedback index requires auth', function () {
    $response = $this->get('/admin/feedback');

    $response->assertStatus(401);
});

test('admin feedback index loads', function () use ($admin) {
    $user = User::factory()->create();
    Feedback::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->withServerVariables($admin)->get('/admin/feedback');

    $response->assertStatus(200);
});

test('admin feedback shows anonymous entries', function () use ($admin) {
    Feedback::factory()->anonymous()->create(['message' => 'Anonymous feedback here']);

    $response = $this->withServerVariables($admin)->get('/admin/feedback');

    $response->assertStatus(200);
    $response->assertSee('Anonymous feedback here');
});
