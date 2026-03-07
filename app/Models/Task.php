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
        'started_at',
        'finished_at',
    ];

    protected $casts = [
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

    public function successful(): bool
    {
        return $this->exit_code === 0;
    }
}
