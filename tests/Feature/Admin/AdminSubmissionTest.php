<?php

use App\Models\Company;
use App\Models\CompanySubmission;

beforeEach(function () {
    config(['admin.username' => 'admin', 'admin.password' => 'secret']);
});

$admin = ['PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW' => 'secret'];

test('admin submissions index requires auth', function () {
    $response = $this->get('/admin/submissions');

    $response->assertStatus(401);
});

test('admin submissions index loads pending submissions', function () use ($admin) {
    CompanySubmission::factory()->count(3)->create(['status' => 'pending']);

    $response = $this->withServerVariables($admin)->get('/admin/submissions');

    $response->assertStatus(200);
});

test('admin submissions index filters by status', function () use ($admin) {
    CompanySubmission::factory()->create(['name' => 'Pending One', 'status' => 'pending']);
    CompanySubmission::factory()->create(['name' => 'Approved One', 'status' => 'approved']);

    $response = $this->withServerVariables($admin)->get('/admin/submissions?status=approved');

    $response->assertStatus(200);
    $response->assertSee('Approved One');
    $response->assertDontSee('Pending One');
});

test('admin can approve a submission', function () use ($admin) {
    $submission = CompanySubmission::factory()->create([
        'name' => 'Awesome Startup',
        'status' => 'pending',
    ]);

    $response = $this->withServerVariables($admin)
        ->post("/admin/submissions/{$submission->id}/approve");

    $response->assertRedirect(route('admin.submissions.index'));
    expect($submission->fresh()->status)->toBe('approved');
    expect(Company::where('name', 'Awesome Startup')->exists())->toBeTrue();
});

test('admin can reject a submission', function () use ($admin) {
    $submission = CompanySubmission::factory()->create(['status' => 'pending']);

    $response = $this->withServerVariables($admin)
        ->post("/admin/submissions/{$submission->id}/reject");

    $response->assertRedirect(route('admin.submissions.index'));
    expect($submission->fresh()->status)->toBe('rejected');
});

test('approving a submission creates company with unique slug', function () use ($admin) {
    $submission = CompanySubmission::factory()->create(['name' => 'Duplicate Name', 'status' => 'pending']);
    Company::factory()->create(['slug' => 'duplicate-name']);

    $this->withServerVariables($admin)
        ->post("/admin/submissions/{$submission->id}/approve");

    $companies = Company::where('name', 'Duplicate Name')->get();
    expect($companies->count())->toBe(1);
    expect($companies->first()->slug)->not->toBe('duplicate-name');
});
