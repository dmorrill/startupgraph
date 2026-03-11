<?php

use App\Models\Company;

beforeEach(function () {
    config(['admin.username' => 'admin', 'admin.password' => 'secret']);
});

$admin = ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'secret'];

test('admin company index requires auth', function () {
    $response = $this->get('/admin/companies');

    $response->assertStatus(401);
});

test('admin company index loads for authenticated admin', function () use ($admin) {
    Company::factory()->count(3)->create();

    $response = $this->withServerVariables($admin)->get('/admin/companies');

    $response->assertStatus(200);
});

test('admin company index filters by search', function () use ($admin) {
    Company::factory()->create(['name' => 'FindMe Corp']);
    Company::factory()->create(['name' => 'Other Corp']);

    $response = $this->withServerVariables($admin)->get('/admin/companies?search=FindMe');

    $response->assertStatus(200);
    $response->assertSee('FindMe Corp');
    $response->assertDontSee('Other Corp');
});

test('admin company create form loads', function () use ($admin) {
    $response = $this->withServerVariables($admin)->get('/admin/companies/create');

    $response->assertStatus(200);
});

test('admin can create a company', function () use ($admin) {
    $response = $this->withServerVariables($admin)->post('/admin/companies', [
        'name' => 'New Admin Company',
    ]);

    $response->assertRedirect();
    expect(Company::where('name', 'New Admin Company')->exists())->toBeTrue();
});

test('admin company store validates required name', function () use ($admin) {
    $response = $this->withServerVariables($admin)->post('/admin/companies', [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});

test('admin company edit form loads', function () use ($admin) {
    $company = Company::factory()->create();

    $response = $this->withServerVariables($admin)->get("/admin/companies/{$company->slug}/edit");

    $response->assertStatus(200);
});

test('admin can update a company', function () use ($admin) {
    $company = Company::factory()->create(['name' => 'Old Name']);

    $response = $this->withServerVariables($admin)->put("/admin/companies/{$company->slug}", [
        'name' => 'Updated Name',
        'slug' => $company->slug,
    ]);

    $response->assertRedirect();
    expect($company->fresh()->name)->toBe('Updated Name');
});

test('admin can delete a company', function () use ($admin) {
    $company = Company::factory()->create();

    $response = $this->withServerVariables($admin)->delete("/admin/companies/{$company->slug}");

    $response->assertRedirect(route('admin.companies.index'));
    expect(Company::find($company->id))->toBeNull();
});
