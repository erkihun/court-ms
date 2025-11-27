<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    | Supported drivers: "session"
    */
    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'applicant' => ['driver' => 'session', 'provider' => 'applicants'],
        'respondent' => ['driver' => 'session', 'provider' => 'respondents'],
    ],

    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => App\Models\User::class],
        'applicants' => ['driver' => 'eloquent', 'model' => App\Models\Applicant::class], // <-- this exact class
        'respondents' => ['driver' => 'eloquent', 'model' => App\Models\Respondent::class],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,   // minutes
            'throttle' => 60,   // seconds
        ],

        // Optional: password reset for applicants (shares token table)
        'applicants' => [
            'provider' => 'applicants',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],

        'respondents' => [
            'provider' => 'respondents',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    | Number of seconds before password confirmation times out (3 hours default)
    */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
