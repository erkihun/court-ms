<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share system settings with the admin layout component
        View::composer('components.admin-layout', function ($view) {
            $default = (object) [
                'app_name'      => config('app.name', 'Laravel'),
                'short_name'    => 'CMS',
                'logo_path'     => null,
                'favicon_path'  => null,
                'contact_email' => null,
                'contact_phone' => null,
                'about'         => null,
            ];

            $settings = $default;

            try {
                if (Schema::hasTable('system_settings')) {
                    $found = SystemSetting::query()->first();
                    if ($found) {
                        $settings = $found;
                    }
                }
            } catch (\Throwable $e) {
                // if something goes wrong (e.g. during migrate), just use defaults
            }

            $view->with('systemSettings', $settings);
        });

        View::composer('components.public-layout', function ($view) {
            $systemSettings = null;
            try {
                if (Schema::hasTable('system_settings')) {
                    $systemSettings = SystemSetting::query()->first();
                }
            } catch (\Throwable $e) {
                $systemSettings = null;
            }

            $brandName = $systemSettings?->app_name ?? config('app.name', __('app.court_ms'));
            $shortName = $systemSettings?->short_name ?: $brandName;
            $logoPath = $systemSettings?->logo_path ?? null;
            $bannerPath = $systemSettings?->banner_path ?? null;
            $footerText = $systemSettings?->footer_text ?? __('app.all_rights_reserved');

            $notificationCount = 0;
            $aid = auth('applicant')->id();
            if ($aid) {
                try {
                    $has = fn($table) => Schema::hasTable($table);
                    if ($has('court_cases') && $has('case_hearings') && $has('case_messages') && $has('case_status_logs') && $has('notification_reads')) {
                        $unseenHearings = DB::table('case_hearings as h')
                            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
                            ->where('c.applicant_id', $aid)
                            ->whereBetween('h.hearing_at', [now()->subDay(), now()->addDays(60)])
                            ->whereNotExists(function ($q) use ($aid) {
                                $q->from('notification_reads as nr')
                                    ->whereColumn('nr.source_id', 'h.id')
                                    ->where('nr.type', 'hearing')
                                    ->where('nr.applicant_id', $aid);
                            })
                            ->count();

                        $unseenMsgs = DB::table('case_messages as m')
                            ->join('court_cases as c', 'c.id', '=', 'm.case_id')
                            ->whereNotNull('m.sender_user_id')
                            ->where('c.applicant_id', $aid)
                            ->where('m.created_at', '>=', now()->subDays(14))
                            ->whereNotExists(function ($q) use ($aid) {
                                $q->from('notification_reads as nr')
                                    ->whereColumn('nr.source_id', 'm.id')
                                    ->where('nr.type', 'message')
                                    ->where('nr.applicant_id', $aid);
                            })
                            ->count();

                        $unseenStatus = DB::table('case_status_logs as l')
                            ->join('court_cases as c', 'c.id', '=', 'l.case_id')
                            ->where('c.applicant_id', $aid)
                            ->where('l.created_at', '>=', now()->subDays(14))
                            ->whereNotExists(function ($q) use ($aid) {
                                $q->from('notification_reads as nr')
                                    ->whereColumn('nr.source_id', 'l.id')
                                    ->where('nr.type', 'status')
                                    ->where('nr.applicant_id', $aid);
                            })
                            ->count();

                        $notificationCount = $unseenHearings + $unseenMsgs + $unseenStatus;
                    }
                } catch (\Throwable $e) {
                    $notificationCount = 0;
                }
            }

            $view->with('publicLayout', [
                'systemSettings' => $systemSettings,
                'brandName' => $brandName,
                'shortName' => $shortName,
                'logoPath' => $logoPath,
                'bannerPath' => $bannerPath,
                'footerText' => $footerText,
                'notificationCount' => $notificationCount,
            ]);
        });

        // Share branding with respondent layout (no notifications needed)
        View::composer('components.respondant-layout', function ($view) {
            $systemSettings = null;
            try {
                if (Schema::hasTable('system_settings')) {
                    $systemSettings = SystemSetting::query()->first();
                }
            } catch (\Throwable $e) {
                $systemSettings = null;
            }

            $brandName = $systemSettings?->app_name ?? config('app.name', __('app.court_ms'));
            $shortName = $systemSettings?->short_name ?: $brandName;
            $logoPath = $systemSettings?->logo_path ?? null;
            $bannerPath = $systemSettings?->banner_path ?? null;
            $footerText = $systemSettings?->footer_text ?? __('app.all_rights_reserved');

            $view->with('publicLayout', [
                'systemSettings' => $systemSettings,
                'brandName' => $brandName,
                'shortName' => $shortName,
                'logoPath' => $logoPath,
                'bannerPath' => $bannerPath,
                'footerText' => $footerText,
            ]);
        });
    }
}
