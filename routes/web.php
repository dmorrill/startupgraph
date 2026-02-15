<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CompanyController::class, 'index'])->name('home');
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/compare', [CompareController::class, 'index'])->name('companies.compare');
Route::get('/companies/export/csv', [CompanyController::class, 'exportCsv'])->name('companies.export.csv');
Route::get('/companies/export/json', [CompanyController::class, 'exportJson'])->name('companies.export.json');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');

// Admin routes
Route::prefix('admin')->middleware('admin.auth')->name('admin.')->group(function () {
    Route::get('/companies', [AdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [AdminCompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [AdminCompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [AdminCompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [AdminCompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [AdminCompanyController::class, 'destroy'])->name('companies.destroy');
});
