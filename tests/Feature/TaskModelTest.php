<?php

use App\Models\Task;

it('has fillable attributes', function () {
    $task = Task::create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
        'status' => 'pending',
    ]);

    expect($task->server_ip)->toBe('192.168.1.1');
    expect($task->ssh_user)->toBe('root');
    expect($task->script)->toBe('echo "Test"');
});

it('casts timestamps', function () {
    $task = Task::factory()->create([
        'started_at' => '2024-01-01 00:00:00',
        'finished_at' => '2024-01-01 00:01:00',
    ]);

    expect($task->started_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    expect($task->finished_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});

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

it('uses factory states', function () {
    $running = Task::factory()->running()->create();
    expect($running->status)->toBe('running');

    $finished = Task::factory()->finished()->create();
    expect($finished->status)->toBe('finished');

    $timedOut = Task::factory()->timedOut()->create();
    expect($timedOut->status)->toBe('timeout');
});
