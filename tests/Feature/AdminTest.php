<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_area_requires_auth(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }
}
