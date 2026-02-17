<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\OpenSourceProject;
use App\Models\Person;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $companies = Company::select('slug', 'updated_at')->orderBy('name')->get();
        $ossProjects = OpenSourceProject::select('id', 'name', 'updated_at')->orderBy('name')->get();
        $people = Person::select('slug', 'updated_at')->orderBy('name')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages
        $staticPages = [
            '/' => ['changefreq' => 'daily', 'priority' => '1.0'],
            '/companies' => ['changefreq' => 'daily', 'priority' => '0.9'],
            '/open-source' => ['changefreq' => 'weekly', 'priority' => '0.7'],
            '/submit' => ['changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        foreach ($staticPages as $path => $meta) {
            $xml .= $this->urlEntry(url($path), null, $meta['changefreq'], $meta['priority']);
        }

        // Company pages
        foreach ($companies as $company) {
            $xml .= $this->urlEntry(
                route('companies.show', $company->slug),
                $company->updated_at->toW3cString(),
                'weekly',
                '0.8'
            );
        }

        // Person pages
        foreach ($people as $person) {
            $xml .= $this->urlEntry(
                url("/people/{$person->slug}"),
                $person->updated_at->toW3cString(),
                'monthly',
                '0.6'
            );
        }

        // Open source project pages
        foreach ($ossProjects as $project) {
            $xml .= $this->urlEntry(
                url("/open-source/{$project->id}"),
                $project->updated_at->toW3cString(),
                'weekly',
                '0.6'
            );
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate a sitemap index for large sites.
     */
    public function sitemapIndex(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $sitemaps = ['sitemap.xml'];
        foreach ($sitemaps as $sitemap) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . url($sitemap) . '</loc>';
            $xml .= '<lastmod>' . now()->toW3cString() . '</lastmod>';
            $xml .= '</sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function urlEntry(string $loc, ?string $lastmod, string $changefreq, string $priority): string
    {
        $xml = '<url>';
        $xml .= '<loc>' . htmlspecialchars($loc) . '</loc>';
        if ($lastmod) {
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
        }
        $xml .= '<changefreq>' . $changefreq . '</changefreq>';
        $xml .= '<priority>' . $priority . '</priority>';
        $xml .= '</url>' . "\n";
        return $xml;
    }
}
