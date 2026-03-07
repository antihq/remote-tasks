<?php

use App\Models\Task;
use Illuminate\Support\Facades\Process;

it('runs task successfully', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(output: 'mkdir output', exitCode: 0))
            ->push(Process::result(output: 'scp output', exitCode: 0))
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
    expect(trim($task->output))->toBe('Success');
    expect($task->finished_at)->not->toBeNull();
});

it('handles task failure', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(output: 'mkdir output', exitCode: 0))
            ->push(Process::result(output: 'scp output', exitCode: 0))
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
    expect(trim($task->output))->toBe('Error');
});

it('handles task with exit code 124', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(output: 'mkdir output', exitCode: 0))
            ->push(Process::result(output: 'scp output', exitCode: 0))
            ->push(Process::result(output: 'Timeout output', exitCode: 124)),
    ]);

    $task = Task::factory()->create([
        'server_ip' => '192.168.1.1',
        'ssh_user' => 'root',
        'script' => 'echo "Test"',
    ]);

    $task->run();
    $task->refresh();

    // With Process::fake(), exit code 124 doesn't trigger timeout exception
    // Real timeout would set timedOut=true in ShellResponse
    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(124);
    expect($task->finished_at)->not->toBeNull();
});
