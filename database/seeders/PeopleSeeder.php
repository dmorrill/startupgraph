<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PeopleSeeder extends Seeder
{
    public function run(): void
    {
        $companyPeople = [
            'spacex' => [
                ['name' => 'Elon Musk', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/elonmusk/'],
                ['name' => 'Gwynne Shotwell', 'role' => 'President & COO', 'linkedin_url' => 'https://www.linkedin.com/in/gwynne-shotwell-6318994/'],
            ],
            'bytedance' => [
                ['name' => 'Zhang Yiming', 'role' => 'Founder', 'is_current' => false],
                ['name' => 'Liang Rubo', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/rubo-liang-8780594/'],
                ['name' => 'Shou Zi Chew', 'role' => 'CEO of TikTok', 'linkedin_url' => 'https://www.linkedin.com/in/sikiuchew/'],
            ],
            'revolut' => [
                ['name' => 'Nikolay Storonsky', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/nickstoronsky/'],
                ['name' => 'Vlad Yatsenko', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/vladyatsenko/'],
            ],
            'klarna' => [
                ['name' => 'Sebastian Siemiatkowski', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/sebastiansiemiatkowski/'],
                ['name' => 'Niklas Adalberth', 'role' => 'Co-founder', 'is_current' => false],
                ['name' => 'Victor Jacobsson', 'role' => 'Co-founder', 'is_current' => false],
            ],
            'airbnb' => [
                ['name' => 'Brian Chesky', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/brianchesky/'],
                ['name' => 'Joe Gebbia', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/jgebbia/'],
                ['name' => 'Nathan Blecharczyk', 'role' => 'Co-founder & Chief Strategy Officer', 'linkedin_url' => 'https://www.linkedin.com/in/nathanblecharczyk/'],
            ],
            'dropbox' => [
                ['name' => 'Drew Houston', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/drewhouston/'],
                ['name' => 'Arash Ferdowsi', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/araborat/'],
            ],
            'doordash' => [
                ['name' => 'Tony Xu', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/xutony/'],
                ['name' => 'Stanley Tang', 'role' => 'Co-founder & Chief Product Officer', 'linkedin_url' => 'https://www.linkedin.com/in/stanleytang/'],
                ['name' => 'Andy Fang', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/andy-fang-9783b228/'],
            ],
            'coinbase' => [
                ['name' => 'Brian Armstrong', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/barmstrong/'],
                ['name' => 'Fred Ehrsam', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/fredehrsam/'],
            ],
            'instacart' => [
                ['name' => 'Fidji Simo', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/fidjisimo/'],
                ['name' => 'Apoorva Mehta', 'role' => 'Founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/apoorvamehta/'],
            ],
            'reddit' => [
                ['name' => 'Steve Huffman', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/spaborat/'],
                ['name' => 'Alexis Ohanian', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/alexisohanian/'],
            ],
            'gitlab' => [
                ['name' => 'Sid Sijbrandij', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/sijbrandij/'],
                ['name' => 'Dmitriy Zaporozhets', 'role' => 'Co-founder & Engineering Fellow', 'linkedin_url' => 'https://www.linkedin.com/in/dzaporozhets/'],
            ],
            'zapier' => [
                ['name' => 'Wade Foster', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/wadefoster/'],
                ['name' => 'Bryan Helmig', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/bryanhelmig/'],
                ['name' => 'Mike Knoop', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/mikeknoop/'],
            ],
            'gusto' => [
                ['name' => 'Josh Reeves', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/joshuareeves/'],
                ['name' => 'Edward Kim', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/edwardkim1/'],
                ['name' => 'Tomer London', 'role' => 'Co-founder & Chief Product Officer', 'linkedin_url' => 'https://www.linkedin.com/in/tomerlondon/'],
            ],
            'deel' => [
                ['name' => 'Alex Bouaziz', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/alexbouaziz/'],
                ['name' => 'Shuo Wang', 'role' => 'Co-founder & CRO', 'linkedin_url' => 'https://www.linkedin.com/in/shuo-wang-deel/'],
            ],
            'plaid' => [
                ['name' => 'Zach Perret', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/zachperret/'],
                ['name' => 'William Hockey', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/williamhockey/'],
            ],
            'ramp' => [
                ['name' => 'Eric Glyman', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/eglyman/'],
                ['name' => 'Karim Atiyeh', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/karimatiyeh/'],
            ],
            'brex' => [
                ['name' => 'Henrique Dubugras', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/henriquedubugras/'],
                ['name' => 'Pedro Franceschi', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/pedrofranceschi/'],
            ],
            'scale-ai' => [
                ['name' => 'Alexandr Wang', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/alexandrwang/'],
                ['name' => 'Lucy Guo', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/lucy-guo-7aa149a9/'],
            ],
            'datadog' => [
                ['name' => 'Olivier Pomel', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/olivierpomel/'],
                ['name' => 'Alexis Lê-Quôc', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/alq/'],
            ],
            'vercel' => [
                ['name' => 'Guillermo Rauch', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/guillermo-rauch-b834b917b/'],
            ],
            'retool' => [
                ['name' => 'David Hsu', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/davidyhsu/'],
            ],
            'linear' => [
                ['name' => 'Karri Saarinen', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/karrisaarinen/'],
                ['name' => 'Tuomas Artman', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/tuomasartman/'],
                ['name' => 'Jori Lallo', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/jorilallo/'],
            ],
            'airtable' => [
                ['name' => 'Howie Liu', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/howieliu/'],
                ['name' => 'Andrew Ofstad', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/andrewofstad/'],
                ['name' => 'Emmett Nicholas', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/emmettnicholas/'],
            ],
            'asana' => [
                ['name' => 'Dustin Moskovitz', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/dmoskov/'],
                ['name' => 'Justin Rosenstein', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/justinrosenstein/'],
            ],
            'mondaycom' => [
                ['name' => 'Roy Mann', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/roymann/'],
                ['name' => 'Eran Zinman', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/eranzinman/'],
            ],
            'clickup' => [
                ['name' => 'Zeb Evans', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/zebevans/'],
            ],
            'miro' => [
                ['name' => 'Andrey Khusid', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/andreykhusid/'],
                ['name' => 'Oleg Shardin', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/shardin/'],
            ],
            'webflow' => [
                ['name' => 'Vlad Magdalin', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/vladmagdalin/'],
                ['name' => 'Sergie Magdalin', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/sergiemagdalin/'],
                ['name' => 'Bryant Chou', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/bryantchou/'],
            ],
            'supabase' => [
                ['name' => 'Paul Copplestone', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/paulcopplestone/'],
                ['name' => 'Ant Wilson', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/antwilson/'],
            ],
            'perplexity' => [
                ['name' => 'Aravind Srinivas', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/aravind-srinivas-16051987/'],
                ['name' => 'Denis Yarats', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/denisyarats/'],
                ['name' => 'Johnny Ho', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/johnny-ho-936b571a7/'],
            ],
            'characterai' => [
                ['name' => 'Noam Shazeer', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/noam-shazeer-3a87496/'],
                ['name' => 'Daniel De Freitas', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/daniel-de-freitas-b3464020/'],
            ],
            'cohere' => [
                ['name' => 'Aidan Gomez', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/aidangomez/'],
                ['name' => 'Ivan Zhang', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/ivan-zhang-aa5b4155/'],
                ['name' => 'Nick Chicken', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/nickfrosst/'],
            ],
            'hugging-face' => [
                ['name' => 'Clément Delangue', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/clementdelangue/'],
                ['name' => 'Julien Chaumond', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/julien-c-b8ab531a/'],
                ['name' => 'Thomas Wolf', 'role' => 'Co-founder & CSO', 'linkedin_url' => 'https://www.linkedin.com/in/thomas-wolf-a056857/'],
            ],
            'mistral-ai' => [
                ['name' => 'Arthur Mensch', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/arthur-mensch-a5225b86/'],
                ['name' => 'Guillaume Lample', 'role' => 'Co-founder & Chief Scientist', 'linkedin_url' => 'https://www.linkedin.com/in/guillaume-lample-3a7a5ba5/'],
                ['name' => 'Timothée Lacroix', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/timotheelacroix/'],
            ],
            'replit' => [
                ['name' => 'Amjad Masad', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/amjadmasad/'],
                ['name' => 'Haya Odeh', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/hayaodeh/'],
            ],
            'cursor' => [
                ['name' => 'Michael Truell', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/michael-truell-bb1691aa/'],
                ['name' => 'Sualeh Asif', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/sualeh-asif/'],
                ['name' => 'Arvid Lunnemark', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/arvidlunnemark/'],
                ['name' => 'Aman Sanger', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/aman-sanger/'],
            ],
            'checkoutcom' => [
                ['name' => 'Guillaume Pousaz', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/guillaumepousaz/'],
            ],
            'twitch' => [
                ['name' => 'Emmett Shear', 'role' => 'Co-founder & Former CEO', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/emmettshear/'],
                ['name' => 'Justin Kan', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/justinkan/'],
                ['name' => 'Dan Clancy', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/dan-clancy-twitch/'],
            ],
            'cruise' => [
                ['name' => 'Kyle Vogt', 'role' => 'Co-founder & Former CEO', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/kylevogt/'],
                ['name' => 'Dan Ammann', 'role' => 'Former CEO', 'is_current' => false],
            ],
            'pagerduty' => [
                ['name' => 'Jennifer Tejada', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/jtejada/'],
                ['name' => 'Alex Solomon', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/alexsolo/'],
            ],
            'whatnot' => [
                ['name' => 'Grant LaFontaine', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/grantlafontaine/'],
                ['name' => 'Logan Head', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/loganhead/'],
            ],
            'benchling' => [
                ['name' => 'Sajith Wickramasekara', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/sajithw/'],
                ['name' => 'Ashu Singhal', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/ashu-singhal/'],
            ],
            'ginkgo-bioworks' => [
                ['name' => 'Jason Kelly', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/jasonkellybio/'],
                ['name' => 'Reshma Shetty', 'role' => 'Co-founder & COO', 'linkedin_url' => 'https://www.linkedin.com/in/reshmashetty/'],
            ],
            'amplitude' => [
                ['name' => 'Spenser Skates', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/spenserskates/'],
                ['name' => 'Curtis Liu', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/curtisliu1/'],
            ],
            'xai' => [
                ['name' => 'Elon Musk', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/elonmusk/'],
            ],
            'groq' => [
                ['name' => 'Jonathan Ross', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/rossjonathan/'],
            ],
            'cerebras-systems' => [
                ['name' => 'Andrew Feldman', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/afeldman/'],
                ['name' => 'Gary Lauterbach', 'role' => 'Co-founder & SVP Engineering', 'linkedin_url' => 'https://www.linkedin.com/in/garylauterbach/'],
            ],
            'stability-ai' => [
                ['name' => 'Emad Mostaque', 'role' => 'Founder & Former CEO', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/emostaque/'],
                ['name' => 'Prem Akkaraju', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/premakkaraju/'],
            ],
            'midjourney' => [
                ['name' => 'David Holz', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/david-holz-106b227/'],
            ],
            'runway' => [
                ['name' => 'Cristóbal Valenzuela', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/cvalenzuelab/'],
                ['name' => 'Anastasis Germanidis', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/anastasis/'],
            ],
            'synthesia' => [
                ['name' => 'Victor Riparbelli', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/victorriparbelli/'],
                ['name' => 'Steffen Tjerrild', 'role' => 'Co-founder & COO', 'linkedin_url' => 'https://www.linkedin.com/in/steffentjerrild/'],
            ],
            'harvey' => [
                ['name' => 'Winston Weinberg', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/winstonweinberg/'],
                ['name' => 'Gabriel Pereyra', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/gabriel-pereyra-44a26962/'],
            ],
            'anyscale' => [
                ['name' => 'Robert Nishihara', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/robert-nishihara-8a58a630/'],
                ['name' => 'Philipp Moritz', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/philipp-moritz-b10a9a64/'],
                ['name' => 'Ion Stoica', 'role' => 'Co-founder & Executive Chairman', 'linkedin_url' => 'https://www.linkedin.com/in/ion-stoica-7b56a/'],
            ],
            'inflection-ai' => [
                ['name' => 'Mustafa Suleyman', 'role' => 'Co-founder & Former CEO', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/mustafa-suleyman/'],
                ['name' => 'Karén Simonyan', 'role' => 'Co-founder & Chief Scientist', 'linkedin_url' => 'https://www.linkedin.com/in/simonyan/'],
                ['name' => 'Reid Hoffman', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/reidhoffman/'],
            ],
            'together-ai' => [
                ['name' => 'Vipul Ved Prakash', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/vipulprakash/'],
                ['name' => 'Ce Zhang', 'role' => 'Co-founder & Chief Scientist', 'linkedin_url' => 'https://www.linkedin.com/in/ce-zhang-59552311/'],
            ],
            'adept' => [
                ['name' => 'David Luan', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/david-luan-00917316/'],
            ],
            'jasper' => [
                ['name' => 'Dave Rogenmoser', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/dave-rogenmoser/'],
                ['name' => 'Chris Hull', 'role' => 'Co-founder & CPO', 'linkedin_url' => 'https://www.linkedin.com/in/chrishull3/'],
            ],
            'copyai' => [
                ['name' => 'Chris Lu', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/chrisdlu/'],
                ['name' => 'Paul Yacoubian', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/paulyacoubian/'],
            ],
            'glean' => [
                ['name' => 'Arvind Jain', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/arvindjain/'],
                ['name' => 'Piyush Prahladka', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/piyushprahladka/'],
            ],
            'writer' => [
                ['name' => 'May Habib', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/mayhabib/'],
                ['name' => 'Waseem Alshikh', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/alshikh/'],
            ],
            'weights-biases' => [
                ['name' => 'Lukas Biewald', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/lucasbiewald/'],
                ['name' => 'Chris Van Pelt', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/chrisvanpelt/'],
            ],
            'mosaic-ml' => [
                ['name' => 'Naveen Rao', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/naveen-rao-ml/'],
                ['name' => 'Hanlin Tang', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/hanlintang/'],
            ],
            'chime' => [
                ['name' => 'Chris Britt', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/chrisbritt/'],
                ['name' => 'Ryan King', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/ryancking/'],
            ],
            'mercury' => [
                ['name' => 'Immad Akhund', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/immad/'],
            ],
            'navan' => [
                ['name' => 'Ariel Cohen', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/ariel-cohen-78a8561/'],
                ['name' => 'Ilan Twig', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/ilantwig/'],
            ],
            'marqeta' => [
                ['name' => 'Jason Gardner', 'role' => 'Founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/jdgardner/'],
                ['name' => 'Simon Khalaf', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/simonkhalaf/'],
            ],
            'affirm' => [
                ['name' => 'Max Levchin', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/maxlevchin/'],
            ],
            'robinhood' => [
                ['name' => 'Vlad Tenev', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/vladtenev/'],
                ['name' => 'Baiju Bhatt', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/baijubhatt/'],
            ],
            'wise' => [
                ['name' => 'Kristo Käärmann', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/kristok/'],
                ['name' => 'Taavet Hinrikus', 'role' => 'Co-founder & Chairman', 'linkedin_url' => 'https://www.linkedin.com/in/taavet/'],
            ],
            'postman' => [
                ['name' => 'Abhinav Asthana', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/abhinavasthana/'],
                ['name' => 'Ankit Sobti', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/ankitsobti/'],
            ],
            'snyk' => [
                ['name' => 'Guy Podjarny', 'role' => 'Founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/guypo/'],
                ['name' => 'Peter McKay', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/peterm51/'],
            ],
            'hashicorp' => [
                ['name' => 'Mitchell Hashimoto', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/mitchellh/'],
                ['name' => 'Armon Dadgar', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/armon-dadgar-5927a21/'],
                ['name' => 'David McJannet', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/davidmcjannet/'],
            ],
            'grafana-labs' => [
                ['name' => 'Raj Dutt', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/rajdutt/'],
                ['name' => 'Torkel Ödegaard', 'role' => 'Co-founder & Chief Product Officer', 'linkedin_url' => 'https://www.linkedin.com/in/torkelodegaard/'],
                ['name' => 'Anthony Woods', 'role' => 'Co-founder & VP Cloud', 'linkedin_url' => 'https://www.linkedin.com/in/anthonyjwoods/'],
            ],
            'launchdarkly' => [
                ['name' => 'Edith Harbaugh', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/edithharbaugh/'],
                ['name' => 'John Kodumal', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/jkodumal/'],
            ],
            'devoted-health' => [
                ['name' => 'Ed Park', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/ed-park-4626021/'],
                ['name' => 'Todd Park', 'role' => 'Co-founder & Executive Chairman', 'linkedin_url' => 'https://www.linkedin.com/in/todd-park-80444411/'],
            ],
            'ro' => [
                ['name' => 'Zachariah Reitano', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/zachreitano/'],
                ['name' => 'Rob Schutz', 'role' => 'Co-founder & COO', 'linkedin_url' => 'https://www.linkedin.com/in/robschutz/'],
            ],
            'hims-hers' => [
                ['name' => 'Andrew Dudum', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/andrewdudum/'],
            ],
            'tempus' => [
                ['name' => 'Eric Lefkofsky', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/elefkofsky/'],
            ],
            'noom' => [
                ['name' => 'Saeju Jeong', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/saejujeong/'],
                ['name' => 'Artem Petakov', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/artempetakov/'],
            ],
            'calm' => [
                ['name' => 'Alex Tew', 'role' => 'Co-founder & Co-CEO', 'linkedin_url' => 'https://www.linkedin.com/in/alextew/'],
                ['name' => 'Michael Acton Smith', 'role' => 'Co-founder & Co-CEO', 'linkedin_url' => 'https://www.linkedin.com/in/michaelactonsmith/'],
            ],
            'headspace' => [
                ['name' => 'Andy Puddicombe', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/andy-puddicombe-3b3bbb14/'],
                ['name' => 'Richard Pierson', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/richardpierson/'],
                ['name' => 'Russell Glass', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/russelljglass/'],
            ],
            'faire' => [
                ['name' => 'Max Rhodes', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/maxwell-rhodes/'],
                ['name' => 'Daniele Perito', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/daniele-perito-53b33423/'],
            ],
            'flexport' => [
                ['name' => 'Ryan Petersen', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/ryanpetersen/'],
            ],
            'fanatics' => [
                ['name' => 'Michael Rubin', 'role' => 'Founder & Chairman', 'linkedin_url' => 'https://www.linkedin.com/in/michael-rubin-64b17826/'],
            ],
            'stockx' => [
                ['name' => 'Josh Luber', 'role' => 'Co-founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/joshluber/'],
                ['name' => 'Greg Schwartz', 'role' => 'Co-founder', 'is_current' => false],
                ['name' => 'Scott Cutler', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/cutlers/'],
            ],
            'goat' => [
                ['name' => 'Eddy Lu', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/eddylu/'],
                ['name' => 'Daishin Sugano', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/daishinsugano/'],
            ],
            'poshmark' => [
                ['name' => 'Manish Chandra', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/mandchan/'],
                ['name' => 'Tracy Sun', 'role' => 'Co-founder & SVP Seller Experience', 'linkedin_url' => 'https://www.linkedin.com/in/tracytsun/'],
            ],
            'shipt' => [
                ['name' => 'Bill Smith', 'role' => 'Founder', 'is_current' => false, 'linkedin_url' => 'https://www.linkedin.com/in/billsmithbham/'],
                ['name' => 'Kamau Witherspoon', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/kamauwitherspoon/'],
            ],
            'waymo' => [
                ['name' => 'Dmitri Dolgov', 'role' => 'Co-CEO', 'linkedin_url' => 'https://www.linkedin.com/in/dmitri-dolgov-9929b3a/'],
                ['name' => 'Tekedra Mawakana', 'role' => 'Co-CEO', 'linkedin_url' => 'https://www.linkedin.com/in/tekedramawakana/'],
            ],
            'rivian' => [
                ['name' => 'RJ Scaringe', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/rjscaringe/'],
            ],
            'aurora' => [
                ['name' => 'Chris Urmson', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/chrisurmson/'],
                ['name' => 'Sterling Anderson', 'role' => 'Co-founder & CPO', 'linkedin_url' => 'https://www.linkedin.com/in/sterlinganderson/'],
                ['name' => 'Drew Bagnell', 'role' => 'Co-founder & Chief Technologist', 'linkedin_url' => 'https://www.linkedin.com/in/drew-bagnell-04613/'],
            ],
            'nuro' => [
                ['name' => 'Dave Ferguson', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/dave-ferguson-63bbb42/'],
                ['name' => 'Jiajun Zhu', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/jiajun-zhu-85b7841/'],
            ],
            'bird' => [
                ['name' => 'Travis VanderZanden', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/travisvanderzanden/'],
            ],
            'servicetitan' => [
                ['name' => 'Ara Mahdessian', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/aramahdessian/'],
                ['name' => 'Vahe Kuzoyan', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/vahekuzoyan/'],
            ],
            'toast' => [
                ['name' => 'Chris Comparato', 'role' => 'CEO', 'linkedin_url' => 'https://www.linkedin.com/in/chriscomparato/'],
                ['name' => 'Steve Fredette', 'role' => 'Co-founder & President', 'linkedin_url' => 'https://www.linkedin.com/in/stevefredette/'],
                ['name' => 'Aman Narang', 'role' => 'Co-founder & COO', 'linkedin_url' => 'https://www.linkedin.com/in/amannarang/'],
            ],
            'celonis' => [
                ['name' => 'Alexander Rinke', 'role' => 'Co-founder & Co-CEO', 'linkedin_url' => 'https://www.linkedin.com/in/alexanderrinke/'],
                ['name' => 'Bastian Nominacher', 'role' => 'Co-founder & Co-CEO', 'linkedin_url' => 'https://www.linkedin.com/in/bastian-nominacher/'],
                ['name' => 'Martin Klenk', 'role' => 'Co-founder & Chief Technology Advisor', 'linkedin_url' => 'https://www.linkedin.com/in/martin-klenk-b6aa9440/'],
            ],
            'uipath' => [
                ['name' => 'Daniel Dines', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/danieldines/'],
                ['name' => 'Marius Tirca', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/mariustirca/'],
            ],
            'loom' => [
                ['name' => 'Joe Thomas', 'role' => 'Co-founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/joe-t-2679846/'],
                ['name' => 'Vinay Hiremath', 'role' => 'Co-founder & CTO', 'linkedin_url' => 'https://www.linkedin.com/in/vinayhiremath/'],
                ['name' => 'Shahed Khan', 'role' => 'Co-founder', 'linkedin_url' => 'https://www.linkedin.com/in/shahedkhan/'],
            ],
            'calendly' => [
                ['name' => 'Tope Awotona', 'role' => 'Founder & CEO', 'linkedin_url' => 'https://www.linkedin.com/in/topeawotona/'],
            ],
        ];

        $count = 0;
        foreach ($companyPeople as $slug => $people) {
            $company = Company::where('slug', $slug)->first();
            if (! $company) {
                continue;
            }

            // Skip if company already has people
            if ($company->people()->count() > 0) {
                continue;
            }

            foreach ($people as $personData) {
                $personSlug = Str::slug($personData['name']);
                $isCurrent = $personData['is_current'] ?? true;

                $person = Person::firstOrCreate(
                    ['slug' => $personSlug],
                    [
                        'name' => $personData['name'],
                        'slug' => $personSlug,
                        'linkedin_url' => $personData['linkedin_url'] ?? null,
                    ]
                );

                $company->people()->attach($person->id, [
                    'role' => $personData['role'],
                    'is_current' => $isCurrent,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $count++;
        }

        $this->command->info("Added people for {$count} companies.");
    }
}
