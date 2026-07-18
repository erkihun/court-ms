<?php

declare(strict_types=1);

return [
    'security' => [
        'app_headers_enabled' => env('SECURITY_APP_HEADERS_ENABLED', false),
        'hsts_enabled' => env('SECURITY_HSTS_ENABLED', false),
        'hsts_max_age' => env('SECURITY_HSTS_MAX_AGE', 31_536_000),
        'csp_enforce' => env('SECURITY_CSP_ENFORCE', false),
        'content_security_policy' => env('SECURITY_CONTENT_SECURITY_POLICY', ''),
    ],

    'uploads' => [
        'scanner' => env('UPLOAD_SCANNER', 'none'),
        'scanner_binary' => env('UPLOAD_SCANNER_BINARY', 'clamscan'),
        'scanner_timeout_seconds' => env('UPLOAD_SCANNER_TIMEOUT', 30),
        'require_clean_scan' => env('UPLOAD_REQUIRE_CLEAN_SCAN', false),
    ],

    'continuity' => [
        'automated_backup' => env('BACKUP_AUTOMATED', false),
        'encrypted' => env('BACKUP_ENCRYPTED', false),
        'offsite' => env('BACKUP_OFFSITE', false),
        'restore_tested_at' => env('BACKUP_RESTORE_TESTED_AT'),
        'rpo_hours' => env('RECOVERY_RPO_HOURS'),
        'rto_hours' => env('RECOVERY_RTO_HOURS'),
    ],
];
