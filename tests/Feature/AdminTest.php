<?php

use App\Models\User;

test('admin area requires auth', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});
