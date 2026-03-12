<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\OpenSourceProject;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $companies = Company::select('slug', 'updated_at')->orderBy('name')->get();
        $ossProjects = OpenSourceProject::select('id', 'updated_at')->orderBy('name')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Static pages
        foreach (['/', '/companies', '/open-source', '/submit'] as $path) {
            $xml .= '<url><loc>'.url($path).'</loc><changefreq>daily</changefreq></url>';
        }

        // Company pages
        foreach ($companies as $company) {
            $xml .= '<url>';
            $xml .= '<loc>'.route('companies.show', $company->slug).'</loc>';
            $xml .= '<lastmod>'.$company->updated_at->toW3cString().'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
