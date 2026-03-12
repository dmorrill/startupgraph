<?php

use App\Models\User;

test('admin companies area returns 401 without credentials', function () {
    config(['admin.username' => 'admin', 'admin.password' => 'secret']);
    $response = $this->get('/admin/companies');
    $response->assertStatus(401);
});
