<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('api-ip-hourly', function (Request $request) {
            return Limit::perHour(100)->by($request->ip() ?: 'unknown-ip');
        });

        try {
            $purifierCachePath = config('purifier.cachePath');
            if ($purifierCachePath) {
                File::ensureDirectoryExists($purifierCachePath, 0755, true);
                File::ensureDirectoryExists($purifierCachePath . DIRECTORY_SEPARATOR . 'CSS', 0755, true);
                File::ensureDirectoryExists($purifierCachePath . DIRECTORY_SEPARATOR . 'HTML', 0755, true);
                File::ensureDirectoryExists($purifierCachePath . DIRECTORY_SEPARATOR . 'URI', 0755, true);
            }
        } catch (\Throwable) {
            // Don't block the app if the directory can't be created
        }

        // ── Shared helper: one cache read for system settings ──────────────
        // All view composers below call this. The result is process-cached in
        // $resolvedSettings so within a single request it is fetched at most once.
        $resolvedSettings = null;
        $getSettings = function () use (&$resolvedSettings): mixed {
            if ($resolvedSettings !== null) {
                return $resolvedSettings;
            }
            try {
                if (Schema::hasTable('system_settings')) {
                    $resolvedSettings = Cache::remember(
                        'system_settings',
                        3600,
                        fn() => SystemSetting::query()->first()
                    );
                }
            } catch (\Throwable) {
                $resolvedSettings = null;
            }
            return $resolvedSettings;
        };

        // ── Admin layout ────────────────────────────────────────────────────
        View::composer('components.admin-layout', function ($view) use ($getSettings) {
            $settings = $getSettings() ?? (object) [
                'app_name'      => config('app.name', 'Laravel'),
                'short_name'    => 'CMS',
                'logo_path'     => null,
                'favicon_path'  => null,
                'contact_email' => null,
                'contact_phone' => null,
                'about'         => null,
            ];

            $view->with('systemSettings', $settings);
        });

        // ── Applicant layout ────────────────────────────────────────────────
        View::composer('components.applicant-layout', function ($view) use ($getSettings) {
            $systemSettings = $getSettings();

            $brandName  = $systemSettings?->app_name   ?? config('app.name', __('app.court_ms'));
            $shortName  = $systemSettings?->short_name ?: $brandName;
            $logoPath   = $systemSettings?->logo_path   ?? null;
            $bannerPath = $systemSettings?->banner_path ?? null;
            $footerText = $systemSettings?->footer_text ?? __('app.all_rights_reserved');

            $notificationCount = 0;
            $aid = auth('applicant')->id();

            if ($aid) {
                $notificationCount = Cache::remember("notif_count_{$aid}", 60, function () use ($aid) {
                    try {
                        $needed = ['court_cases', 'case_hearings', 'case_messages', 'case_status_logs', 'notification_reads'];
                        foreach ($needed as $tbl) {
                            if (!Schema::hasTable($tbl)) return 0;
                        }

                        // Single UNION query instead of 3 separate COUNT queries
                        $unseenHearings = DB::table('case_hearings as h')
                            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
                            ->where('c.applicant_id', $aid)
                            ->whereBetween('h.hearing_at', [now()->subDay(), now()->addDays(60)])
                            ->whereNotExists(fn($q) => $q->from('notification_reads as nr')
                                ->whereColumn('nr.source_id', 'h.id')
                                ->where('nr.type', 'hearing')
                                ->where('nr.applicant_id', $aid))
                            ->count();

                        $unseenMsgs = DB::table('case_messages as m')
                            ->join('court_cases as c', 'c.id', '=', 'm.case_id')
                            ->whereNotNull('m.sender_user_id')
                            ->where('c.applicant_id', $aid)
                            ->where('m.created_at', '>=', now()->subDays(14))
                            ->whereNotExists(fn($q) => $q->from('notification_reads as nr')
                                ->whereColumn('nr.source_id', 'm.id')
                                ->where('nr.type', 'message')
                                ->where('nr.applicant_id', $aid))
                            ->count();

                        $unseenStatus = DB::table('case_status_logs as l')
                            ->join('court_cases as c', 'c.id', '=', 'l.case_id')
                            ->where('c.applicant_id', $aid)
                            ->where('l.created_at', '>=', now()->subDays(14))
                            ->whereNotExists(fn($q) => $q->from('notification_reads as nr')
                                ->whereColumn('nr.source_id', 'l.id')
                                ->where('nr.type', 'status')
                                ->where('nr.applicant_id', $aid))
                            ->count();

                        return $unseenHearings + $unseenMsgs + $unseenStatus;
                    } catch (\Throwable) {
                        return 0;
                    }
                });
            }

            $view->with('publicLayout', compact(
                'systemSettings', 'brandName', 'shortName',
                'logoPath', 'bannerPath', 'footerText', 'notificationCount'
            ));
        });

        // ── Home / landing page ─────────────────────────────────────────────
        View::composer('home', function ($view) use ($getSettings) {
            $systemSettings = $getSettings();
            $brandName = $systemSettings?->app_name   ?? config('app.name', __('app.court_ms'));
            $shortName = $systemSettings?->short_name ?: $brandName;
            $logoPath  = $systemSettings?->logo_path  ?? null;

            $view->with('publicLayout', compact('systemSettings', 'brandName', 'shortName', 'logoPath'));
        });

        // ── Respondent layout ───────────────────────────────────────────────
        View::composer('components.respondant-layout', function ($view) use ($getSettings) {
            $systemSettings = $getSettings();
            $brandName  = $systemSettings?->app_name   ?? config('app.name', __('app.court_ms'));
            $shortName  = $systemSettings?->short_name ?: $brandName;
            $logoPath   = $systemSettings?->logo_path   ?? null;
            $bannerPath = $systemSettings?->banner_path ?? null;
            $footerText = $systemSettings?->footer_text ?? __('app.all_rights_reserved');

            $view->with('publicLayout', compact(
                'systemSettings', 'brandName', 'shortName',
                'logoPath', 'bannerPath', 'footerText'
            ));
        });
    }
}
