<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\OpenSourceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\OssProjectController as AdminOssProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/', [CompanyController::class, 'index'])->name('home');
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/compare', [CompareController::class, 'index'])->name('companies.compare');
Route::get('/companies/export/csv', [CompanyController::class, 'exportCsv'])->name('companies.export.csv');
Route::get('/companies/export/json', [CompanyController::class, 'exportJson'])->name('companies.export.json');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');
Route::get('/investors', [InvestorController::class, 'index'])->name('investors.index');
Route::get('/investors/{investor}', [InvestorController::class, 'show'])->name('investors.show');
Route::get('/open-source', [OpenSourceController::class, 'index'])->name('open-source.index');

Route::get('/submit', [SubmissionController::class, 'create'])->name('submissions.create');
Route::post('/submit', [SubmissionController::class, 'store'])->name('submissions.store');

// Admin routes
Route::prefix('admin')->middleware(['throttle:10,1', 'admin.auth'])->name('admin.')->group(function () {
    Route::get('/companies', [AdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [AdminCompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [AdminCompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [AdminCompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [AdminCompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [AdminCompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('/oss-projects', [AdminOssProjectController::class, 'index'])->name('oss-projects.index');
    Route::get('/oss-projects/{ossProject}', [AdminOssProjectController::class, 'show'])->name('oss-projects.show');
    Route::post('/oss-projects/{ossProject}/link-company', [AdminOssProjectController::class, 'linkCompany'])->name('oss-projects.link-company');
    Route::delete('/oss-projects/{ossProject}/unlink-company/{company}', [AdminOssProjectController::class, 'unlinkCompany'])->name('oss-projects.unlink-company');

    Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::post('/submissions/{submission}/approve', [AdminSubmissionController::class, 'approve'])->name('submissions.approve');
    Route::post('/submissions/{submission}/reject', [AdminSubmissionController::class, 'reject'])->name('submissions.reject');
});

// Authenticated user routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/saved-searches', [\App\Http\Controllers\SavedSearchController::class, 'index'])->name('saved-searches.index');
    Route::post('/saved-searches', [\App\Http\Controllers\SavedSearchController::class, 'store'])->name('saved-searches.store');
    Route::patch('/saved-searches/{savedSearch}', [\App\Http\Controllers\SavedSearchController::class, 'update'])->name('saved-searches.update');
    Route::delete('/saved-searches/{savedSearch}', [\App\Http\Controllers\SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');
});

require __DIR__.'/auth.php';
