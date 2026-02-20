<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Import remote-first companies from AsyncWorldwide.com directory.
 *
 * Run: php artisan db:seed --class=AsyncWorldwideSeeder
 */
class AsyncWorldwideSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['Aha!', 'https://www.aha.io/'],
            ['Appcues', 'https://www.appcues.com/'],
            ['Arbitrum', 'https://arbitrum.io/'],
            ['Arkency', 'https://arkency.com/'],
            ['Automattic', 'https://automattic.com/'],
            ['Awesome Motive', 'https://awesomemotive.com/'],
            ['Buffer', 'https://buffer.com/'],
            ['Chili Piper', 'https://www.chilipiper.com/'],
            ['Constructor', 'https://constructor.com/'],
            ['Contra', 'https://contra.com/'],
            ['Doist', 'https://doist.com/'],
            ['DuckDuckGo', 'https://duckduckgo.com/'],
            ['Float', 'https://www.float.com/'],
            ['FM', 'https://www.fm.co/'],
            ['Ghost', 'https://ghost.org/'],
            ['GitLab', 'https://gitlab.com/'],
            ['Gorilla Logic', 'https://gorillalogic.com/'],
            ['Groove', 'https://www.groovehq.com/'],
            ['Interaction Design Foundation', 'https://www.interaction-design.org/'],
            ['Kinsta', 'https://kinsta.com/'],
            ['MailerLite', 'https://www.mailerlite.com/'],
            ['Levity', 'https://levity.ai/'],
            ['Lightdash', 'https://www.lightdash.com/'],
            ['Literal Humans', 'https://literalhumans.com/'],
            ['Liveblocks', 'https://liveblocks.io/'],
            ['Mailbird', 'https://www.getmailbird.com/'],
            ['MeetEdgar', 'https://meetedgar.com/'],
            ['Modash', 'https://www.modash.io/'],
            ['Namecheap', 'https://www.namecheap.com/'],
            ['Okta', 'https://www.okta.com/'],
            ['Omnipresent', 'https://www.omnipresent.com/'],
            ['Omniscient Digital', 'https://beomniscient.com/'],
            ['Oyster', 'https://oysterhr.com/'],
            ['Pitch', 'https://pitch.com/'],
            ['Plus AI', 'https://plusai.com/'],
            ['Remote', 'https://remote.com/'],
            ['SafetyWing', 'https://safetywing.com/'],
            ['Sanctuary Computer', 'https://www.sanctuary.computer/'],
            ['ScaleMath', 'https://scalemath.com/'],
            ['Shogun', 'https://getshogun.com/'],
            ['SimpleTexting', 'https://simpletexting.com/'],
            ['Smile.io', 'https://smile.io/'],
            ['Sporty', 'https://sporty.com/'],
            ['Springworks', 'https://www.springworks.in/'],
            ['StackBlitz', 'https://stackblitz.com/'],
            ['Storylane', 'https://www.storylane.io/'],
            ['Supabase', 'https://supabase.com/'],
            ['TestGorilla', 'https://www.testgorilla.com/'],
            ['Tether', 'https://tether.to/'],
            ['The Shelf', 'https://www.theshelf.com/'],
            ['tl;dv', 'https://tldv.io/'],
            ['Toggl', 'https://toggl.com/'],
            ['Tortuga', 'https://www.tortugabackpacks.com/'],
            ['Whereby', 'https://whereby.com/'],
            ['Windsor.ai', 'https://windsor.ai/'],
            ['YNAB', 'https://www.ynab.com/'],
            ['Zapier', 'https://zapier.com/'],
            ['Zyte', 'https://www.zyte.com/'],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($companies as [$name, $website]) {
            if (Company::where('website', $website)->exists()) {
                $this->command->info("SKIP: {$name} — already exists");
                $skipped++;

                continue;
            }

            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $i = 2;
            while (Company::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }

            Company::create([
                'name' => $name,
                'slug' => $slug,
                'website' => $website,
                'description' => 'remote-first',
                'import_source' => 'asyncworldwide.com',
                'status' => 'operating',
            ]);

            $this->command->info("CREATED: {$name}");
            $created++;
        }

        $this->command->info("Done: {$created} created, {$skipped} skipped.");
    }
}
