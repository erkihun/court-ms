<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\CaseMessageController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\Integration\EuisisCasePartiesController;
use App\Http\Controllers\Api\Integration\EuisisCaseWorkspaceController;
use App\Http\Controllers\Api\Integration\EuisisInstitutionCaseController;
use App\Http\Controllers\Api\Integration\EuisisLetterController;
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

        // Flow 5: employees file from the EUISIS employee portal; court letters for EUISIS institutions.
        Route::post('/people/cases', [EuisisPeopleController::class, 'fileCase'])
            ->middleware(['integration.client:people:file', 'throttle:30,1']);
        Route::get('/letters/updates', [EuisisLetterController::class, 'updates'])
            ->middleware('integration.client:letters:read');
        Route::get('/letters/{letter}/pdf', [EuisisLetterController::class, 'pdf'])
            ->whereNumber('letter')
            ->middleware('integration.client:letters:read');

        // Flow 6: the employee's case workspace in the EUISIS employee portal. POST throughout,
        // so the employee identity travels in the body, never in a URL.
        Route::prefix('/people')->whereNumber(['case', 'file', 'evidence', 'witness', 'response', 'reply'])->group(function (): void {
            Route::middleware(['integration.client:people:read', 'throttle:120,1'])->group(function (): void {
                Route::post('/cases/{case}/workspace', [EuisisCaseWorkspaceController::class, 'workspace']);
                Route::post('/cases/{case}/edit', [EuisisCaseWorkspaceController::class, 'edit']);
                Route::post('/cases/{case}/responses/{response}', [EuisisCaseWorkspaceController::class, 'response']);
                Route::post('/cases/{case}/responses/{response}/replies/{reply}', [EuisisCaseWorkspaceController::class, 'reply']);
                Route::post('/cases/{case}/download', [EuisisCaseWorkspaceController::class, 'download']);
                Route::post('/notices', [EuisisCaseWorkspaceController::class, 'notices']);
                Route::post('/parties', [EuisisCasePartiesController::class, 'index']);
                Route::post('/lawyers', [EuisisCasePartiesController::class, 'lawyers']);
            });
            Route::middleware(['integration.client:people:act', 'throttle:60,1'])->group(function (): void {
                Route::post('/parties/{party}/respond', [EuisisCasePartiesController::class, 'respond'])->whereNumber('party');
                Route::post('/interventions', [EuisisCasePartiesController::class, 'intervene']);
                Route::post('/cases/{case}/correct', [EuisisCaseWorkspaceController::class, 'correct']);
                Route::post('/cases/{case}/messages', [EuisisCaseWorkspaceController::class, 'postMessage']);
                Route::post('/cases/{case}/files', [EuisisCaseWorkspaceController::class, 'uploadFile']);
                Route::post('/cases/{case}/files/{file}/delete', [EuisisCaseWorkspaceController::class, 'deleteFile']);
                Route::post('/cases/{case}/evidences/{evidence}/delete', [EuisisCaseWorkspaceController::class, 'deleteEvidence']);
                Route::post('/cases/{case}/witnesses/{witness}/delete', [EuisisCaseWorkspaceController::class, 'deleteWitness']);
                Route::post('/cases/{case}/responses/{response}/replies', [EuisisCaseWorkspaceController::class, 'storeReply']);
                Route::post('/cases/{case}/responses/{response}/replies/{reply}/update', [EuisisCaseWorkspaceController::class, 'updateReply']);
                Route::post('/cases/{case}/responses/{response}/replies/{reply}/delete', [EuisisCaseWorkspaceController::class, 'destroyReply']);
                Route::post('/notices/read', [EuisisCaseWorkspaceController::class, 'readNotices']);
                // An appeal to the appellate court (EUISIS ADR 0013 #14).
                Route::post('/cases/{case}/appeal', [EuisisCaseWorkspaceController::class, 'appeal']);
            });
        });

        // Flow 7: cases against EUISIS institutions, for the officer who follows them, and their appeals.
        Route::prefix('/institutions')->whereNumber('case')->group(function (): void {
            Route::middleware(['integration.client:institutions:read', 'throttle:120,1'])->group(function (): void {
                Route::post('/cases/new', [EuisisInstitutionCaseController::class, 'newCases']);
                Route::post('/cases/{case}', [EuisisInstitutionCaseController::class, 'show']);
                Route::post('/cases/{case}/appeal-record', [EuisisInstitutionCaseController::class, 'appealRecord']);
            });
            Route::post('/cases/{case}/appeal', [EuisisInstitutionCaseController::class, 'appeal'])
                ->middleware(['integration.client:institutions:act', 'throttle:60,1']);
        });
    });
