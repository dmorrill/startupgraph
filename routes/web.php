<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CompanyController::class, 'index'])->name('home');
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');
