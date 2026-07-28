<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\OssProjectController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\ScreenController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SignalController;
use App\Http\Controllers\Api\StatsController;
use App\Models\Company;
use Illuminate\Http\Request;
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
    'data' => collect(Company::CATEGORIES)->map(fn ($label, $key) => [
        'key' => $key,
        'label' => $label,
    ])->values(),
    'meta' => [
        'source' => 'startupgraph',
        'version' => '1.0',
    ],
]));

/*
|--------------------------------------------------------------------------
| Research Layer (authenticated)
|--------------------------------------------------------------------------
|
| The user-scoped write surface agents use: lists, screens, notes, and
| signals. Auth: Sanctum bearer token (php artisan api:token).
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (Request $request) => response()->json([
        'data' => [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'token_name' => $request->user()->currentAccessToken()?->name,
        ],
        'meta' => ['source' => 'startupgraph', 'version' => '1.0'],
    ]));

    Route::get('/lists', [ListController::class, 'index']);
    Route::post('/lists', [ListController::class, 'store']);
    Route::get('/lists/{id}', [ListController::class, 'show']);
    Route::delete('/lists/{id}', [ListController::class, 'destroy']);
    Route::post('/lists/{id}/companies', [ListController::class, 'addCompany']);
    Route::delete('/lists/{id}/companies/{slug}', [ListController::class, 'removeCompany']);

    Route::get('/screens', [ScreenController::class, 'index']);
    Route::post('/screens', [ScreenController::class, 'store']);
    Route::get('/screens/{id}', [ScreenController::class, 'show']);
    Route::post('/screens/{id}/refresh', [ScreenController::class, 'refresh']);
    Route::delete('/screens/{id}', [ScreenController::class, 'destroy']);

    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);

    Route::get('/signals', [SignalController::class, 'index']);
    Route::post('/signals', [SignalController::class, 'store']);
    Route::post('/signals/{id}/read', [SignalController::class, 'markRead']);
});
