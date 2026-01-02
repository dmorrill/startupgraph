<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class LinkedInUrlSeeder extends Seeder
{
    public function run(): void
    {
        $linkedInUrls = [
            'adept' => 'https://www.linkedin.com/company/adeptai/',
            'affirm' => 'https://www.linkedin.com/company/affirm/',
            'airbnb' => 'https://www.linkedin.com/company/airbnb/',
            'airtable' => 'https://www.linkedin.com/company/airtable/',
            'amplitude' => 'https://www.linkedin.com/company/amplitude-analytics/',
            'anthropic' => 'https://www.linkedin.com/company/anthropicresearch/',
            'anyscale' => 'https://www.linkedin.com/company/joinanyscale/',
            'asana' => 'https://www.linkedin.com/company/asana/',
            'aurora' => 'https://www.linkedin.com/company/aurora-innovation/',
            'benchling' => 'https://www.linkedin.com/company/benchling/',
            'bird' => 'https://www.linkedin.com/company/bird-rides/',
            'brex' => 'https://www.linkedin.com/company/brexhq/',
            'bytedance' => 'https://www.linkedin.com/company/bytedance/',
            'calendly' => 'https://www.linkedin.com/company/calendly/',
            'calm' => 'https://www.linkedin.com/company/calm/',
            'canva' => 'https://www.linkedin.com/company/canva/',
            'celonis' => 'https://www.linkedin.com/company/celonis/',
            'cerebras-systems' => 'https://www.linkedin.com/company/cerebras-systems/',
            'characterai' => 'https://www.linkedin.com/company/character-ai/',
            'checkoutcom' => 'https://www.linkedin.com/company/checkout/',
            'chime' => 'https://www.linkedin.com/company/chime-card/',
            'clickup' => 'https://www.linkedin.com/company/clickup-app/',
            'cohere' => 'https://www.linkedin.com/company/cohere-ai/',
            'coinbase' => 'https://www.linkedin.com/company/coinbase/',
            'copyai' => 'https://www.linkedin.com/company/copy-ai/',
            'cruise' => 'https://www.linkedin.com/company/cruise/',
            'cursor' => 'https://www.linkedin.com/company/cursorai/',
            'databricks' => 'https://www.linkedin.com/company/databricks/',
            'datadog' => 'https://www.linkedin.com/company/datadog/',
            'deel' => 'https://www.linkedin.com/company/deel/',
            'devoted-health' => 'https://www.linkedin.com/company/devoted-health/',
            'doordash' => 'https://www.linkedin.com/company/doordash/',
            'dropbox' => 'https://www.linkedin.com/company/dropbox/',
            'faire' => 'https://www.linkedin.com/company/faire-wholesale/',
            'fanatics' => 'https://www.linkedin.com/company/fanatics-inc/',
            'figma' => 'https://www.linkedin.com/company/figma/',
            'flexport' => 'https://www.linkedin.com/company/flexport/',
            'ginkgo-bioworks' => 'https://www.linkedin.com/company/ginkgo-bioworks/',
            'gitlab' => 'https://www.linkedin.com/company/gitlab-com/',
            'glean' => 'https://www.linkedin.com/company/gleanwork/',
            'goat' => 'https://www.linkedin.com/company/goat/',
            'grafana-labs' => 'https://www.linkedin.com/company/grafana-labs/',
            'groq' => 'https://www.linkedin.com/company/groq/',
            'gusto' => 'https://www.linkedin.com/company/gustohq/',
            'harvey' => 'https://www.linkedin.com/company/harvey-ai/',
            'hashicorp' => 'https://www.linkedin.com/company/hashicorp/',
            'headspace' => 'https://www.linkedin.com/company/headspace-meditation-limited/',
            'hims-hers' => 'https://www.linkedin.com/company/hims/',
            'hugging-face' => 'https://www.linkedin.com/company/huggingface/',
            'inflection-ai' => 'https://www.linkedin.com/company/inflectionai/',
            'instacart' => 'https://www.linkedin.com/company/instacart/',
            'jasper' => 'https://www.linkedin.com/company/heyjasperai/',
            'klarna' => 'https://www.linkedin.com/company/klarna/',
            'launchdarkly' => 'https://www.linkedin.com/company/launchdarkly/',
            'linear' => 'https://www.linkedin.com/company/linearapp/',
            'loom' => 'https://www.linkedin.com/company/useloom/',
            'marqeta' => 'https://www.linkedin.com/company/marqeta/',
            'mercury' => 'https://www.linkedin.com/company/mercuryhq/',
            'midjourney' => 'https://www.linkedin.com/company/midjourney/',
            'miro' => 'https://www.linkedin.com/company/mirohq/',
            'mistral-ai' => 'https://www.linkedin.com/company/mistral-ai/',
            'mondaycom' => 'https://www.linkedin.com/company/mondaydotcom/',
            'mosaic-ml' => 'https://www.linkedin.com/company/mosaicml/',
            'navan' => 'https://www.linkedin.com/company/navan/',
            'noom' => 'https://www.linkedin.com/company/noom-inc-/',
            'notion' => 'https://www.linkedin.com/company/notionhq/',
            'nuro' => 'https://www.linkedin.com/company/nuro-inc/',
            'openai' => 'https://www.linkedin.com/company/openai/',
            'pagerduty' => 'https://www.linkedin.com/company/pagerduty/',
            'perplexity' => 'https://www.linkedin.com/company/perplexity-ai/',
            'plaid' => 'https://www.linkedin.com/company/plaid-/',
            'poshmark' => 'https://www.linkedin.com/company/poshmark/',
            'postman' => 'https://www.linkedin.com/company/postman-platform/',
            'ramp' => 'https://www.linkedin.com/company/ramp/',
            'reddit' => 'https://www.linkedin.com/company/reddit-com/',
            'replit' => 'https://www.linkedin.com/company/repl-it/',
            'retool' => 'https://www.linkedin.com/company/retoolhq/',
            'revolut' => 'https://www.linkedin.com/company/revolut/',
            'rippling' => 'https://www.linkedin.com/company/rippling/',
            'rivian' => 'https://www.linkedin.com/company/rivian/',
            'ro' => 'https://www.linkedin.com/company/ro-co/',
            'robinhood' => 'https://www.linkedin.com/company/robinhood/',
            'runway' => 'https://www.linkedin.com/company/runwayml/',
            'scale-ai' => 'https://www.linkedin.com/company/scaleai/',
            'servicetitan' => 'https://www.linkedin.com/company/servicetitan/',
            'shipt' => 'https://www.linkedin.com/company/shipt/',
            'snyk' => 'https://www.linkedin.com/company/snyk/',
            'spacex' => 'https://www.linkedin.com/company/spacex/',
            'stability-ai' => 'https://www.linkedin.com/company/stability-ai/',
            'stockx' => 'https://www.linkedin.com/company/stockx/',
            'stripe' => 'https://www.linkedin.com/company/stripe/',
            'supabase' => 'https://www.linkedin.com/company/supabase/',
            'synthesia' => 'https://www.linkedin.com/company/synthesia-technologies/',
            'tempus' => 'https://www.linkedin.com/company/tempus-ai/',
            'toast' => 'https://www.linkedin.com/company/toast/',
            'together-ai' => 'https://www.linkedin.com/company/togetherai/',
            'twitch' => 'https://www.linkedin.com/company/twitch-tv/',
            'uipath' => 'https://www.linkedin.com/company/uipath/',
            'vercel' => 'https://www.linkedin.com/company/vercel/',
            'waymo' => 'https://www.linkedin.com/company/waymo/',
            'webflow' => 'https://www.linkedin.com/company/webflow-inc-/',
            'weights-biases' => 'https://www.linkedin.com/company/wandb/',
            'whatnot' => 'https://www.linkedin.com/company/whatnot-inc/',
            'wise' => 'https://www.linkedin.com/company/wiseaccount/',
            'writer' => 'https://www.linkedin.com/company/writer/',
            'xai' => 'https://www.linkedin.com/company/xai/',
            'zapier' => 'https://www.linkedin.com/company/zapier/',
        ];

        $updated = 0;
        foreach ($linkedInUrls as $slug => $url) {
            $company = Company::where('slug', $slug)->first();
            if ($company) {
                $company->update(['linkedin_url' => $url]);
                $updated++;
            }
        }

        $this->command->info("Updated {$updated} companies with LinkedIn URLs.");
    }
}
