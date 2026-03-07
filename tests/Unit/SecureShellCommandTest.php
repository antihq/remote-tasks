<?php

use App\SecureShellCommand;

it('builds ssh command for script', function () {
    $command = SecureShellCommand::forScript(
        '192.168.1.1',
        '/path/to/key',
        'root',
        'ls -la'
    );

    expect($command)->toContain('ssh');
    expect($command)->toContain('root@192.168.1.1');
    expect($command)->toContain('ls -la');
});

it('includes ssh options', function () {
    $command = SecureShellCommand::forScript(
        '192.168.1.1',
        '/path/to/key',
        'root',
        'ls -la'
    );

    expect($command)->toContain('StrictHostKeyChecking=no');
    expect($command)->toContain('UserKnownHostsFile=/dev/null');
});

it('uses port 22', function () {
    $command = SecureShellCommand::forScript(
        '192.168.1.1',
        '/path/to/key',
        'root',
        'ls -la'
    );

    expect($command)->toContain('-p 22');
});

it('builds scp command for upload', function () {
    $command = SecureShellCommand::forUpload(
        '192.168.1.1',
        '/path/to/key',
        'root',
        '/local/file.sh',
        '/remote/file.sh'
    );

    expect($command)->toContain('scp');
    expect($command)->toContain('/local/file.sh');
    expect($command)->toContain('root@192.168.1.1:/remote/file.sh');
});

it('interpolates user and ip', function () {
    $command = SecureShellCommand::forScript(
        '192.168.1.1',
        '/path/to/key',
        'ubuntu',
        'ls'
    );

    expect($command)->toContain('ubuntu@192.168.1.1');
});
