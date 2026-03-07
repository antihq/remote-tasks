<?php

use Livewire\Component;

new class extends Component
{
    public string $server_ip = '';

    public string $script = '';

    public function getPublicKeyProperty(): string
    {
        return config('remote-tasks.ssh_public_key') ?? 'Public key not configured';
    }

    public function runTask(): void
    {
        $privateKey = config('remote-tasks.ssh_private_key');
        $publicKey = config('remote-tasks.ssh_public_key');

        // SSH connection logic will go here
    }
};
?>

<div class="min-h-screen flex flex-col items-center justify-center p-8">
    <div class="w-full max-w-lg mx-auto">
        <form class="space-y-6">
            <flux:heading size="lg">Remote Task Runner</flux:heading>

            <flux:input
                wire:model="server_ip"
                label="Server IP Address"
                placeholder="192.168.1.1"
            />

            <flux:textarea
                wire:model="script"
                label="Script"
                rows="10"
                placeholder="echo 'Hello from remote server'"
            />

            <flux:input
                readonly
                copyable
                :value="$this->publicKey"
                variant="filled"
                label="Public Key (authorize this on remote server)"
                description:trailing="Add this public key to ~/.ssh/authorized_keys on the remote server"
            />

            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="runTask">
                    Run Task
                </flux:button>
            </div>
        </form>
    </div>
</div>
