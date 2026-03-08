<?php

use App\Models\Task;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Task $task;

    public function mount(): void
    {
        $this->task->load([]);
    }

    public function pollTaskStatus(): void
    {
        if ($this->task->status === 'running') {
            $this->task->refresh();
        }
    }

    public function statusColor(): string
    {
        return match ($this->task->status) {
            'pending' => 'zinc',
            'running' => 'blue',
            'finished' => 'green',
            'timeout' => 'yellow',
            'failed' => 'red',
            default => 'zinc',
        };
    }
};
?>

<div class="min-h-screen flex flex-col items-center justify-center p-8" @if($task->status === 'running') wire:poll.3s="pollTaskStatus" @endif>
    <div class="w-full max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">
                Task #{{ $task->id }}
                <flux:badge :color="$this->statusColor()" size="sm">{{ ucfirst($task->status) }}</flux:badge>
            </flux:heading>

            <flux:button href="{{ route('home') }}" wire:navigate>
                Run Another Task
            </flux:button>
        </div>

        @if($task->status === 'running')
            <flux:callout color="blue">
                <flux:callout.heading>Task Running</flux:callout.heading>
                <flux:callout.text>
                    The script is executing on the remote server. This page will automatically update when complete.
                </flux:callout.text>
            </flux:callout>
        @endif

        <x-description.list>
            <x-description.term>Server IP Address</x-description.term>
            <x-description.details>{{ $task->server_ip }}</x-description.details>

            <x-description.term>SSH User</x-description.term>
            <x-description.details>{{ $task->ssh_user }}</x-description.details>

            <x-description.term>Script</x-description.term>
            <x-description.details>
                <pre class="whitespace-pre-wrap font-mono text-sm">{{ $task->script }}</pre>
            </x-description.details>

            @if($task->output)
                <x-description.term>Output</x-description.term>
                <x-description.details>
                    <pre class="whitespace-pre-wrap font-mono text-sm">{{ $task->output }}</pre>
                </x-description.details>
            @endif

            <x-description.term>Created</x-description.term>
            <x-description.details>{{ $task->created_at->format('M j, Y g:i A') }}</x-description.details>

            @if($task->started_at)
                <x-description.term>Started</x-description.term>
                <x-description.details>{{ $task->started_at->format('M j, Y g:i A') }}</x-description.details>
            @endif

            @if($task->finished_at)
                <x-description.term>Finished</x-description.term>
                <x-description.details>{{ $task->finished_at->format('M j, Y g:i A') }}</x-description.details>
            @endif

            @if($task->finished_at && $task->exit_code !== null)
                <x-description.term>Exit Code</x-description.term>
                <x-description.details>
                    @if($task->exit_code === 0)
                        <flux:text color="green">{{ $task->exit_code }}</flux:text>
                    @else
                        <flux:text color="red">{{ $task->exit_code }}</flux:text>
                    @endif
                </x-description.details>
            @endif
        </x-description.list>
    </div>
</div>
