<?php

test('sitemap returns xml', function () {
    $response = $this->get('/sitemap.xml');
    $response->assertStatus(200);
});

test('robots.txt is accessible', function () {
    $response = $this->get('/robots.txt');
    $response->assertStatus(200);
});
