<?php

test('saved search requires auth', function () {
    $response = $this->get('/saved-searches');
    $response->assertRedirect('/login');
});
