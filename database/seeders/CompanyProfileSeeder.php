<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAnthropic();
        $this->seedOpenAI();
        $this->seedStripe();
        $this->seedDatabricks();
        $this->seedFigma();
        $this->seedNotion();
        $this->seedRippling();
        $this->seedCanva();
    }

    private function seedAnthropic(): void
    {
        $company = Company::where('slug', 'anthropic')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'Claude AI assistant available in multiple models (Opus, Sonnet, Haiku)',
                'Claude.ai consumer product for chat and document analysis',
                'Developer API for building AI-powered applications',
                'Claude Code for programming assistance and code generation',
                'Focus on AI safety, interpretability, and alignment research',
                'Constitutional AI approach to training safe, helpful assistants',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Dario Amodei', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/dario-amodei-3934934/'],
            ['name' => 'Daniela Amodei', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/daniela-amodei-797b862/'],
            ['name' => 'Tom Brown', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/tom-brown-6b7b5a1/'],
            ['name' => 'Chris Olah', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/chris-olah-7a5b7a1/'],
            ['name' => 'Mike Krieger', 'role' => 'Chief Product Officer', 'linkedin_url' => 'https://www.linkedin.com/in/mikekrieger/'],
        ]);
    }

    private function seedOpenAI(): void
    {
        $company = Company::where('slug', 'openai')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'ChatGPT conversational AI with GPT-4o and GPT-4.1 models',
                'OpenAI API for developers with text, image, and audio capabilities',
                'DALL-E 3 for AI image generation',
                'Sora for AI video generation from text prompts',
                'GPT-4o multimodal model accepting text, audio, image, and video',
                'Advanced reasoning models (o1, o3) for complex problem-solving',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Sam Altman', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/samaltman/'],
            ['name' => 'Greg Brockman', 'role' => 'President', 'linkedin_url' => 'https://www.linkedin.com/in/thegdb/'],
            ['name' => 'Mira Murati', 'role' => 'Former CTO', 'is_current' => false],
            ['name' => 'Ilya Sutskever', 'role' => 'Co-founder & Former Chief Scientist', 'is_current' => false],
        ]);
    }

    private function seedStripe(): void
    {
        $company = Company::where('slug', 'stripe')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'Payment processing supporting 100+ payment methods and 135+ currencies',
                'Stripe Connect for platforms to embed payments and financial services',
                'Stripe Billing for subscriptions and usage-based billing',
                'Stripe Atlas for incorporating US businesses from anywhere',
                'Stripe Radar for ML-powered fraud detection',
                'Stripe Terminal for in-person payments with developer SDKs',
                'Revenue and Finance Automation suite (Tax, Sigma, Revenue Recognition)',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Patrick Collison', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/patrickcollison/'],
            ['name' => 'John Collison', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/johncollison/'],
            ['name' => 'Claire Hughes Johnson', 'role' => 'Former COO', 'is_current' => false],
            ['name' => 'Will Gaybrick', 'role' => 'President of Product & Business', 'linkedin_url' => 'https://www.linkedin.com/in/willgaybrick/'],
        ]);
    }

    private function seedDatabricks(): void
    {
        $company = Company::where('slug', 'databricks')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'Data Lakehouse platform combining data warehouse and data lake',
                'Unity Catalog for centralized governance, access control, and data discovery',
                'Databricks SQL for analytics and BI workloads',
                'MLflow for machine learning lifecycle management',
                'Delta Lake open-source storage layer with ACID transactions',
                'Mosaic AI for building and deploying generative AI applications',
                'Lakehouse Federation for unified access across external data sources',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Ali Ghodsi', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/alighodsi/'],
            ['name' => 'Matei Zaharia', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/mateizaharia/'],
            ['name' => 'Reynold Xin', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/rxin/'],
            ['name' => 'Ion Stoica', 'role' => 'Co-founder & Executive Chairman', 'linkedin_url' => 'https://www.linkedin.com/in/ion-stoica-7b56a/'],
        ]);
    }

    private function seedFigma(): void
    {
        $company = Company::where('slug', 'figma')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'Browser-based collaborative design tool for UI/UX',
                'FigJam for online whiteboarding and brainstorming',
                'Dev Mode for developer handoff with code snippets',
                'Design systems with shared component libraries',
                'Real-time multiplayer collaboration',
                'Prototyping with interactive transitions and animations',
                'Figma AI for automated design suggestions',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Dylan Field', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/dylanfield/'],
            ['name' => 'Evan Wallace', 'role' => 'Co-founder & Former CTO', 'is_current' => false],
            ['name' => 'Yuhki Yamashita', 'role' => 'Chief Product Officer', 'linkedin_url' => 'https://www.linkedin.com/in/yuhkiyamashita/'],
        ]);
    }

    private function seedNotion(): void
    {
        $company = Company::where('slug', 'notion')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'All-in-one workspace for notes, docs, wikis, and projects',
                'Notion AI for writing assistance and Q&A across workspace',
                'Database views (table, board, calendar, gallery, timeline)',
                'Team wikis and knowledge management',
                'Project and task management with custom workflows',
                'Notion Calendar for scheduling and time management',
                'API for integrations and automations',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Ivan Zhao', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/ivanhzhao/'],
            ['name' => 'Simon Last', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/simonlast/'],
            ['name' => 'Akshay Kothari', 'role' => 'COO', 'linkedin_url' => 'https://www.linkedin.com/in/akothari/'],
        ]);
    }

    private function seedRippling(): void
    {
        $company = Company::where('slug', 'rippling')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'Unified HR platform for payroll, benefits, and compliance',
                'IT management with device management and app provisioning',
                'Spend management with corporate cards and expense tracking',
                'Global payroll supporting 185+ countries',
                'Identity and access management across business apps',
                'Time and attendance tracking',
                'Workflow automation connecting HR, IT, and Finance',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Parker Conrad', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/parkerconrad/'],
            ['name' => 'Prasanna Sankar', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/prasannasankar/'],
        ]);
    }

    private function seedCanva(): void
    {
        $company = Company::where('slug', 'canva')->first();
        if (! $company) {
            return;
        }

        $company->update([
            'product_highlights' => [
                'Browser-based graphic design platform with drag-and-drop editor',
                'Thousands of templates for social media, presentations, and marketing',
                'Canva Pro with Brand Kit, background remover, and Magic Resize',
                'Canva for Teams with collaboration and approval workflows',
                'AI-powered Magic Design, Magic Write, and Magic Edit tools',
                'Video editing and animation capabilities',
                'Print on demand for business cards, posters, and merchandise',
            ],
            'profile_refreshed_at' => now(),
        ]);

        $this->createPeopleForCompany($company, [
            ['name' => 'Melanie Perkins', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/melanieperkins/'],
            ['name' => 'Cliff Obrecht', 'role' => 'Co-founder & COO', 'linkedin_url' => 'https://www.linkedin.com/in/cliff-obrecht-4a87a31b/'],
            ['name' => 'Cameron Adams', 'role' => 'Co-founder & Chief Product Officer', 'linkedin_url' => 'https://www.linkedin.com/in/themaninblue/'],
        ]);
    }

    private function createPeopleForCompany(Company $company, array $peopleData): void
    {
        foreach ($peopleData as $personData) {
            $slug = Str::slug($personData['name']);
            $isCurrent = $personData['is_current'] ?? true;

            $person = Person::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $personData['name'],
                    'slug' => $slug,
                    'linkedin_url' => $personData['linkedin_url'] ?? null,
                ]
            );

            // Attach to company if not already attached
            if (! $company->people()->where('person_id', $person->id)->exists()) {
                $company->people()->attach($person->id, [
                    'role' => $personData['role'],
                    'is_current' => $isCurrent,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
