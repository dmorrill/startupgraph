<?php

test('admin area requires auth', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});
