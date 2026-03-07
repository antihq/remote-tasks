#!/bin/bash
set -e

# Write user script to temp file
cat > {{ $tempScriptPath }} << 'SCRIPT_EOF'
{!! $task->script !!}

SCRIPT_EOF

# Execute the user's script
@if ($task->timeout > 0)
timeout {{ $task->timeout }}s bash {{ $tempScriptPath }}
@else
bash {{ $tempScriptPath }}
@endif

# Capture exit code
EXIT_CODE=$?

# Callback to home server with exit code
curl -X POST "{!! $callbackUrl !!}" -d "exit_code=${EXIT_CODE}" > /dev/null 2>&1 &
