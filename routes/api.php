<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\CaseMessageController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\Integration\EuisisPeopleController;
use App\Http\Controllers\Api\Integration\EuisisReferralController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\RespondentResponseController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('v1')
    ->middleware('throttle:api-ip-hourly')
    ->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/case-types', [LookupController::class, 'caseTypes']);

        Route::get('/cases', [CaseController::class, 'index']);
        Route::post('/cases', [CaseController::class, 'store']);
        Route::get('/cases/{case}', [CaseController::class, 'show'])->whereNumber('case');

        Route::get('/cases/{case}/messages', [CaseMessageController::class, 'index'])->whereNumber('case');
        Route::post('/cases/{case}/messages', [CaseMessageController::class, 'store'])->whereNumber('case');

        Route::get('/responses', [RespondentResponseController::class, 'index']);
        Route::get('/responses/{response}', [RespondentResponseController::class, 'show'])->whereNumber('response');
        Route::post('/responses', [RespondentResponseController::class, 'store']);
    });
});

// EUISIS integration (docs/euisis-integration.md): machine tokens from
// `php artisan euisis:integration-token`, never a person's Sanctum token.
Route::prefix('v1/integration')
    ->middleware('throttle:120,1')
    ->group(function () {
        Route::get('/case-types', [EuisisReferralController::class, 'caseTypes'])
            ->middleware('integration.client:referrals:read');
        Route::post('/referrals', [EuisisReferralController::class, 'store'])
            ->middleware('integration.client:referrals:create');
        Route::get('/referrals/updates', [EuisisReferralController::class, 'updates'])
            ->middleware('integration.client:referrals:read');
        Route::get('/referrals/{referral}', [EuisisReferralController::class, 'show'])
            ->whereUuid('referral')
            ->middleware('integration.client:referrals:read');

        // Flow 4: the EUISIS employee portal's court section. POSTs, so a national ID never sits in a URL.
        Route::post('/people/court-summary', [EuisisPeopleController::class, 'summary'])
            ->middleware('integration.client:people:read');
        Route::post('/people/sign-in-tickets', [EuisisPeopleController::class, 'signInTicket'])
            ->middleware(['integration.client:people:sign-in', 'throttle:60,1']);
    });
