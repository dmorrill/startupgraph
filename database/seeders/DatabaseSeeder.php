<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            // Base company data
            CompanySeeder::class,
            TechCrunchCompaniesSeeder::class,

            // Company profiles and people
            CompanyProfileSeeder::class,
            ProductHighlightsSeeder::class,
            PeopleSeeder::class,

            // Categories
            CompanyCategorySeeder::class,

            // Funding data
            FundingDataSeeder::class,
            MoreFundingDataSeeder::class,
            AdditionalFundingDataSeeder::class,

            // LinkedIn URLs
            LinkedInUrlSeeder::class,

            // Source URLs for funding rounds
            FundingSourceUrlSeeder::class,

            // Wikipedia company imports
            WikipediaCompaniesSeeder::class,
        ]);
    }
}
