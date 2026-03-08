<?php

use App\Models\Task;
use Illuminate\Support\Facades\Process;
use Livewire\Livewire;

it('renders successfully', function () {
    $this->get('/')
        ->assertStatus(200)
        ->assertSee('Remote Task Runner')
        ->assertSee('Server IP Address')
        ->assertSee('SSH User');
});

it('validates required fields', function () {
    Livewire::test('pages::welcome')
        ->set('server_ip', '')
        ->set('ssh_user', '')
        ->set('script', '')
        ->call('runTask')
        ->assertHasErrors(['server_ip', 'ssh_user', 'script']);
});

it('validates ip address format', function () {
    Livewire::test('pages::welcome')
        ->set('server_ip', 'not-an-ip')
        ->call('runTask')
        ->assertHasErrors(['server_ip']);
});

it('executes task successfully', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: 'Success output', exitCode: 0)),
    ]);

    Livewire::test('pages::welcome')
        ->set('server_ip', '192.168.1.1')
        ->set('ssh_user', 'root')
        ->set('script', 'echo "Test"')
        ->set('run_in_background', false)
        ->call('runTask')
        ->assertRedirect();

    expect(Task::count())->toBe(1);
    expect(Task::first()->status)->toBe('finished');
});

it('shows timeout status', function () {
    Process::fake([
        '*' => Process::sequence()
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: '', exitCode: 0))
            ->push(Process::result(output: 'Timeout output', exitCode: 124)),
    ]);

    Livewire::test('pages::welcome')
        ->set('server_ip', '192.168.1.1')
        ->set('ssh_user', 'root')
        ->set('script', 'echo "Test"')
        ->set('run_in_background', false)
        ->call('runTask')
        ->assertRedirect();

    expect(Task::first()->status)->toBe('finished');
});

it('displays public key', function () {
    $this->get('/')
        ->assertSee(config('remote-tasks.ssh_public_key'));
});
