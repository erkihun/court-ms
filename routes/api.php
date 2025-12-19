<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaseController;
use App\Http\Controllers\Api\CaseMessageController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\RespondentResponseController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

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
