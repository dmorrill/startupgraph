<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\OssProjectController;
use App\Http\Controllers\Api\TrendingController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| StartupGraph Public API - No authentication required.
| Designed to be queried by AI agents like Claude Code.
|
*/

// Database stats
Route::get('/stats', [StatsController::class, 'index']);

// Search across companies and people
Route::get('/search', [SearchController::class, 'index']);

// Companies
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/companies/compare', CompareController::class);
Route::get('/companies/trending', TrendingController::class);
Route::get('/companies/export.csv', [ExportController::class, 'csv']);
Route::get('/companies/{company}', [CompanyController::class, 'show']);
Route::get('/companies/{company}/funding', [CompanyController::class, 'funding']);
Route::get('/companies/{company}/people', [CompanyController::class, 'people']);
Route::get('/companies/{company}/headcount', [CompanyController::class, 'headcount']);

// People
Route::get('/people/{person}', [PersonController::class, 'show']);

// Open Source Projects
Route::get('/oss-projects', [OssProjectController::class, 'index']);
Route::get('/oss-projects/{ossProject}', [OssProjectController::class, 'show']);

// Categories list (for filtering)
Route::get('/categories', fn () => response()->json([
    'data' => collect(\App\Models\Company::CATEGORIES)->map(fn ($label, $key) => [
        'key' => $key,
        'label' => $label,
    ])->values(),
    'meta' => [
        'source' => 'startupgraph',
        'version' => '1.0',
    ],
]));
