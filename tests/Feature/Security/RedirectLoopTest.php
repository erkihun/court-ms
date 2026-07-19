<?php

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

test('trusted forwarded https requests are not redirected back to the same url', function () {
    config(['app.url' => 'https://example.test']);

    SystemSetting::query()->create(['force_https' => true]);
    Cache::forget('system_settings');

    $this->withServerVariables([
        'REMOTE_ADDR' => '10.0.0.10',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_X_FORWARDED_HOST' => 'example.test',
    ])
        ->get('http://example.test/applicant/login')
        ->assertOk();
});

test('an unauthenticated applicant dashboard has a reachable login destination', function () {
    $this->get(route('applicant.dashboard'))
        ->assertRedirect(route('applicant.login'));

    $this->get(route('applicant.login'))
        ->assertOk();
});
