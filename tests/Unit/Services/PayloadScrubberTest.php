<?php

use App\Services\PayloadScrubber;

beforeEach(function () {
    $this->scrubber = new PayloadScrubber;
});

it('removes keys that look like secrets at any depth', function () {
    $clean = $this->scrubber->scrub([
        'title' => 'Bug',
        'password' => 'x',
        'access_token' => 'y',
        'nested' => ['api_key' => 'z', 'cvv' => '123', 'safe' => 'keep'],
        'cookie' => 'session=1',
    ]);

    expect($clean)->toBe(['title' => 'Bug', 'nested' => ['safe' => 'keep']]);
});

it('truncates console errors to 1000 characters and redacts bearer tokens and emails', function () {
    $errors = $this->scrubber->consoleErrors([
        str_repeat('a', 1500),
        'Failed for jane@example.com using Bearer eyJhbGciOi.payload.sig',
        '',
    ]);

    expect($errors)->toHaveCount(2)
        ->and(mb_strlen($errors[0]))->toBe(1000)
        ->and($errors[1])->toContain('[email]')
        ->and($errors[1])->toContain('Bearer [redacted]')
        ->and($errors[1])->not->toContain('jane@example.com');
});

it('removes sensitive query parameters from page URLs', function () {
    expect($this->scrubber->scrubUrl('https://app.example.com/pay?cart=9&api_key=abc&token=t#step'))
        ->toBe('https://app.example.com/pay?cart=9#step');
});

it('keeps null and invalid URLs unchanged', function () {
    expect($this->scrubber->scrubUrl(null))->toBeNull()
        ->and($this->scrubber->scrubUrl('not a url'))->toBe('not a url');
});

it('stores only allow-listed headers with lowercase names', function () {
    $headers = $this->scrubber->allowedHeaders([
        'User-Agent' => ['Mozilla/5.0'],
        'Authorization' => ['Bearer abc'],
        'Cookie' => ['session=1'],
        'Accept-Language' => ['ar'],
    ]);

    expect($headers)->toBe([
        'user-agent' => 'Mozilla/5.0',
        'accept-language' => 'ar',
    ]);
});
