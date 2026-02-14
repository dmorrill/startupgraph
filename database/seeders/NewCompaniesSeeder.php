<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            // Dev Tools / Infrastructure
            ['name' => 'Railway', 'website' => 'https://railway.app', 'description' => 'Cloud platform for deploying and managing applications with instant infrastructure provisioning.', 'founded_date' => '2020-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'PlanetScale', 'website' => 'https://planetscale.com', 'description' => 'Serverless MySQL database platform built on Vitess with branching and non-blocking schema changes.', 'founded_date' => '2018-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Neon', 'website' => 'https://neon.tech', 'description' => 'Serverless Postgres database with autoscaling, branching, and a generous free tier.', 'founded_date' => '2021-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Turso', 'website' => 'https://turso.tech', 'description' => 'Edge database built on libSQL, a fork of SQLite, for globally distributed low-latency data.', 'founded_date' => '2022-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Resend', 'website' => 'https://resend.com', 'description' => 'Email API for developers with a focus on deliverability and modern developer experience.', 'founded_date' => '2022-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Raycast', 'website' => 'https://raycast.com', 'description' => 'Productivity launcher for macOS replacing Spotlight with extensible commands and AI integration.', 'founded_date' => '2020-01-01', 'city' => 'London', 'state' => null, 'country' => 'UK', 'category' => 'developer_tools'],
            ['name' => 'Warp', 'website' => 'https://warp.dev', 'description' => 'Modern terminal reimagined with AI, collaborative features, and a block-based editor.', 'founded_date' => '2020-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Windsurf', 'website' => 'https://windsurf.com', 'description' => 'AI-powered code editor (formerly Codeium) with agentic coding capabilities.', 'founded_date' => '2021-01-01', 'city' => 'Mountain View', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Lovable', 'website' => 'https://lovable.dev', 'description' => 'AI-powered full-stack app builder that generates production-ready applications from prompts.', 'founded_date' => '2023-01-01', 'city' => 'Stockholm', 'state' => null, 'country' => 'Sweden', 'category' => 'developer_tools'],
            ['name' => 'Bolt', 'website' => 'https://bolt.new', 'description' => 'AI web development agent that builds and deploys full-stack apps in the browser.', 'founded_date' => '2023-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Dagger', 'website' => 'https://dagger.io', 'description' => 'Programmable CI/CD engine that runs pipelines as code in containers.', 'founded_date' => '2021-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Fly.io', 'website' => 'https://fly.io', 'description' => 'Platform for running full-stack apps and databases close to users worldwide.', 'founded_date' => '2017-01-01', 'city' => 'Chicago', 'state' => 'IL', 'country' => 'USA', 'category' => 'developer_tools'],
            ['name' => 'Render', 'website' => 'https://render.com', 'description' => 'Unified cloud platform to build and run apps with free TLS, global CDN, and auto deploys.', 'founded_date' => '2018-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'developer_tools'],

            // AI / ML
            ['name' => 'Modal', 'website' => 'https://modal.com', 'description' => 'Serverless cloud platform for running GPU-accelerated AI workloads and data pipelines.', 'founded_date' => '2021-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Replicate', 'website' => 'https://replicate.com', 'description' => 'Platform for running open-source AI models in the cloud via a simple API.', 'founded_date' => '2019-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Coreweave', 'website' => 'https://coreweave.com', 'description' => 'GPU cloud provider specializing in large-scale AI and HPC compute infrastructure.', 'founded_date' => '2017-01-01', 'city' => 'Livingston', 'state' => 'NJ', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Poolside', 'website' => 'https://poolside.ai', 'description' => 'AI company building foundation models purpose-built for software engineering.', 'founded_date' => '2023-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'ElevenLabs', 'website' => 'https://elevenlabs.io', 'description' => 'AI voice technology company offering realistic text-to-speech and voice cloning.', 'founded_date' => '2022-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Pika', 'website' => 'https://pika.art', 'description' => 'AI video generation platform for creating and editing videos from text and images.', 'founded_date' => '2023-04-01', 'city' => 'Palo Alto', 'state' => 'CA', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Suno', 'website' => 'https://suno.com', 'description' => 'AI music generation platform creating full songs from text prompts.', 'founded_date' => '2023-01-01', 'city' => 'Cambridge', 'state' => 'MA', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Udio', 'website' => 'https://udio.com', 'description' => 'AI music creation tool generating high-quality songs with vocals and instrumentation.', 'founded_date' => '2023-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Deepgram', 'website' => 'https://deepgram.com', 'description' => 'AI speech-to-text and text-to-speech API platform for developers.', 'founded_date' => '2015-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Pinecone', 'website' => 'https://pinecone.io', 'description' => 'Vector database for building high-performance AI applications with similarity search.', 'founded_date' => '2019-01-01', 'city' => 'San Francisco', 'state' => 'CA', 'country' => 'USA', 'category' => 'ai_ml'],
            ['name' => 'Weaviate', 'website' => 'https://weaviate.io', 'description' => 'Open-source vector database for AI-native applications and semantic search.', 'founded_date' => '2019-01-01', 'city' => 'Amsterdam', 'state' => null, 'country' => 'Netherlands', 'category' => 'ai_ml'],

            // Consumer
            ['name' => 'Arc', 'website' => 'https://arc.net', 'description' => 'Reimagined web browser from The Browser Company with spaces, profiles, and built-in tools.', 'founded_date' => '2019-01-01', 'city' => 'New York', 'state' => 'NY', 'country' => 'USA', 'category' => 'consumer'],
        ];

        foreach ($companies as $companyData) {
            $companyData['slug'] = Str::slug($companyData['name']);
            Company::updateOrCreate(
                ['slug' => $companyData['slug']],
                $companyData
            );
        }

        $this->command->info('Seeded ' . count($companies) . ' new companies.');
    }
}
