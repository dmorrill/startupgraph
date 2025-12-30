<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            // Top Unicorns / Most Valuable
            ['name' => 'OpenAI', 'website' => 'https://openai.com', 'description' => 'AI research company building safe and beneficial artificial general intelligence. Creator of ChatGPT and GPT models.', 'founded_date' => '2015-12-11', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'SpaceX', 'website' => 'https://spacex.com', 'description' => 'Aerospace manufacturer and space transportation company designing, manufacturing, and launching rockets and spacecraft.', 'founded_date' => '2002-03-14', 'city' => 'Hawthorne', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'ByteDance', 'website' => 'https://bytedance.com', 'description' => 'Technology company operating content platforms including TikTok, Douyin, and news aggregator Toutiao.', 'founded_date' => '2012-03-01', 'city' => 'Beijing', 'state' => null, 'country' => 'China'],
            ['name' => 'Anthropic', 'website' => 'https://anthropic.com', 'description' => 'AI safety company building reliable, interpretable, and steerable AI systems. Creator of Claude.', 'founded_date' => '2021-01-28', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Stripe', 'website' => 'https://stripe.com', 'description' => 'Financial infrastructure platform for the internet, providing payment processing and business tools.', 'founded_date' => '2010-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Databricks', 'website' => 'https://databricks.com', 'description' => 'Data and AI company providing a unified analytics platform for data engineering and data science.', 'founded_date' => '2013-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Canva', 'website' => 'https://canva.com', 'description' => 'Online design and visual communication platform making it easy to create professional designs.', 'founded_date' => '2012-01-01', 'city' => 'Sydney', 'state' => 'NSW', 'country' => 'Australia'],
            ['name' => 'Revolut', 'website' => 'https://revolut.com', 'description' => 'Digital banking and financial technology company offering banking services, currency exchange, and cryptocurrency.', 'founded_date' => '2015-07-01', 'city' => 'London', 'state' => null, 'country' => 'UK'],
            ['name' => 'Checkout.com', 'website' => 'https://checkout.com', 'description' => 'Cloud-based payments platform processing online payments for global enterprises.', 'founded_date' => '2012-01-01', 'city' => 'London', 'state' => null, 'country' => 'UK'],
            ['name' => 'Klarna', 'website' => 'https://klarna.com', 'description' => 'Buy now, pay later fintech company providing payment solutions for online shopping.', 'founded_date' => '2005-01-01', 'city' => 'Stockholm', 'state' => null, 'country' => 'Sweden'],

            // Y Combinator Alumni
            ['name' => 'Airbnb', 'website' => 'https://airbnb.com', 'description' => 'Online marketplace for lodging, vacation rentals, and tourism experiences.', 'founded_date' => '2008-08-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Dropbox', 'website' => 'https://dropbox.com', 'description' => 'File hosting and cloud storage service with file synchronization and collaboration tools.', 'founded_date' => '2007-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'DoorDash', 'website' => 'https://doordash.com', 'description' => 'Food delivery platform connecting customers with local restaurants and stores.', 'founded_date' => '2013-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Coinbase', 'website' => 'https://coinbase.com', 'description' => 'Cryptocurrency exchange platform for buying, selling, and storing digital currencies.', 'founded_date' => '2012-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Instacart', 'website' => 'https://instacart.com', 'description' => 'Grocery delivery and pick-up service connecting customers with personal shoppers.', 'founded_date' => '2012-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Reddit', 'website' => 'https://reddit.com', 'description' => 'Social news aggregation, content rating, and discussion website organized into communities.', 'founded_date' => '2005-06-23', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Twitch', 'website' => 'https://twitch.tv', 'description' => 'Live streaming platform primarily focused on video game live streaming and esports.', 'founded_date' => '2011-06-06', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'GitLab', 'website' => 'https://gitlab.com', 'description' => 'DevOps platform providing source code management, CI/CD, and collaboration tools.', 'founded_date' => '2011-10-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Zapier', 'website' => 'https://zapier.com', 'description' => 'Automation platform connecting apps and services to automate workflows without coding.', 'founded_date' => '2011-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Gusto', 'website' => 'https://gusto.com', 'description' => 'Cloud-based payroll, benefits, and HR platform for small and medium businesses.', 'founded_date' => '2011-11-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Deel', 'website' => 'https://deel.com', 'description' => 'Global payroll and compliance platform for hiring international employees and contractors.', 'founded_date' => '2019-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Rippling', 'website' => 'https://rippling.com', 'description' => 'Workforce management platform combining HR, IT, and finance in one system.', 'founded_date' => '2016-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Cruise', 'website' => 'https://getcruise.com', 'description' => 'Self-driving car company developing autonomous vehicle technology.', 'founded_date' => '2013-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'PagerDuty', 'website' => 'https://pagerduty.com', 'description' => 'Digital operations management platform for real-time incident response.', 'founded_date' => '2009-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Whatnot', 'website' => 'https://whatnot.com', 'description' => 'Live shopping platform for collectibles, trading cards, and unique items.', 'founded_date' => '2019-01-01', 'city' => 'Los Angeles', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Benchling', 'website' => 'https://benchling.com', 'description' => 'Cloud platform for life sciences R&D with tools for biotech research.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Ginkgo Bioworks', 'website' => 'https://ginkgobioworks.com', 'description' => 'Biotech company using genetic engineering to produce bacteria for industrial applications.', 'founded_date' => '2008-01-01', 'city' => 'Boston', 'state' => 'MA', 'country' => 'USA'],
            ['name' => 'Amplitude', 'website' => 'https://amplitude.com', 'description' => 'Product analytics platform helping companies understand user behavior.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Supabase', 'website' => 'https://supabase.com', 'description' => 'Open source Firebase alternative providing database, auth, and storage services.', 'founded_date' => '2020-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Replit', 'website' => 'https://replit.com', 'description' => 'Browser-based IDE and collaborative coding platform with AI coding assistance.', 'founded_date' => '2016-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],

            // AI Startups
            ['name' => 'xAI', 'website' => 'https://x.ai', 'description' => 'AI company founded by Elon Musk developing the Grok AI model.', 'founded_date' => '2023-03-09', 'city' => 'Palo Alto', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Groq', 'website' => 'https://groq.com', 'description' => 'AI chip company building high-performance inference accelerators for AI workloads.', 'founded_date' => '2016-01-01', 'city' => 'Mountain View', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Cerebras Systems', 'website' => 'https://cerebras.net', 'description' => 'AI hardware company that built the largest chip ever made for AI compute.', 'founded_date' => '2016-01-01', 'city' => 'Sunnyvale', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Cohere', 'website' => 'https://cohere.com', 'description' => 'Enterprise AI platform providing large language models for business applications.', 'founded_date' => '2019-01-01', 'city' => 'Toronto', 'state' => 'ON', 'country' => 'Canada'],
            ['name' => 'Hugging Face', 'website' => 'https://huggingface.co', 'description' => 'AI community platform and hub for machine learning models and datasets.', 'founded_date' => '2016-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'Scale AI', 'website' => 'https://scale.com', 'description' => 'Data platform for AI providing high-quality training data and AI infrastructure.', 'founded_date' => '2016-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Stability AI', 'website' => 'https://stability.ai', 'description' => 'AI company behind Stable Diffusion and other generative AI models.', 'founded_date' => '2019-01-01', 'city' => 'London', 'state' => null, 'country' => 'UK'],
            ['name' => 'Midjourney', 'website' => 'https://midjourney.com', 'description' => 'AI research lab creating text-to-image AI models for creative applications.', 'founded_date' => '2021-08-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Runway', 'website' => 'https://runwayml.com', 'description' => 'AI creative tools company building generative AI for video and image creation.', 'founded_date' => '2018-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'Synthesia', 'website' => 'https://synthesia.io', 'description' => 'AI video generation platform creating videos with AI avatars.', 'founded_date' => '2017-01-01', 'city' => 'London', 'state' => null, 'country' => 'UK'],
            ['name' => 'Harvey', 'website' => 'https://harvey.ai', 'description' => 'AI platform for law firms automating legal work and research.', 'founded_date' => '2022-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Anyscale', 'website' => 'https://anyscale.com', 'description' => 'Platform for scaling AI applications built on the Ray open source project.', 'founded_date' => '2019-12-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Perplexity', 'website' => 'https://perplexity.ai', 'description' => 'AI-powered answer engine providing conversational search with citations.', 'founded_date' => '2022-08-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Character.AI', 'website' => 'https://character.ai', 'description' => 'AI chatbot platform allowing users to create and interact with AI characters.', 'founded_date' => '2021-09-01', 'city' => 'Palo Alto', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Inflection AI', 'website' => 'https://inflection.ai', 'description' => 'AI company building personal AI assistants, creators of Pi.', 'founded_date' => '2022-01-01', 'city' => 'Palo Alto', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Mistral AI', 'website' => 'https://mistral.ai', 'description' => 'French AI company building open and efficient large language models.', 'founded_date' => '2023-04-01', 'city' => 'Paris', 'state' => null, 'country' => 'France'],
            ['name' => 'Together AI', 'website' => 'https://together.ai', 'description' => 'Cloud platform for running and fine-tuning open source AI models.', 'founded_date' => '2022-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Adept', 'website' => 'https://adept.ai', 'description' => 'AI research lab building AI that can take actions in software.', 'founded_date' => '2022-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Jasper', 'website' => 'https://jasper.ai', 'description' => 'AI content creation platform for marketing and business writing.', 'founded_date' => '2021-01-01', 'city' => 'Austin', 'state' => 'TX', 'country' => 'USA'],
            ['name' => 'Copy.ai', 'website' => 'https://copy.ai', 'description' => 'AI writing assistant for marketing copy, blog posts, and content.', 'founded_date' => '2020-10-01', 'city' => 'Memphis', 'state' => 'TN', 'country' => 'USA'],
            ['name' => 'Glean', 'website' => 'https://glean.com', 'description' => 'Enterprise AI search platform connecting all company knowledge.', 'founded_date' => '2019-05-01', 'city' => 'Palo Alto', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Writer', 'website' => 'https://writer.com', 'description' => 'Enterprise AI platform for content generation with brand consistency.', 'founded_date' => '2020-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Weights & Biases', 'website' => 'https://wandb.ai', 'description' => 'MLOps platform for experiment tracking and model management.', 'founded_date' => '2017-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Mosaic ML', 'website' => 'https://mosaicml.com', 'description' => 'Platform for efficient training of large AI models (acquired by Databricks).', 'founded_date' => '2021-06-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Cursor', 'website' => 'https://cursor.com', 'description' => 'AI-powered code editor built for pair programming with AI.', 'founded_date' => '2022-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],

            // Fintech
            ['name' => 'Plaid', 'website' => 'https://plaid.com', 'description' => 'Financial services API platform connecting apps to users bank accounts.', 'founded_date' => '2013-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Chime', 'website' => 'https://chime.com', 'description' => 'Digital banking platform offering fee-free mobile banking services.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Brex', 'website' => 'https://brex.com', 'description' => 'Corporate credit card and spend management platform for startups.', 'founded_date' => '2017-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Ramp', 'website' => 'https://ramp.com', 'description' => 'Corporate card and spend management platform with automated expense tracking.', 'founded_date' => '2019-03-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'Mercury', 'website' => 'https://mercury.com', 'description' => 'Banking for startups with checking, savings, and credit products.', 'founded_date' => '2017-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Navan', 'website' => 'https://navan.com', 'description' => 'Corporate travel and expense management platform (formerly TripActions).', 'founded_date' => '2015-01-01', 'city' => 'Palo Alto', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Marqeta', 'website' => 'https://marqeta.com', 'description' => 'Card issuing platform enabling companies to create custom payment cards.', 'founded_date' => '2010-01-01', 'city' => 'Oakland', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Affirm', 'website' => 'https://affirm.com', 'description' => 'Buy now, pay later platform offering transparent installment payments.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Robinhood', 'website' => 'https://robinhood.com', 'description' => 'Commission-free stock trading and investing app.', 'founded_date' => '2013-04-18', 'city' => 'Menlo Park', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Wise', 'website' => 'https://wise.com', 'description' => 'International money transfer service with transparent exchange rates.', 'founded_date' => '2011-01-01', 'city' => 'London', 'state' => null, 'country' => 'UK'],

            // Developer Tools / Infrastructure
            ['name' => 'Vercel', 'website' => 'https://vercel.com', 'description' => 'Frontend cloud platform for deploying web applications with seamless developer experience.', 'founded_date' => '2015-11-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Figma', 'website' => 'https://figma.com', 'description' => 'Collaborative interface design tool running in the browser.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Notion', 'website' => 'https://notion.so', 'description' => 'All-in-one workspace for notes, docs, wikis, and project management.', 'founded_date' => '2013-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Airtable', 'website' => 'https://airtable.com', 'description' => 'Spreadsheet-database hybrid platform for organizing anything.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Linear', 'website' => 'https://linear.app', 'description' => 'Issue tracking and project management tool for software teams.', 'founded_date' => '2019-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Retool', 'website' => 'https://retool.com', 'description' => 'Low-code platform for building internal tools and business applications.', 'founded_date' => '2017-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Webflow', 'website' => 'https://webflow.com', 'description' => 'No-code website builder and CMS for designers and developers.', 'founded_date' => '2013-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Postman', 'website' => 'https://postman.com', 'description' => 'API development platform for building and testing APIs.', 'founded_date' => '2014-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Snyk', 'website' => 'https://snyk.io', 'description' => 'Developer security platform for finding and fixing vulnerabilities.', 'founded_date' => '2015-01-01', 'city' => 'Boston', 'state' => 'MA', 'country' => 'USA'],
            ['name' => 'HashiCorp', 'website' => 'https://hashicorp.com', 'description' => 'Infrastructure automation software including Terraform and Vault.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Grafana Labs', 'website' => 'https://grafana.com', 'description' => 'Open source analytics and monitoring platform.', 'founded_date' => '2014-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'LaunchDarkly', 'website' => 'https://launchdarkly.com', 'description' => 'Feature management platform for controlling feature flags.', 'founded_date' => '2014-01-01', 'city' => 'Oakland', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Datadog', 'website' => 'https://datadoghq.com', 'description' => 'Cloud monitoring and security platform for infrastructure and applications.', 'founded_date' => '2010-06-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],

            // Health Tech
            ['name' => 'Devoted Health', 'website' => 'https://devoted.com', 'description' => 'Medicare Advantage health insurance company focused on seniors.', 'founded_date' => '2017-01-01', 'city' => 'Waltham', 'state' => 'MA', 'country' => 'USA'],
            ['name' => 'Ro', 'website' => 'https://ro.co', 'description' => 'Direct-to-patient healthcare company providing telehealth services.', 'founded_date' => '2017-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'Hims & Hers', 'website' => 'https://forhims.com', 'description' => 'Telehealth platform for personalized health and wellness products.', 'founded_date' => '2017-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Tempus', 'website' => 'https://tempus.com', 'description' => 'Healthcare AI company making precision medicine accessible.', 'founded_date' => '2015-08-01', 'city' => 'Chicago', 'state' => 'IL', 'country' => 'USA'],
            ['name' => 'Noom', 'website' => 'https://noom.com', 'description' => 'Psychology-based weight loss and health app.', 'founded_date' => '2008-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'Calm', 'website' => 'https://calm.com', 'description' => 'Mental health and meditation app for sleep and relaxation.', 'founded_date' => '2012-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Headspace', 'website' => 'https://headspace.com', 'description' => 'Meditation and mindfulness app with guided content.', 'founded_date' => '2010-01-01', 'city' => 'Santa Monica', 'state' => 'CA', 'country' => 'USA'],

            // E-commerce / Marketplaces
            ['name' => 'Faire', 'website' => 'https://faire.com', 'description' => 'B2B wholesale marketplace connecting retailers with independent brands.', 'founded_date' => '2017-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Flexport', 'website' => 'https://flexport.com', 'description' => 'Freight forwarding and supply chain platform for global trade.', 'founded_date' => '2013-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Fanatics', 'website' => 'https://fanatics.com', 'description' => 'Sports merchandise and collectibles company.', 'founded_date' => '1995-01-01', 'city' => 'Jacksonville', 'state' => 'FL', 'country' => 'USA'],
            ['name' => 'StockX', 'website' => 'https://stockx.com', 'description' => 'Online marketplace for sneakers, streetwear, and collectibles.', 'founded_date' => '2015-02-01', 'city' => 'Detroit', 'state' => 'MI', 'country' => 'USA'],
            ['name' => 'Goat', 'website' => 'https://goat.com', 'description' => 'Sneaker and apparel marketplace with authentication services.', 'founded_date' => '2015-07-01', 'city' => 'Culver City', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Poshmark', 'website' => 'https://poshmark.com', 'description' => 'Social commerce marketplace for buying and selling fashion.', 'founded_date' => '2011-01-01', 'city' => 'Redwood City', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Shipt', 'website' => 'https://shipt.com', 'description' => 'Same-day delivery service for groceries and essentials.', 'founded_date' => '2014-01-01', 'city' => 'Birmingham', 'state' => 'AL', 'country' => 'USA'],

            // Transportation / Mobility
            ['name' => 'Waymo', 'website' => 'https://waymo.com', 'description' => 'Autonomous vehicle technology company developing self-driving cars.', 'founded_date' => '2009-01-01', 'city' => 'Mountain View', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Rivian', 'website' => 'https://rivian.com', 'description' => 'Electric vehicle manufacturer making trucks and SUVs.', 'founded_date' => '2009-01-01', 'city' => 'Irvine', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Aurora', 'website' => 'https://aurora.tech', 'description' => 'Self-driving technology company building autonomous trucks.', 'founded_date' => '2017-01-01', 'city' => 'Pittsburgh', 'state' => 'PA', 'country' => 'USA'],
            ['name' => 'Nuro', 'website' => 'https://nuro.ai', 'description' => 'Autonomous delivery vehicle company for goods transportation.', 'founded_date' => '2016-06-01', 'city' => 'Mountain View', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Bird', 'website' => 'https://bird.co', 'description' => 'Electric scooter sharing company for urban transportation.', 'founded_date' => '2017-01-01', 'city' => 'Miami', 'state' => 'FL', 'country' => 'USA'],

            // Enterprise / B2B
            ['name' => 'ServiceTitan', 'website' => 'https://servicetitan.com', 'description' => 'Software platform for home service businesses.', 'founded_date' => '2012-01-01', 'city' => 'Glendale', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Toast', 'website' => 'https://toasttab.com', 'description' => 'Restaurant point-of-sale and management platform.', 'founded_date' => '2011-01-01', 'city' => 'Boston', 'state' => 'MA', 'country' => 'USA'],
            ['name' => 'Celonis', 'website' => 'https://celonis.com', 'description' => 'Process mining platform for business process optimization.', 'founded_date' => '2011-01-01', 'city' => 'Munich', 'state' => null, 'country' => 'Germany'],
            ['name' => 'UiPath', 'website' => 'https://uipath.com', 'description' => 'Robotic process automation platform for enterprise automation.', 'founded_date' => '2005-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA'],
            ['name' => 'Monday.com', 'website' => 'https://monday.com', 'description' => 'Work operating system for project management and collaboration.', 'founded_date' => '2012-01-01', 'city' => 'Tel Aviv', 'state' => null, 'country' => 'Israel'],
            ['name' => 'Asana', 'website' => 'https://asana.com', 'description' => 'Work management platform for teams to organize and track work.', 'founded_date' => '2008-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'ClickUp', 'website' => 'https://clickup.com', 'description' => 'Productivity platform combining tasks, docs, goals, and more.', 'founded_date' => '2017-01-01', 'city' => 'San Diego', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Miro', 'website' => 'https://miro.com', 'description' => 'Visual collaboration platform for distributed teams.', 'founded_date' => '2011-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Loom', 'website' => 'https://loom.com', 'description' => 'Video messaging platform for async workplace communication.', 'founded_date' => '2015-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA'],
            ['name' => 'Calendly', 'website' => 'https://calendly.com', 'description' => 'Scheduling automation platform for meeting coordination.', 'founded_date' => '2013-01-01', 'city' => 'Atlanta', 'state' => 'GA', 'country' => 'USA'],
        ];

        foreach ($companies as $companyData) {
            $companyData['slug'] = Str::slug($companyData['name']);
            Company::create($companyData);
        }
    }
}
