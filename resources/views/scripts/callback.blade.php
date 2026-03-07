#!/bin/bash
set -e

# Execute the user's script
@if ($task->timeout > 0)
timeout {{ $task->timeout }}s bash {{ $scriptPath }}
EXIT_CODE=$?
@else
bash {{ $scriptPath }}
EXIT_CODE=$?
@endif

# Callback to home server with exit code
curl -X POST "{{ $callbackUrl }}" -d "exit_code=${EXIT_CODE}" > /dev/null 2>&1 &
