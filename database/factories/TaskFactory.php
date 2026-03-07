<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'server_ip' => '192.168.1.1',
            'ssh_user' => 'root',
            'script' => 'echo "Test"',
            'status' => 'pending',
            'exit_code' => null,
            'output' => null,
            'timeout' => 3600,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function finished(int $exitCode = 0, string $output = 'Success'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finished',
            'exit_code' => $exitCode,
            'output' => $output,
            'finished_at' => now(),
        ]);
    }

    public function timedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'timeout',
            'finished_at' => now(),
        ]);
    }
}
