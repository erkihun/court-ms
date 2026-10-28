<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | EUISIS (Agelgay) integration — docs/euisis-integration.md.
    | Outbound: court-ms reads organizations and people from the EUISIS
    | partner API with a registered-application token. Inbound: EUISIS sends
    | tribunal referrals with an integration client token (euisis:integration-token).
    */
    'euisis' => [
        'enabled' => (bool) env('EUISIS_ENABLED', false),
        // Only EUISIS employees and institutions may sue or be sued: new cases come from EUISIS
        // (the employee portal, or a grievance referral) with an EUISIS respondent institution.
        'parties_only' => (bool) env('EUISIS_PARTIES_ONLY', (bool) env('EUISIS_ENABLED', false)),
        // Employees work on their cases in the EUISIS employee portal (Flow 6): the applicant and
        // respondent portal is closed. On with EUISIS parties only; EUISIS_PORTAL_URL is where they go.
        'portal_in_euisis' => (bool) env('EUISIS_PORTAL_IN_EUISIS', (bool) env('EUISIS_PARTIES_ONLY', (bool) env('EUISIS_ENABLED', false))),
        'portal_url' => env('EUISIS_PORTAL_URL'),
        'url' => rtrim((string) env('EUISIS_URL', ''), '/'),
        'token' => env('EUISIS_TOKEN'),
        'timeout' => (int) env('EUISIS_TIMEOUT', 15),
        'verify_tls' => (bool) env('EUISIS_VERIFY_TLS', true),
        // Directory page size; the EUISIS API allows at most 100.
        'per_page' => (int) env('EUISIS_PER_PAGE', 100),
        // Change-feed pages applied per run; the next scheduled run continues from the cursor.
        'max_pages_per_run' => (int) env('EUISIS_MAX_PAGES_PER_RUN', 20),
    ],

];
