<?php

use App\Models\ScheduledTaskExecution;
use App\Models\Company;

test('scheduled task execution belongs to a company', function () {
    $company = Company::factory()->create();
    $task = ScheduledTaskExecution::factory()->create(['company_id' => $company->id]);
    expect($task->company)->toBeInstanceOf(Company::class);
    expect($task->company->id)->toBe($company->id);
});

test('scheduled task execution casts metadata to array', function () {
    $task = ScheduledTaskExecution::factory()->create(['metadata' => ['key' => 'value']]);
    expect($task->metadata)->toBeArray();
    expect($task->metadata['key'])->toBe('value');
});

test('scheduled task execution casts dates', function () {
    $task = ScheduledTaskExecution::factory()->create();
    expect($task->started_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($task->completed_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('scheduled task execution has fillable attributes', function () {
    $task = ScheduledTaskExecution::factory()->create([
        'task_type' => 'headcount_update',
        'status' => 'success',
    ]);
    expect($task->task_type)->toBe('headcount_update');
    expect($task->status)->toBe('success');
});

test('scope recent filters by days', function () {
    ScheduledTaskExecution::factory()->create(['created_at' => now()->subDays(3)]);
    ScheduledTaskExecution::factory()->create(['created_at' => now()->subDays(10)]);

    $recent = ScheduledTaskExecution::recent(7)->get();
    expect($recent)->toHaveCount(1);
});

test('scope failed filters by status', function () {
    ScheduledTaskExecution::factory()->create(['status' => 'success']);
    ScheduledTaskExecution::factory()->failed()->create();

    expect(ScheduledTaskExecution::failed()->count())->toBe(1);
});

test('scope successful filters by status', function () {
    ScheduledTaskExecution::factory()->successful()->create();
    ScheduledTaskExecution::factory()->failed()->create();

    expect(ScheduledTaskExecution::successful()->count())->toBe(1);
});

test('scope forTaskType filters by task type', function () {
    ScheduledTaskExecution::factory()->create(['task_type' => 'headcount_update']);
    ScheduledTaskExecution::factory()->create(['task_type' => 'news_scan']);

    expect(ScheduledTaskExecution::forTaskType('headcount_update')->count())->toBe(1);
});

test('company_id is nullable', function () {
    $task = ScheduledTaskExecution::factory()->create(['company_id' => null]);
    expect($task->company_id)->toBeNull();
    expect($task->company)->toBeNull();
});
