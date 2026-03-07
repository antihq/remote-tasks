<?php

namespace App\Models;

use App\Traits\InteractsWithSsh;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, InteractsWithSsh;

    protected $fillable = [
        'server_ip',
        'ssh_user',
        'script',
        'status',
        'exit_code',
        'output',
        'timeout',
        'options',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'options' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function markAsRunning(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsTimedOut(): self
    {
        return tap($this)->update([
            'status' => 'timeout',
            'finished_at' => now(),
        ]);
    }

    public function markAsFinished(int $exitCode = 0): self
    {
        return tap($this)->update([
            'status' => 'finished',
            'exit_code' => $exitCode,
            'output' => $this->retrieveOutput(),
            'finished_at' => now(),
        ]);
    }

    public function retrieveOutput(): string
    {
        $keyPath = $this->writeKeyFile();

        try {
            $command = sprintf(
                'ssh -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s "cat %s 2>/dev/null || echo \'\'"',
                escapeshellarg($keyPath),
                escapeshellarg($this->ssh_user),
                escapeshellarg($this->server_ip),
                escapeshellarg($this->outputFile())
            );

            $result = \Illuminate\Support\Facades\Process::timeout(10)->run($command);

            return $result->output();
        } catch (\Exception $e) {
            return '';
        } finally {
            @unlink($keyPath);
        }
    }

    protected function writeKeyFile(): string
    {
        $path = storage_path('app/keys/'.uniqid());
        file_put_contents($path, rtrim(config('remote-tasks.ssh_private_key')).PHP_EOL);
        chmod($path, 0600);

        return $path;
    }

    public function successful(): bool
    {
        return $this->exit_code === 0;
    }
}
