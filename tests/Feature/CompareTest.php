<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    public function test_compare_page_loads(): void
    {
        $user = User::factory()->create();
        $companies = Company::factory()->count(2)->create();

        $response = $this->actingAs($user)->get('/compare?'.
            'companies[]='.$companies[0]->id.
            '&companies[]='.$companies[1]->id);

        $response->assertStatus(200);
    }
}
