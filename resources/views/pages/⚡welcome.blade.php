<?php

use App\Models\Task;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $server_ip = '';

    public string $ssh_user = 'root';

    public string $script = '';

    public bool $run_in_background = false;

    #[Computed]
    public function publicKey(): string
    {
        return config('remote-tasks.ssh_public_key') ?? 'Public key not configured';
    }

    public function runTask(): void
    {
        if ($this->isRateLimited()) {
            return;
        }

        $this->validate([
            'server_ip' => 'required|ip',
            'ssh_user' => 'required|string',
            'script' => 'required|string',
        ]);

        $task = Task::create([
            'server_ip' => $this->server_ip,
            'ssh_user' => $this->ssh_user,
            'script' => $this->script,
            'status' => 'pending',
            'timeout' => 3600,
        ]);

        if ($this->run_in_background) {
            $task->runInBackground();
        } else {
            $task->run();
        }

        $this->redirect(URL::signedRoute('tasks.show', ['task' => $task->id]));
    }

    protected function isRateLimited(): bool
    {
        $key = 'run-task:'.$this->getIp();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('rate_limit', "Too many requests. Please try again in {$seconds} seconds.");

            return true;
        }

        RateLimiter::hit($key, 60);

        return false;
    }

    protected function getIp(): string
    {
        return request()->ip() ?? 'unknown';
    }
};
?>

<div class="min-h-screen flex flex-col items-center justify-center p-8">
    <div class="w-full max-w-2xl mx-auto space-y-6">
        <flux:heading size="lg">Remote Task Runner</flux:heading>

        <form wire:submit="runTask" class="space-y-6">
            @error('rate_limit')
                <flux:callout color="red">
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

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

            <flux:switch
                wire:model="run_in_background"
                label="Run in Background"
                description="Execute script asynchronously"
            />

            <div class="flex items-center justify-end">
                <flux:button
                    type="submit"
                    variant="primary"
                >
                    Run Task
                </flux:button>
            </div>
        </form>
    </div>
</div>
