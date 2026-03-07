<?php

use App\Models\Task;
use Illuminate\Support\Facades\Process;

it('runs task in background successfully', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(output: 'mkdir output', exitCode: 0))
            ->push(Process::result(output: 'scp output', exitCode: 0))
            ->push(Process::result(output: 'nohup output', exitCode: 0)),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
        'timeout' => 3600,
    ]);

    $task->runInBackground();

    expect($task->status)->toBe('running');
    expect($task->started_at)->not->toBeNull();
});

it('marks task as finished with exit code', function () {
    Process::fake([
        '*' => Process::result(output: 'Task output', exitCode: 0),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
        'status' => 'running',
    ]);

    $task->markAsFinished(0);

    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(0);
    expect($task->finished_at)->not->toBeNull();
});

it('marks task as finished with non-zero exit code', function () {
    Process::fake([
        '*' => Process::result(output: 'Error output', exitCode: 1),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'exit 1',
        'status' => 'running',
    ]);

    $task->markAsFinished(1);

    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(1);
    expect($task->finished_at)->not->toBeNull();
});

it('generates signed callback url', function () {
    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
    ]);

    $callbackUrl = \Illuminate\Support\Facades\URL::signedRoute(
        'api.callback',
        ['task' => $task->id],
        now()->addHours(24)
    );

    expect($callbackUrl)->toContain('/api/callback/'.$task->id);
    expect($callbackUrl)->toContain('signature=');
});

it('accepts valid callback with signed url', function () {
    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
        'status' => 'running',
    ]);

    $callbackUrl = \Illuminate\Support\Facades\URL::signedRoute(
        'api.callback',
        ['task' => $task->id],
        now()->addHours(24)
    );

    Process::fake([
        '*' => Process::result(output: 'Task output', exitCode: 0),
    ]);

    $response = $this->post($callbackUrl, ['exit_code' => 0]);

    $response->assertNoContent();
});

it('rejects callback for non-running task', function () {
    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
        'status' => 'finished',
    ]);

    $callbackUrl = \Illuminate\Support\Facades\URL::signedRoute(
        'api.callback',
        ['task' => $task->id],
        now()->addHours(24)
    );

    $response = $this->post($callbackUrl, ['exit_code' => 0]);

    $response->assertNotFound();
});

it('rejects callback with invalid signature', function () {
    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
        'status' => 'running',
    ]);

    $response = $this->post('/api/callback/'.$task->id.'?exit_code=0');

    $response->assertForbidden();
});
