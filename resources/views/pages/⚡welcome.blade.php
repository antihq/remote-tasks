<?php

use Livewire\Component;

new class extends Component
{
    public string $server_ip = '';

    public string $script = '';
};
?>

<div class="min-h-screen flex flex-col items-center justify-center p-8">
    <div class="w-full max-w-lg mx-auto">
        <form class="space-y-6">
            <flux:heading size="lg">Remote Task Runner</flux:heading>

            <flux:field>
                <flux:label>Server IP Address</flux:label>
                <flux:input wire:model="server_ip" placeholder="192.168.1.1" />
                <flux:error name="server_ip" />
            </flux:field>

            <flux:field>
                <flux:label>Script</flux:label>
                <flux:textarea wire:model="script" rows="10" placeholder="echo 'Hello from remote server'" />
                <flux:error name="script" />
            </flux:field>

            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="runTask">
                    Run Task
                </flux:button>
            </div>
        </form>
    </div>
</div>
