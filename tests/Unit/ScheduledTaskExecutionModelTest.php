<?php

use App\Models\Company;
use App\Models\ScheduledTaskExecution;

test('scheduled task execution has fillable attributes', function () {
    $execution = new ScheduledTaskExecution;
    expect($execution->getFillable())->toBe([
        'task_type',
        'company_id',
        'status',
        'error_message',
        'metadata',
        'started_at',
        'completed_at',
    ]);
});

test('scheduled task execution belongs to a company', function () {
    $execution = ScheduledTaskExecution::factory()->create();
    expect($execution->company())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    expect($execution->company)->toBeInstanceOf(Company::class);
});

test('scheduled task execution casts metadata to array', function () {
    $execution = ScheduledTaskExecution::factory()->create([
        'metadata' => ['key' => 'value', 'nested' => ['a' => 1]],
    ]);
    $execution->refresh();
    expect($execution->metadata)->toBeArray();
    expect($execution->metadata['key'])->toBe('value');
    expect($execution->metadata['nested']['a'])->toBe(1);
});

test('scheduled task execution casts datetime fields', function () {
    $execution = ScheduledTaskExecution::factory()->create();
    expect($execution->started_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($execution->completed_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
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

    $failed = ScheduledTaskExecution::failed()->get();
    expect($failed)->toHaveCount(1);
    expect($failed->first()->status)->toBe('failed');
});

test('scope successful filters by status', function () {
    ScheduledTaskExecution::factory()->create(['status' => 'success']);
    ScheduledTaskExecution::factory()->failed()->create();

    $successful = ScheduledTaskExecution::successful()->get();
    expect($successful)->toHaveCount(1);
    expect($successful->first()->status)->toBe('success');
});

test('scope forTaskType filters by task type', function () {
    ScheduledTaskExecution::factory()->create(['task_type' => 'news_fetch']);
    ScheduledTaskExecution::factory()->create(['task_type' => 'headcount_update']);

    $newsFetch = ScheduledTaskExecution::forTaskType('news_fetch')->get();
    expect($newsFetch)->toHaveCount(1);
    expect($newsFetch->first()->task_type)->toBe('news_fetch');
});
