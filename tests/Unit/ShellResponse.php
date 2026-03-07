<?php

use App\ShellResponse;

it('creates response with all parameters', function () {
    $response = new ShellResponse(0, 'Success output', false);

    expect($response->exitCode)->toBe(0);
    expect($response->output)->toBe('Success output');
    expect($response->timedOut)->toBeFalse();
});

it('defaults timed out to false', function () {
    $response = new ShellResponse(0, 'Success');

    expect($response->timedOut)->toBeFalse();
});

it('accepts zero exit code', function () {
    $response = new ShellResponse(0, 'Success');

    expect($response->exitCode)->toBe(0);
});

it('accepts non zero exit code', function () {
    $response = new ShellResponse(1, 'Error');

    expect($response->exitCode)->toBe(1);
});
