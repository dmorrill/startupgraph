<?php

test('sitemap returns xml', function () {
    $response = $this->get('/sitemap.xml');
    $response->assertStatus(200);
});
