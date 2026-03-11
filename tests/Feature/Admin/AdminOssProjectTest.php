<?php

use App\Models\Company;
use App\Models\OpenSourceProject;

beforeEach(function () {
    config(['admin.username' => 'admin', 'admin.password' => 'secret']);
});

$admin = ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'secret'];

test('admin oss projects index requires auth', function () {
    $response = $this->get('/admin/oss-projects');

    $response->assertStatus(401);
});

test('admin oss projects index loads', function () use ($admin) {
    OpenSourceProject::factory()->count(3)->create();

    $response = $this->withServerVariables($admin)->get('/admin/oss-projects');

    $response->assertStatus(200);
});

test('admin oss projects index filters by search', function () use ($admin) {
    OpenSourceProject::factory()->create(['name' => 'FindableProject']);
    OpenSourceProject::factory()->create(['name' => 'AnotherProject']);

    $response = $this->withServerVariables($admin)->get('/admin/oss-projects?search=Findable');

    $response->assertStatus(200);
    $response->assertSee('FindableProject');
    $response->assertDontSee('AnotherProject');
});

test('admin oss project show page loads', function () use ($admin) {
    $project = OpenSourceProject::factory()->create();

    $response = $this->withServerVariables($admin)->get("/admin/oss-projects/{$project->id}");

    $response->assertStatus(200);
});

test('admin can link company to oss project', function () use ($admin) {
    $project = OpenSourceProject::factory()->create();
    $company = Company::factory()->create();

    $response = $this->withServerVariables($admin)
        ->post("/admin/oss-projects/{$project->id}/link-company", [
            'company_id' => $company->id,
            'relationship_type' => 'commercial_version_of',
        ]);

    $response->assertRedirect();
    expect($project->companies()->where('company_id', $company->id)->exists())->toBeTrue();
});

test('admin can unlink company from oss project', function () use ($admin) {
    $project = OpenSourceProject::factory()->create();
    $company = Company::factory()->create();
    $project->companies()->attach($company->id, ['relationship_type' => 'alternative_to']);

    $response = $this->withServerVariables($admin)
        ->delete("/admin/oss-projects/{$project->id}/unlink-company/{$company->slug}");

    $response->assertRedirect();
    expect($project->companies()->where('company_id', $company->id)->exists())->toBeFalse();
});

test('link company validation fails without company_id', function () use ($admin) {
    $project = OpenSourceProject::factory()->create();

    $response = $this->withServerVariables($admin)
        ->post("/admin/oss-projects/{$project->id}/link-company", [
            'relationship_type' => 'alternative_to',
        ]);

    $response->assertSessionHasErrors('company_id');
});
