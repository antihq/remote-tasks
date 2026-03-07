<?php

use App\Models\Task;
use Illuminate\Support\Facades\Process;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('welcome')
        ->assertStatus(200)
        ->assertSee('Remote Task Runner')
        ->assertSee('Server IP Address')
        ->assertSee('SSH User');
});

it('validates required fields', function () {
    Livewire::test('welcome')
        ->set('server_ip', '')
        ->set('ssh_user', '')
        ->set('script', '')
        ->call('runTask')
        ->assertHasErrors(['server_ip', 'ssh_user', 'script']);
});

it('validates ip address format', function () {
    Livewire::test('welcome')
        ->set('server_ip', 'not-an-ip')
        ->call('runTask')
        ->assertHasErrors(['server_ip']);
});

it('executes task successfully', function () {
    Process::fake([
        'ssh *' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: 'Success output', exitCode: 0)),
    ]);

    Livewire::test('welcome')
        ->set('server_ip', '192.168.1.1')
        ->set('ssh_user', 'root')
        ->set('script', 'echo "Test"')
        ->call('runTask')
        ->assertSet('status', 'finished')
        ->assertSet('output', 'Success output')
        ->assertSet('isRunning', false);

    expect(Task::count())->toBe(1);
});

it('shows timeout status', function () {
    Process::fake([
        'ssh *' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '')->exitCode(124)),
    ]);

    Livewire::test('welcome')
        ->set('server_ip', '192.168.1.1')
        ->set('ssh_user', 'root')
        ->set('script', 'echo "Test"')
        ->call('runTask')
        ->assertSet('status', 'timeout');
});

it('displays public key', function () {
    Livewire::test('welcome')
        ->assertSee(config('remote-tasks.ssh_public_key'));
});
