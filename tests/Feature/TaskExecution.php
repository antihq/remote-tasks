<?php

use App\Models\Task;
use Illuminate\Support\Facades\Process;

it('runs task successfully', function () {
    Process::fake([
        'ssh *' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: 'Success', exitCode: 0)),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
    ]);

    $task->run();

    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(0);
    expect($task->output)->toBe('Success');
    expect($task->finished_at)->not->toBeNull();
});

it('handles task failure', function () {
    Process::fake([
        'ssh *' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: 'Error', exitCode: 1)),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'exit 1',
    ]);

    $task->run();

    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(1);
    expect($task->output)->toBe('Error');
});

it('handles task timeout', function () {
    Process::fake([
        'ssh *' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '')->exitCode(124)),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
    ]);

    $task->run();

    expect($task->status)->toBe('timeout');
    expect($task->finished_at)->not->toBeNull();
});

it('uses correct path for root user', function () {
    $task = Task::factory()->create(['ssh_user' => 'root']);

    expect($task->path())->toBe('/root/.remote-tasks');
});

it('uses correct path for non root user', function () {
    $task = Task::factory()->create(['ssh_user' => 'ubuntu']);

    expect($task->path())->toBe('/home/ubuntu/.remote-tasks');
});
