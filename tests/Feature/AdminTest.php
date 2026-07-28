<?php

test('admin area requires basic auth', function () {
    config(['admin.username' => 'admin', 'admin.password' => 'secret']);

    $response = $this->get('/admin/companies');
    $response->assertStatus(401);
});

test('admin area unavailable without configured credentials', function () {
    config(['admin.username' => null, 'admin.password' => null]);

    $response = $this->get('/admin/companies');
    $response->assertStatus(503);
});
