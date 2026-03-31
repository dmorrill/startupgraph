<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_fillable_attributes(): void
    {
        $fillable = ['name', 'email', 'password'];
        $user = new User();
        
        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();
        
        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_saved_searches_relationship(): void
    {
        $user = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->savedSearches->contains($savedSearch));
        $this->assertInstanceOf(SavedSearch::class, $user->savedSearches->first());
    }

    public function test_followed_companies_relationship(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        
        $user->followedCompanies()->attach($company);

        $this->assertTrue($user->followedCompanies->contains($company));
        $this->assertInstanceOf(Company::class, $user->followedCompanies->first());
    }

    public function test_recently_viewed_companies_relationship(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        
        // Insert into company_views table
        \DB::table('company_views')->insert([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'viewed_at' => now(),
        ]);

        $this->assertTrue($user->recentlyViewedCompanies->contains($company));
        $this->assertInstanceOf(Company::class, $user->recentlyViewedCompanies->first());
    }

    public function test_is_following_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        
        // User is not following initially
        $this->assertFalse($user->isFollowing($company));
        
        // Follow the company
        $user->followedCompanies()->attach($company);
        
        // Refresh the relationship
        $user->refresh();
        
        // User should now be following
        $this->assertTrue($user->isFollowing($company));
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'test123']);
        
        $this->assertTrue(password_verify('test123', $user->password));
        $this->assertNotEquals('test123', $user->password);
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['email_verified_at' => '2024-01-01 12:00:00']);
        
        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }
}