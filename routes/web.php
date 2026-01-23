<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Applicant-facing controllers
use App\Http\Controllers\Applicant\ApplicantAuthController;
use App\Http\Controllers\Applicant\ApplicantCaseController;
use App\Http\Controllers\Applicant\ApplicantDashboardController;
use App\Http\Controllers\Applicant\ApplicantProfileController;
use App\Http\Controllers\Applicant\ApplicantRoleSwitchController;
use App\Http\Controllers\Applicant\ApplicantNotificationController;
use App\Http\Controllers\Applicant\ApplicantPasswordController;
use App\Http\Controllers\Applicant\ApplicantVerificationController;
use App\Http\Controllers\Respondent\RespondentAuthController;
use App\Http\Controllers\Respondent\ResponseController;
use App\Http\Controllers\Respondent\DashboardController as RespondentDashboardController;
use App\Http\Controllers\Respondent\ProfileController;
use App\Http\Controllers\Respondent\CaseSearchController;
use App\Http\Controllers\Respondent\NotificationController as RespondentNotificationController;

// Admin-facing controllers
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\BenchNoteController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AppealController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\CaseTypeController;
use App\Http\Controllers\Admin\DecisionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\LetterTemplateController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\LetterController;
use App\Http\Controllers\Admin\LetterComposerController;
use App\Http\Controllers\Admin\TermsAndConditionsController;
use App\Http\Controllers\Admin\RecordController;
use App\Http\Controllers\Admin\HearingController;

// Localization middleware
use App\Http\Middleware\SetLocale;
use App\Http\Controllers\TermsDisplayController;
use App\Http\Controllers\PublicSignageController;
use App\Http\Controllers\SecureFileController;

/*
|--------------------------------------------------------------------------
| All routes wrapped with SetLocale
|--------------------------------------------------------------------------
*/

Route::middleware(SetLocale::class)->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public / Locale
    |--------------------------------------------------------------------------
    */
    // Single canonical language switch route (matches admin layout)
    Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

    Route::get('/home', function () {
        $totalCases      = DB::table('court_cases')->count();
        $pendingCases    = DB::table('court_cases')->where('status', 'pending')->count();
        $resolvedCases   = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();
        $openCases       = max($totalCases - $resolvedCases, 0);
        $upcomingHearings = DB::table('case_hearings')
            ->whereBetween('hearing_at', [now(), now()->addDays(30)])
            ->count();
        $recentCases     = DB::table('court_cases')
            ->select('case_number', 'title', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('home', compact('totalCases', 'pendingCases', 'resolvedCases', 'openCases', 'upcomingHearings', 'recentCases'));
    })->name('landing.home');

    Route::get('/debug-locale', fn() => 'locale=' . app()->getLocale());
    Route::get('/', fn() => redirect()->route('applicant.login'))->name('root');
    Route::get('/terms', [TermsDisplayController::class, 'show'])->name('public.terms');
    Route::get('/signage', [PublicSignageController::class, 'show'])
        ->middleware(['throttle:30,1'])
        ->name('public.signage');
    Route::get('/applicant', fn() => redirect()->route('applicant.login'))->name('applicant.login.shortcut');
    Route::get('/respondent', fn() => redirect()->route('respondent.login'))->name('respondent.login.shortcut');

    /*
    |-------------------------------------------------------------------------- 
    | Respondent Public Routes (guest:respondent)
    |-------------------------------------------------------------------------- 
    */
    Route::middleware('guest:respondent')->group(function () {
        Route::get('/respondent/register', fn () => redirect()->route('applicant.login', ['login_as' => 'respondent']))->name('respondent.register');
        Route::post('/respondent/register', fn () => redirect()->route('applicant.login', ['login_as' => 'respondent']))->name('respondent.register.submit');
        Route::get('/respondent/login', fn () => redirect()->route('applicant.login', ['login_as' => 'respondent']))->name('respondent.login');
        Route::post('/respondent/login', fn () => redirect()->route('applicant.login', ['login_as' => 'respondent']))->name('respondent.login.submit');
    });

    Route::middleware(['auth:applicant'])->group(function () {
        Route::post('/respondent/logout', [RespondentAuthController::class, 'logout'])->name('respondent.logout');
        Route::get('/respondent/dashboard', [RespondentDashboardController::class, 'index'])->name('respondent.dashboard');
        Route::get('/respondent/case-search/results', [CaseSearchController::class, 'myCases'])->name('respondent.cases.my');
        Route::get('/respondent/responses', [ResponseController::class, 'index'])->name('respondent.responses.index');
        Route::get('/respondent/responses/create', [ResponseController::class, 'create'])->name('respondent.responses.create');
        Route::post('/respondent/responses', [ResponseController::class, 'store'])->name('respondent.responses.store');
        Route::get('/respondent/responses/{response}', [ResponseController::class, 'show'])->name('respondent.responses.show');
        Route::get('/respondent/responses/{response}/edit', [ResponseController::class, 'edit'])->name('respondent.responses.edit');
        Route::patch('/respondent/responses/{response}', [ResponseController::class, 'update'])->name('respondent.responses.update');
        Route::delete('/respondent/responses/{response}', [ResponseController::class, 'destroy'])->name('respondent.responses.destroy');
        Route::get('/respondent/profile', [ProfileController::class, 'edit'])->name('respondent.profile.edit');
        Route::patch('/respondent/profile', [ProfileController::class, 'update'])->name('respondent.profile.update');
        Route::patch('/respondent/profile/password', [ProfileController::class, 'updatePassword'])->name('respondent.profile.password');
        Route::post('/respondent/notifications/mark-one', [RespondentNotificationController::class, 'markOne'])->name('respondent.notifications.markOne');
        Route::post('/respondent/notifications/mark-all', [RespondentNotificationController::class, 'markAll'])->name('respondent.notifications.markAll');
        Route::post('/respondent/switch-to-applicant', [RespondentAuthController::class, 'switchToApplicant'])->name('respondent.switchToApplicant');
    });

    Route::get('/respondent/case-search', [CaseSearchController::class, 'index'])
        ->middleware(['auth:applicant', 'throttle:30,1'])
        ->name('respondent.case.search');
    Route::get('/respondent/cases/{caseNumber}', [CaseSearchController::class, 'show'])
        ->middleware(['auth:applicant', 'throttle:30,1'])
        ->name('respondent.cases.show')
        ->where('caseNumber', '.*');

    // Public-facing preview for applicants/respondents (authorization handled in controller)
    Route::get('/case-letters/{letter}', [LetterController::class, 'publicPreview'])
        ->name('letters.case-preview');

    // Legacy "/apply" prefix for email verification links emitted on production domains.
    Route::get('/apply/email/verify', function () {
        return redirect()->route('applicant.verification.notice');
    });
    Route::get('/apply/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
        return redirect()->route(
            'applicant.verification.verify',
            array_merge(['id' => $id, 'hash' => $hash], $request->query())
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Applicant Auth (guest:applicant)
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest:applicant')->group(function () {
        // Register
        Route::get('/applicant/register',  [ApplicantAuthController::class, 'showRegister'])->name('applicant.register');
        Route::post('/applicant/register', [ApplicantAuthController::class, 'register'])->name('applicant.register.submit');
        Route::get('/applicant/login',  [ApplicantAuthController::class, 'showLogin'])->name('applicant.login');
        Route::post('/applicant/login', [ApplicantAuthController::class, 'login'])->name('applicant.login.submit');
        Route::get('/applicant/forgot-password',        [ApplicantPasswordController::class, 'showLinkRequestForm'])->name('applicant.password.request');
        Route::post('/applicant/forgot-password',       [ApplicantPasswordController::class, 'sendResetLinkEmail'])->name('applicant.password.email');
        Route::get('/applicant/reset-password/{token}', [ApplicantPasswordController::class, 'showResetForm'])->name('applicant.password.reset');
        Route::post('/applicant/reset-password',        [ApplicantPasswordController::class, 'reset'])->name('applicant.password.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Applicant Portal (auth:applicant)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:applicant')->group(function () {
        Route::post('/applicant/logout', [ApplicantAuthController::class, 'logout'])->name('applicant.logout');

        // Email verification
        Route::get('/applicant/email/verify', [ApplicantVerificationController::class, 'notice'])->name('applicant.verification.notice');
        Route::post('/applicant/email/verification-notification', [ApplicantVerificationController::class, 'send'])
            ->middleware('throttle:6,1')->name('applicant.verification.send');
        Route::get('/applicant/email/verify/{id}/{hash}', [ApplicantVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])->name('applicant.verification.verify');

        Route::middleware([])->group(function () {
            // Dashboard
            Route::get('/applicant/dashboard', [ApplicantDashboardController::class, 'index'])->name('applicant.dashboard');

            // Profile
            Route::get('/applicant/profile',  [ApplicantProfileController::class, 'edit'])->name('applicant.profile.edit');
            Route::patch('/applicant/profile', [ApplicantProfileController::class, 'update'])->name('applicant.profile.update');
            Route::post('/applicant/switch-to-respondent', ApplicantRoleSwitchController::class)
                ->name('applicant.switchToRespondent');

            // Cases (only applicant's own)
            Route::get('/applicant/cases',            [ApplicantCaseController::class, 'index'])->name('applicant.cases.index');
            Route::get('/applicant/cases/create',     [ApplicantCaseController::class, 'create'])->name('applicant.cases.create');
            Route::post('/applicant/cases',           [ApplicantCaseController::class, 'store'])->name('applicant.cases.store');
            Route::get('/applicant/cases/{id}',       [ApplicantCaseController::class, 'show'])->name('applicant.cases.show');
            Route::get('/applicant/cases/{case}/respondent-responses/{response}', [ApplicantCaseController::class, 'showRespondentResponse'])->name('applicant.cases.respondentResponses.show');
            Route::get('/applicant/cases/{case}/respondent-responses/{response}/reply', [ApplicantCaseController::class, 'replyRespondentResponse'])->name('applicant.cases.respondentResponses.reply');
            Route::get('/applicant/cases/{id}/edit',  [ApplicantCaseController::class, 'edit'])->name('applicant.cases.edit');
            Route::patch('/applicant/cases/{id}',     [ApplicantCaseController::class, 'update'])->name('applicant.cases.update');
            Route::delete('/applicant/cases/{id}', [ApplicantCaseController::class, 'destroy'])
                ->name('applicant.cases.destroy');
            // Files
            Route::post('/applicant/cases/{id}/files',            [ApplicantCaseController::class, 'uploadFile'])->name('applicant.cases.files.upload');
            Route::delete('/applicant/cases/{id}/files/{fileId}', [ApplicantCaseController::class, 'deleteFile'])->name('applicant.cases.files.delete');
            Route::get('/applicant/cases/{id}/files/{fileId}/download', [SecureFileController::class, 'caseFile'])
                ->name('applicant.cases.files.download');

            // Evidences / Witnesses
            Route::delete('/applicant/cases/{id}/evidences/{evidenceId}', [ApplicantCaseController::class, 'deleteEvidence'])->name('applicant.cases.evidences.delete');
            Route::get('/applicant/cases/{id}/evidences/{evidenceId}/download', [SecureFileController::class, 'caseEvidence'])
                ->name('applicant.cases.evidences.download');
            Route::delete('/applicant/cases/{id}/witnesses/{witnessId}',  [ApplicantCaseController::class, 'deleteWitness'])->name('applicant.cases.witnesses.delete');

            // Messages
            Route::post('/applicant/cases/{id}/messages', [ApplicantCaseController::class, 'postMessage'])->name('applicant.cases.messages.post');

            // Receipts / exports
            Route::get('/applicant/cases/{id}/receipt',        [ApplicantCaseController::class, 'receipt'])->name('applicant.cases.receipt');
            Route::get('/applicant/cases/{id}/receipt/pdf',    [ApplicantCaseController::class, 'receiptPdf'])->name('applicant.cases.receipt.pdf');
            Route::post('/applicant/cases/{id}/receipt/email', [ApplicantCaseController::class, 'emailReceipt'])->name('applicant.cases.receipt.email');

            // Hearing ICS
            Route::get('/applicant/cases/{id}/hearings/{hearingId}/ics', [ApplicantCaseController::class, 'downloadHearingIcs'])->name('applicant.cases.hearings.ics');

            // Respondent / client actions (case search + responses)
            Route::middleware('throttle:30,1')->group(function () {
                Route::get('/applicant/respondent/case-search', [CaseSearchController::class, 'index'])
                    ->name('applicant.respondent.cases.search');
                Route::get('/applicant/respondent/cases/{caseNumber}', [CaseSearchController::class, 'show'])
                    ->name('applicant.respondent.cases.show')
                    ->where('caseNumber', '.*');
            });
            Route::get('/applicant/respondent/cases/my', [CaseSearchController::class, 'myCases'])
                ->middleware('throttle:30,1')
                ->name('applicant.respondent.cases.my');

            Route::get('/applicant/respondent/responses', [ResponseController::class, 'index'])
                ->name('applicant.respondent.responses.index');
            Route::get('/applicant/respondent/responses/create', [ResponseController::class, 'create'])
                ->name('applicant.respondent.responses.create');
            Route::post('/applicant/respondent/responses', [ResponseController::class, 'store'])
                ->name('applicant.respondent.responses.store');
            Route::get('/applicant/respondent/responses/{response}', [ResponseController::class, 'show'])
                ->name('applicant.respondent.responses.show');
            Route::get('/applicant/respondent/responses/{response}/edit', [ResponseController::class, 'edit'])
                ->name('applicant.respondent.responses.edit');
            Route::patch('/applicant/respondent/responses/{response}', [ResponseController::class, 'update'])
                ->name('applicant.respondent.responses.update');
            Route::delete('/applicant/respondent/responses/{response}', [ResponseController::class, 'destroy'])
                ->name('applicant.respondent.responses.destroy');
            Route::get('/applicant/respondent/responses/{response}/download', [SecureFileController::class, 'respondentResponse'])
                ->name('applicant.respondent.responses.download');

            // Notifications
            Route::get('/applicant/notifications',           [ApplicantNotificationController::class, 'index'])->name('applicant.notifications.index');
            Route::post('/applicant/notifications/mark-one', [ApplicantNotificationController::class, 'markOne'])->name('applicant.notifications.markOne');
            Route::post('/applicant/notifications/mark-all', [ApplicantNotificationController::class, 'markAll'])->name('applicant.notifications.markAll');

            // Notification settings
            Route::get('/applicant/notifications/settings',  [ApplicantNotificationController::class, 'settingsEdit'])->name('applicant.notifications.settings');
            Route::post('/applicant/notifications/settings', [ApplicantNotificationController::class, 'settingsUpdate'])->name('applicant.notifications.settings.update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin area (auth:web)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'force.password.change', 'verified', \App\Http\Middleware\SystemAuditMiddleware::class])->group(function () {
        // Admin dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Admin self profile
        Route::get('/profile',    [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');

        // Admin section with /admin prefix
        Route::prefix('admin')->group(function () {
            Route::get('/', fn() => redirect()->route('admin.dashboard'))
                ->name('admin.home');

              Route::get('/dashboard', [DashboardController::class, 'index'])
                  ->name('admin.dashboard');

              Route::get('/reports', [ReportController::class, 'index'])
                  ->middleware('perm:reports.view')
                  ->name('reports.index');

              // Dashboard stats (AJAX)
              Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
                  ->name('admin.dashboard.stats');

            // System settings
            Route::get('/settings/system', [SystemSettingController::class, 'edit'])
                ->name('settings.system.edit');

            Route::post('/settings/system', [SystemSettingController::class, 'update'])
                ->name('settings.system.update');

            // System audit (read-only view)
            Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])
                ->name('admin.audit');

            /*
             * Cases (admin)
             */
            Route::patch('/cases/{id}/review', [CaseController::class, 'reviewDecision'])
                ->middleware('perm:cases.review')
                ->name('cases.review.update');

            Route::patch('/respondent-responses/{response}/review', [CaseController::class, 'reviewRespondentResponse'])
                ->middleware('perm:cases.review')
                ->name('respondent-responses.review');

            Route::get('/cases',                   [CaseController::class, 'index'])->middleware('perm:cases.view')->name('cases.index');
            Route::get('/cases/export',            [CaseController::class, 'export'])->middleware('perm:reports.export')->name('cases.export');
            Route::get('/cases/{id}',              [CaseController::class, 'show'])->middleware('perm:cases.view')->name('cases.show');
            Route::get('/cases/{case}/documents/{doc}', [CaseController::class, 'viewDocument'])->middleware('perm:cases.view')->name('cases.documents.view');

            Route::get('/cases/{caseId}/assign',   [CaseController::class, 'assignForm'])
                ->middleware('perm:cases.assign')
                ->name('cases.assign.form');
            Route::patch('/cases/{caseId}/assign', [CaseController::class, 'assignUpdate'])
                ->middleware('perm:cases.assign')
                ->name('cases.assign.update');

            Route::patch('/cases/{id}/status',     [CaseController::class, 'updateStatus'])->middleware('perm:cases.edit')->name('cases.status.update');
            Route::post('/cases/{id}/messages',    [CaseController::class, 'postAdminMessage'])->middleware('perm:cases.edit')->name('cases.messages.post');

            // Registrar review (accept/reject) - route used by index blade
            Route::post('/cases/review',           [CaseController::class, 'review'])->middleware('perm:cases.review')->name('cases.review');

            // Hearings
            Route::post('/cases/{case}/hearings',  [CaseController::class, 'storeHearing'])->middleware('perm:cases.edit')->name('cases.hearings.store');
            Route::patch('/hearings/{hearing}',    [CaseController::class, 'updateHearing'])->middleware('perm:cases.edit')->name('cases.hearings.update');
            Route::delete('/hearings/{hearing}',   [CaseController::class, 'deleteHearing'])->middleware('perm:cases.edit')->name('cases.hearings.delete');
            Route::get('/hearings',                [HearingController::class, 'index'])->middleware('perm:cases.view')->name('admin.hearings.index');

            Route::get('/admin/chat',              [ChatController::class, 'index'])->middleware('perm:cases.view')->name('admin.chat');
            Route::get('/admin/chat/conversation/{user}', [ChatController::class, 'conversation'])->middleware('perm:cases.view')->name('admin.chat.conversation');
            Route::post('/admin/chat/messages',    [ChatController::class, 'storeMessage'])->middleware('perm:cases.edit')->name('admin.chat.messages');

            // Files
            Route::post('/cases/{case}/files',          [CaseController::class, 'storeFile'])->middleware('perm:cases.edit')->name('cases.files.upload');
            Route::delete('/cases/{case}/files/{file}', [CaseController::class, 'deleteFile'])->middleware('perm:cases.edit')->name('cases.files.delete');
            Route::get('/cases/{case}/files/{file}/download', [SecureFileController::class, 'caseFile'])
                ->middleware('perm:cases.view')
                ->name('cases.files.download');

            // Witnesses
            Route::post('/cases/{case}/witnesses',            [CaseController::class, 'storeWitness'])->middleware('perm:cases.edit')->name('cases.witnesses.store');
            Route::patch('/cases/{case}/witnesses/{witness}', [CaseController::class, 'updateWitness'])->middleware('perm:cases.edit')->name('cases.witnesses.update');
            Route::delete('/cases/{case}/witnesses/{witness}', [CaseController::class, 'deleteWitness'])->middleware('perm:cases.edit')->name('cases.witnesses.delete');

            Route::middleware('perm:bench-notes.manage')->group(function () {
                Route::get('/bench-notes', [BenchNoteController::class, 'index'])->name('bench-notes.index');
                Route::get('/bench-notes/create', [BenchNoteController::class, 'create'])->name('bench-notes.create');
                Route::post('/bench-notes', [BenchNoteController::class, 'store'])->name('bench-notes.store');
                Route::get('/bench-notes/{benchNote}', [BenchNoteController::class, 'show'])->name('bench-notes.show');
                Route::get('/bench-notes/{benchNote}/edit', [BenchNoteController::class, 'edit'])->name('bench-notes.edit');
                Route::patch('/bench-notes/{benchNote}', [BenchNoteController::class, 'update'])->name('bench-notes.update');
                Route::delete('/bench-notes/{benchNote}', [BenchNoteController::class, 'destroy'])->name('bench-notes.destroy');
            });

            // Applicants
            Route::get('/applicants', [ApplicantController::class, 'index'])
                ->middleware('perm:applicants.view')
                ->name('applicants.index');
            Route::patch('/applicants/{applicant}/status', [ApplicantController::class, 'updateStatus'])
                ->middleware('perm:applicants.manage')
                ->name('applicants.status.update');

            // Teams
            Route::get('/teams', [TeamController::class, 'index'])
                ->middleware('perm:teams.manage')
                ->name('teams.index');
            Route::get('/teams/create', [TeamController::class, 'create'])
                ->middleware('perm:teams.manage')
                ->name('teams.create');
            Route::post('/teams', [TeamController::class, 'store'])
                ->middleware('perm:teams.manage')
                ->name('teams.store');
            Route::get('/teams/{team}', [TeamController::class, 'show'])
                ->middleware('perm:teams.manage')
                ->name('teams.show');
            Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])
                ->middleware('perm:teams.manage')
                ->name('teams.edit');
            Route::patch('/teams/{team}', [TeamController::class, 'update'])
                ->middleware('perm:teams.manage')
                ->name('teams.update');
            Route::patch('/teams/{team}/users', [TeamController::class, 'updateUsers'])
                ->middleware('perm:teams.manage')
                ->name('teams.users.update');
            Route::delete('/teams/{team}', [TeamController::class, 'destroy'])
                ->middleware('perm:teams.manage')
                ->name('teams.destroy');

            /*
             * Permissions (admin)
             * (kept inside /admin; names remain 'permissions.*' which matches the sidebar)
             */
            Route::resource('permissions', PermissionController::class)
                ->except(['show'])
                ->middleware('perm:permissions.manage');

            /*
             * Appeals (admin)
             */
            Route::get('/appeals',                  [AppealController::class, 'index'])->middleware('perm:appeals.view')->name('appeals.index');
            Route::get('/appeals/create',           [AppealController::class, 'create'])->middleware('perm:appeals.create')->name('appeals.create');
            Route::post('/appeals',                 [AppealController::class, 'store'])->middleware('perm:appeals.create')->name('appeals.store');
            Route::get('/appeals/{appeal}',         [AppealController::class, 'show'])->middleware('perm:appeals.view')->name('appeals.show');
            Route::get('/appeals/{appeal}/edit',    [AppealController::class, 'edit'])->middleware('perm:appeals.edit')->name('appeals.edit');
            Route::patch('/appeals/{appeal}',       [AppealController::class, 'update'])->middleware('perm:appeals.edit')->name('appeals.update');
            Route::post('/appeals/{appeal}/submit', [AppealController::class, 'submit'])->middleware('perm:appeals.edit')->name('appeals.submit');
            Route::post('/appeals/{appeal}/decide', [AppealController::class, 'decide'])->middleware('perm:appeals.decide')->name('appeals.decide');

            /*
             * Decisions (admin)
             */
            Route::get('/decisions', [DecisionController::class, 'index'])
                ->middleware('perm:decision.view')
                ->name('decisions.index');
            Route::get('/decisions/create', [DecisionController::class, 'create'])
                ->middleware('perm:decision.create')
                ->name('decisions.create');
            Route::post('/decisions', [DecisionController::class, 'store'])
                ->middleware('perm:decision.create')
                ->name('decisions.store');
            Route::get('/decisions/{decision}', [DecisionController::class, 'show'])
                ->middleware('perm:decision.view')
                ->name('decisions.show');
            Route::post('/decisions/{decision}/reviews', [DecisionController::class, 'storeReview'])
                ->middleware('perm:decision.update')
                ->name('decisions.reviews.store');
            Route::get('/decisions/{decision}/reviews/{review}/edit', [DecisionController::class, 'editReview'])
                ->middleware('perm:decision.update')
                ->name('decisions.reviews.edit');
            Route::patch('/decisions/{decision}/reviews/{review}', [DecisionController::class, 'updateReview'])
                ->middleware('perm:decision.update')
                ->name('decisions.reviews.update');
            Route::delete('/decisions/{decision}/reviews/{review}', [DecisionController::class, 'destroyReview'])
                ->middleware('perm:decision.update')
                ->name('decisions.reviews.destroy');
            Route::get('/decisions/{decision}/edit', [DecisionController::class, 'edit'])
                ->middleware('perm:decision.update')
                ->name('decisions.edit');
            Route::patch('/decisions/{decision}', [DecisionController::class, 'update'])
                ->middleware('perm:decision.update')
                ->name('decisions.update');
            Route::delete('/decisions/{decision}', [DecisionController::class, 'destroy'])
                ->middleware('perm:decision.delete')
                ->name('decisions.destroy');

            // Case records (consolidated PDF/HTML)
            Route::get('/recordes', [RecordController::class, 'index'])
                ->middleware('perm:cases.view')
                ->name('recordes.index');
            Route::get('/recordes/{case}', [RecordController::class, 'show'])
                ->middleware('perm:cases.view')
                ->name('recordes.show');
            Route::get('/recordes/{case}/pdf', [RecordController::class, 'pdf'])
                ->middleware('perm:cases.view')
                ->name('recordes.pdf');

            // Appeal documents
            Route::post('/appeals/{appeal}/documents',         [AppealController::class, 'uploadDoc'])->middleware('perm:appeals.edit')->name('appeals.docs.upload');
            Route::get('/appeals/{appeal}/documents/{doc}/download', [SecureFileController::class, 'appealDocument'])
                ->middleware('perm:appeals.view')
                ->name('appeals.docs.download');
            Route::delete('/appeals/{appeal}/documents/{doc}', [AppealController::class, 'deleteDoc'])->middleware('perm:appeals.edit')->name('appeals.docs.delete');

            // Admin notifications (matches admin layout links)
            Route::get('/notifications',           [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
            Route::post('/notifications/mark-one', [AdminNotificationController::class, 'markOne'])->name('admin.notifications.markOne');
            Route::post('/notifications/mark-all', [AdminNotificationController::class, 'markAll'])->name('admin.notifications.markAll');

            // Case Types (matches admin layout link 'case-types.index')
            Route::get('/case-types',           [CaseTypeController::class, 'index'])->name('case-types.index');
            Route::get('/case-types/create',    [CaseTypeController::class, 'create'])->name('case-types.create');
            Route::post('/case-types',          [CaseTypeController::class, 'store'])->name('case-types.store');
            Route::get('/case-types/{id}/edit', [CaseTypeController::class, 'edit'])->name('case-types.edit');
            Route::patch('/case-types/{id}',    [CaseTypeController::class, 'update'])->name('case-types.update');
            Route::delete('/case-types/{id}',   [CaseTypeController::class, 'destroy'])->name('case-types.delete');

            // Letter templates
            // Letter templates with granular permissions
            Route::get('/letter-templates', [LetterTemplateController::class, 'index'])
                ->middleware('perm:letters.templet.view')
                ->name('letter-templates.index');
            Route::get('/letter-templates/create', [LetterTemplateController::class, 'create'])
                ->middleware('perm:letters.templet.create')
                ->name('letter-templates.create');
            Route::post('/letter-templates', [LetterTemplateController::class, 'store'])
                ->middleware('perm:letters.templet.create')
                ->name('letter-templates.store');
            Route::get('/letter-templates/{letter_template}/edit', [LetterTemplateController::class, 'edit'])
                ->middleware('perm:letters.templet.update')
                ->name('letter-templates.edit');
            Route::patch('/letter-templates/{letter_template}', [LetterTemplateController::class, 'update'])
                ->middleware('perm:letters.templet.update')
                ->name('letter-templates.update');
            Route::delete('/letter-templates/{letter_template}', [LetterTemplateController::class, 'destroy'])
                ->middleware('perm:letters.templet.delete')
                ->name('letter-templates.destroy');

            Route::resource('terms', TermsAndConditionsController::class)
                ->except(['show'])
                ->middleware('perm:settings.manage')
                ->names('terms');

            // Letters with granular permissions
            Route::get('/letters/compose', [LetterComposerController::class, 'create'])
                ->middleware('perm:letters.create')
                ->name('letters.compose');
            Route::get('/letters', [LetterController::class, 'index'])
                ->middleware('perm:letters.view')
                ->name('letters.index');
            Route::post('/letters', [LetterController::class, 'store'])
                ->middleware('perm:letters.create')
                ->name('letters.store');
            Route::get('/letters/{letter}', [LetterController::class, 'show'])
                ->middleware('perm:letters.view')
                ->name('letters.show');
            Route::get('/letters/{letter}/edit', [LetterController::class, 'edit'])
                ->middleware('perm:letters.update')
                ->name('letters.edit');
            Route::post('/letters/{letter}/approve', [LetterController::class, 'approve'])
                ->middleware('perm:letters.approve')
                ->name('letters.approve');
            Route::patch('/letters/{letter}', [LetterController::class, 'update'])
                ->middleware('perm:letters.update')
                ->name('letters.update');
            Route::delete('/letters/{letter}', [LetterController::class, 'destroy'])
                ->middleware('perm:letters.delete')
                ->name('letters.destroy');
        });

        /*
         * Users & Roles (top-level, still under auth:)
         * Keep names as 'users.*' and 'roles.*' to satisfy admin layout.
         */
        Route::middleware('perm:users.manage')->group(function () {
            Route::get('users',        [UsersController::class, 'index'])->name('users.index');
            Route::get('users/create', [UsersController::class, 'create'])->name('users.create');
            Route::post('users',       [UsersController::class, 'store'])->name('users.store');
        });

        Route::get('users/{user}',      [UsersController::class, 'show'])->middleware('can:view,user')->name('users.show');
        Route::get('users/{user}/edit', [UsersController::class, 'edit'])->middleware('can:update,user')->name('users.edit');
        Route::patch('users/{user}',    [UsersController::class, 'update'])->middleware('can:update,user')->name('users.update');
        Route::delete('users/{user}',   [UsersController::class, 'destroy'])->middleware('can:delete,user')->name('users.destroy');

        // Roles (resource with explicit names 'roles.*' as used in sidebar)
        Route::resource('roles', RolesController::class)
            ->except(['show'])
            ->middleware('perm:roles.manage')
            ->names('roles');
    });

    require __DIR__ . '/auth.php';
});
