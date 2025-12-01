<?php

use Illuminate\Support\Facades\Route;

// Applicant-facing controllers
use App\Http\Controllers\Applicant\ApplicantAuthController;
use App\Http\Controllers\Applicant\ApplicantCaseController;
use App\Http\Controllers\Applicant\ApplicantDashboardController;
use App\Http\Controllers\Applicant\ApplicantProfileController;
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
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AppealController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\CaseTypeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\LetterTemplateController;
use App\Http\Controllers\Admin\LetterController;
use App\Http\Controllers\Admin\LetterComposerController;
use App\Http\Controllers\Admin\TermsAndConditionsController;

// Localization middleware
use App\Http\Middleware\SetLocale;
use App\Http\Controllers\TermsDisplayController;

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

    Route::get('/debug-locale', fn() => 'locale=' . app()->getLocale());
    Route::get('/', fn() => redirect()->route('applicant.login'))->name('root');
    Route::get('/terms', [TermsDisplayController::class, 'show'])->name('public.terms');
    Route::get('/applicant', fn() => redirect()->route('applicant.login'))->name('applicant.login.shortcut');
    Route::get('/respondent', fn() => redirect()->route('respondent.login'))->name('respondent.login.shortcut');

    /*
    |--------------------------------------------------------------------------
    | Respondent Public Routes (guest:respondent)
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest:respondent')->group(function () {
        Route::get('/respondent/register', [RespondentAuthController::class, 'showRegister'])->name('respondent.register');
        Route::post('/respondent/register', [RespondentAuthController::class, 'register'])->name('respondent.register.submit');
        Route::get('/respondent/login', [RespondentAuthController::class, 'showLogin'])->name('respondent.login');
        Route::post('/respondent/login', [RespondentAuthController::class, 'login'])->name('respondent.login.submit');
    });

    Route::middleware('auth:respondent')->group(function () {
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
    });

    Route::get('/respondent/case-search', [CaseSearchController::class, 'index'])->name('respondent.case.search');
    Route::get('/respondent/cases/{caseNumber}', [CaseSearchController::class, 'show'])->name('respondent.cases.show');

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

        // Dashboard (add ->middleware('verified') if you want to enforce it)
        Route::get('/applicant/dashboard', [ApplicantDashboardController::class, 'index'])->name('applicant.dashboard');

        // Profile
        Route::get('/applicant/profile',  [ApplicantProfileController::class, 'edit'])->name('applicant.profile.edit');
        Route::patch('/applicant/profile', [ApplicantProfileController::class, 'update'])->name('applicant.profile.update');

        // Cases (only applicant's own)
        Route::get('/applicant/cases',            [ApplicantCaseController::class, 'index'])->name('applicant.cases.index');
        Route::get('/applicant/cases/create',     [ApplicantCaseController::class, 'create'])->name('applicant.cases.create');
        Route::post('/applicant/cases',           [ApplicantCaseController::class, 'store'])->name('applicant.cases.store');
        Route::get('/applicant/cases/{id}',       [ApplicantCaseController::class, 'show'])->name('applicant.cases.show');
        Route::get('/applicant/cases/{id}/edit',  [ApplicantCaseController::class, 'edit'])->name('applicant.cases.edit');
        Route::patch('/applicant/cases/{id}',     [ApplicantCaseController::class, 'update'])->name('applicant.cases.update');
        Route::delete('/applicant/cases/{id}', [ApplicantCaseController::class, 'destroy'])
            ->name('applicant.cases.destroy');
        // Files
        Route::post('/applicant/cases/{id}/files',            [ApplicantCaseController::class, 'uploadFile'])->name('applicant.cases.files.upload');
        Route::delete('/applicant/cases/{id}/files/{fileId}', [ApplicantCaseController::class, 'deleteFile'])->name('applicant.cases.files.delete');

        // Evidences / Witnesses
        Route::delete('/applicant/cases/{id}/evidences/{evidenceId}', [ApplicantCaseController::class, 'deleteEvidence'])->name('applicant.cases.evidences.delete');
        Route::delete('/applicant/cases/{id}/witnesses/{witnessId}',  [ApplicantCaseController::class, 'deleteWitness'])->name('applicant.cases.witnesses.delete');

        // Messages
        Route::post('/applicant/cases/{id}/messages', [ApplicantCaseController::class, 'postMessage'])->name('applicant.cases.messages.post');

        // Receipts / exports
        Route::get('/applicant/cases/{id}/receipt',        [ApplicantCaseController::class, 'receipt'])->name('applicant.cases.receipt');
        Route::get('/applicant/cases/{id}/receipt/pdf',    [ApplicantCaseController::class, 'receiptPdf'])->name('applicant.cases.receipt.pdf');
        Route::post('/applicant/cases/{id}/receipt/email', [ApplicantCaseController::class, 'emailReceipt'])->name('applicant.cases.receipt.email');

        // Hearing ICS
        Route::get('/applicant/cases/{id}/hearings/{hearingId}/ics', [ApplicantCaseController::class, 'downloadHearingIcs'])->name('applicant.cases.hearings.ics');

        // Notifications
        Route::get('/applicant/notifications',           [ApplicantNotificationController::class, 'index'])->name('applicant.notifications.index');
        Route::post('/applicant/notifications/mark-one', [ApplicantNotificationController::class, 'markOne'])->name('applicant.notifications.markOne');
        Route::post('/applicant/notifications/mark-all', [ApplicantNotificationController::class, 'markAll'])->name('applicant.notifications.markAll');

        // Notification settings
        Route::get('/applicant/notifications/settings',  [ApplicantNotificationController::class, 'settingsEdit'])->name('applicant.notifications.settings');
        Route::post('/applicant/notifications/settings', [ApplicantNotificationController::class, 'settingsUpdate'])->name('applicant.notifications.settings.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin area (auth:web)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'force.password.change'])->group(function () {
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

            // Dashboard stats (AJAX)
            Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
                ->name('admin.dashboard.stats');

            // System settings
            Route::get('/settings/system', [SystemSettingController::class, 'edit'])
                ->name('settings.system.edit');

            Route::post('/settings/system', [SystemSettingController::class, 'update'])
                ->name('settings.system.update');

            /*
             * Cases (admin)
             */
            Route::patch('/cases/{id}/review', [CaseController::class, 'reviewDecision'])
                ->middleware('perm:cases.review')
                ->name('cases.review.update');

            Route::get('/cases',                   [CaseController::class, 'index'])->middleware('perm:cases.view')->name('cases.index');
            Route::get('/cases/export',            [CaseController::class, 'export'])->middleware('perm:reports.export')->name('cases.export');
            Route::get('/cases/{id}',              [CaseController::class, 'show'])->middleware('perm:cases.view')->name('cases.show');
            Route::get('/cases/{case}/documents/{doc}', [CaseController::class, 'viewDocument'])->middleware('perm:cases.view')->name('cases.documents.view');

            Route::get('/cases/{caseId}/assign',   [CaseController::class, 'assignForm'])->middleware('perm:cases.assign')->name('cases.assign.form');
            Route::patch('/cases/{caseId}/assign', [CaseController::class, 'assignUpdate'])->middleware('perm:cases.assign')->name('cases.assign.update');

            Route::patch('/cases/{id}/status',     [CaseController::class, 'updateStatus'])->middleware('perm:cases.edit')->name('cases.status.update');
            Route::post('/cases/{id}/messages',    [CaseController::class, 'postAdminMessage'])->middleware('perm:cases.edit')->name('cases.messages.post');

            // Registrar review (accept/reject) - route used by index blade
            Route::post('/cases/review',           [CaseController::class, 'review'])->middleware('perm:cases.review')->name('cases.review');

            // Hearings
            Route::post('/cases/{case}/hearings',  [CaseController::class, 'storeHearing'])->middleware('perm:cases.edit')->name('cases.hearings.store');
            Route::patch('/hearings/{hearing}',    [CaseController::class, 'updateHearing'])->middleware('perm:cases.edit')->name('cases.hearings.update');
            Route::delete('/hearings/{hearing}',   [CaseController::class, 'deleteHearing'])->middleware('perm:cases.edit')->name('cases.hearings.delete');

            // Files
            Route::post('/cases/{case}/files',          [CaseController::class, 'storeFile'])->middleware('perm:cases.edit')->name('cases.files.upload');
            Route::delete('/cases/{case}/files/{file}', [CaseController::class, 'deleteFile'])->middleware('perm:cases.edit')->name('cases.files.delete');

            // Witnesses
            Route::post('/cases/{case}/witnesses',            [CaseController::class, 'storeWitness'])->middleware('perm:cases.edit')->name('cases.witnesses.store');
            Route::patch('/cases/{case}/witnesses/{witness}', [CaseController::class, 'updateWitness'])->middleware('perm:cases.edit')->name('cases.witnesses.update');
            Route::delete('/cases/{case}/witnesses/{witness}', [CaseController::class, 'deleteWitness'])->middleware('perm:cases.edit')->name('cases.witnesses.delete');

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

            // Appeal documents
            Route::post('/appeals/{appeal}/documents',         [AppealController::class, 'uploadDoc'])->middleware('perm:appeals.edit')->name('appeals.docs.upload');
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
            Route::resource('letter-templates', LetterTemplateController::class)
                ->except('show')
                ->middleware('perm:templates.manage')
                ->names('letter-templates');

            Route::resource('terms', TermsAndConditionsController::class)
                ->except(['show'])
                ->middleware('perm:settings.manage')
                ->names('terms');

            Route::get('/letters/compose', [LetterComposerController::class, 'create'])
                ->middleware('perm:cases.edit')
                ->name('letters.compose');
            Route::get('/letters', [LetterController::class, 'index'])
                ->middleware('perm:cases.edit')
                ->name('letters.index');
            Route::post('/letters', [LetterController::class, 'store'])
                ->middleware('perm:cases.edit')
                ->name('letters.store');
            Route::get('/letters/{letter}', [LetterController::class, 'show'])
                ->middleware('perm:cases.edit')
                ->name('letters.show');
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
