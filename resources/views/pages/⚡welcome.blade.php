<?php

use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $server_ip = '';

    public string $ssh_user = 'root';

    public string $script = '';

    public ?Task $task = null;

    public string $output = '';

    public string $status = '';

    public bool $isRunning = false;

    #[Computed]
    public function publicKey(): string
    {
        return config('remote-tasks.ssh_public_key') ?? 'Public key not configured';
    }

    public function runTask(): void
    {
        $this->validate([
            'server_ip' => 'required|ip',
            'ssh_user' => 'required|string',
            'script' => 'required|string',
        ]);

        $this->isRunning = true;
        $this->status = 'pending';
        $this->output = '';

        $this->task = Task::create([
            'server_ip' => $this->server_ip,
            'ssh_user' => $this->ssh_user,
            'script' => $this->script,
            'status' => 'pending',
            'timeout' => 3600,
        ]);

        try {
            $this->task->run();
            $this->output = $this->task->output;
            $this->status = $this->task->status;
        } catch (\Exception $e) {
            $this->output = 'Error: '.$e->getMessage();
            $this->status = 'error';
        } finally {
            $this->isRunning = false;
        }
    }
};
?>

<div class="min-h-screen flex flex-col items-center justify-center p-8">
    <div class="w-full max-w-2xl mx-auto">
        <flux:heading size="lg" class="mb-6">Remote Task Runner</flux:heading>

        <form wire:submit="runTask" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="server_ip"
                    label="Server IP Address"
                    placeholder="192.168.1.1"
                    required
                />

                <flux:input
                    wire:model="ssh_user"
                    label="SSH User"
                    placeholder="root"
                    required
                />
            </div>

            <flux:textarea
                wire:model="script"
                label="Script"
                rows="10"
                placeholder="echo 'Hello from remote server'"
                required
            />

            <flux:input
                readonly
                copyable
                :value="$this->publicKey"
                variant="filled"
                label="Public Key (authorize this on remote server)"
                description:trailing="Add to ~/.ssh/authorized_keys"
            />

            <div class="flex justify-end">
                <flux:button
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Run Task</span>
                    <span wire:loading>Running...</span>
                </flux:button>
            </div>
        </form>

        @if($output)
            <div class="mt-8 space-y-4">
                <flux:heading size="md">
                    Output
                    @if($status === 'finished')
                        <flux:badge color="green" size="sm">Success</flux:badge>
                    @elseif($status === 'timeout')
                        <flux:badge color="yellow" size="sm">Timeout</flux:badge>
                    @endif
                </flux:heading>

                <flux:textarea
                    readonly
                    rows="15"
                    variant="filled"
                >{{ $output }}</flux:textarea>
            </div>
        @endif
    </div>
</div>
