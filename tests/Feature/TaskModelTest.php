<?php

use App\Models\Task;

it('marks as running', function () {
    $task = Task::factory()->create(['status' => 'pending']);

    $task->markAsRunning();

    expect($task->status)->toBe('running');
    expect($task->started_at)->not->toBeNull();
});

it('marks as timed out', function () {
    $task = Task::factory()->create(['status' => 'running']);

    $task->markAsTimedOut();

    expect($task->status)->toBe('timeout');
    expect($task->finished_at)->not->toBeNull();
});

it('checks if successful with zero exit code', function () {
    $task = Task::factory()->create(['exit_code' => 0]);

    expect($task->successful())->toBeTrue();
});

it('checks if not successful with non zero exit code', function () {
    $task = Task::factory()->create(['exit_code' => 1]);

    expect($task->successful())->toBeFalse();
});
