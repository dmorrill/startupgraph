<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanyCategorySeeder extends Seeder
{
    /**
     * Assign categories to all existing companies.
     */
    public function run(): void
    {
        $categories = [
            'ai_ml' => [
                'OpenAI', 'Anthropic', 'xAI', 'Cohere', 'Mistral AI', 'Hugging Face',
                'Scale AI', 'Weights & Biases', 'Groq', 'Together AI', 'Character.AI',
                'Inflection AI', 'Stability AI', 'Midjourney', 'Runway', 'Jasper',
                'Copy.ai', 'Writer', 'Glean', 'Perplexity', 'Cursor', 'Applied Intuition',
                'ElevenLabs', 'Mercor', 'Lovable', 'Imbue', 'Covariant', 'Poolside',
                'Magic', 'Cognition', 'Anysphere', 'Adept', 'Harvey', 'Synthesia',
                'Mosaic ML', 'Anyscale', 'Cerebras Systems', 'NeoLogic',
            ],
            'fintech' => [
                'Stripe', 'Plaid', 'Chime', 'Brex', 'Ramp', 'Mercury', 'Klarna',
                'Affirm', 'Checkout.com', 'Revolut', 'Wise', 'Marqeta', 'Robinhood',
                'Coinbase', 'Rippling', 'Deel', 'Pine Labs', 'Karat Financial', 'Zip',
                'Alpaca', 'Synctera', 'Djamo', 'Sardine', 'Gusto',
            ],
            'enterprise' => [
                'Databricks', 'ServiceTitan', 'Toast', 'Celonis', 'Monday.com',
                'Asana', 'Notion', 'Airtable', 'ClickUp', 'Miro', 'Figma', 'Canva',
                'Webflow', 'Loom', 'Linear', 'Calendly', 'Island', 'SecurityPal',
                'Wiz', 'Vanta', 'Flock Safety', 'Navan', 'Retool', 'Faire',
            ],
            'healthcare' => [
                'Devoted Health', 'Ro', 'Hims & Hers', 'Noom', 'Tempus', 'Headspace',
                'Calm', 'Retro Biosciences', 'Mammoth Biosciences', 'Cradle',
                'Cortical Labs', 'Ginkgo Bioworks',
            ],
            'robotics' => [
                'Figure AI', '1X Technologies', 'Pickle Robot', 'Coco',
                'Diligent Robotics', 'Chef Robotics', 'Apptronik', 'Agility Robotics',
                'Nuro', 'Waymo', 'Cruise', 'Aurora',
            ],
            'space' => [
                'SpaceX', 'Blue Origin', 'Relativity Space', 'Stoke Space', 'Vast',
                'Intuitive Machines', 'Orbit Fab', 'ABL Space Systems', 'Gravitics',
            ],
            'climate' => [
                'Redwood Materials', 'Amp Robotics', 'Gridware', 'Lumotive', 'Rivian',
            ],
            'consumer' => [
                'Airbnb', 'DoorDash', 'Instacart', 'Reddit', 'Dropbox', 'Twitch',
                'Poshmark', 'StockX', 'Goat', 'Whatnot', 'Fanatics', 'Bird',
                'Bluesky', 'Fizz', 'Shipt', 'ByteDance',
            ],
            'developer_tools' => [
                'Vercel', 'Replit', 'Postman', 'GitLab', 'HashiCorp', 'Snyk',
                'LaunchDarkly', 'PagerDuty', 'Datadog', 'Grafana Labs', 'Amplitude',
                'Benchling', 'UiPath', 'Zapier', 'Supabase', 'Plural', 'Parasail',
                'Flexport',
            ],
            'defense' => [
                'Anduril Industries', 'Castelion',
            ],
        ];

        $updated = 0;

        foreach ($categories as $category => $companyNames) {
            foreach ($companyNames as $name) {
                $count = Company::where('name', $name)
                    ->whereNull('category')
                    ->update(['category' => $category]);
                $updated += $count;
            }
        }

        $this->command->info("Updated {$updated} companies with categories.");

        // Report any uncategorized companies
        $uncategorized = Company::whereNull('category')->pluck('name');
        if ($uncategorized->isNotEmpty()) {
            $this->command->warn("Uncategorized companies: " . $uncategorized->join(', '));
        }
    }
}
