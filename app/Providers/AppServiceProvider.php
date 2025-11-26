<?php

namespace App\Providers;

use App\Models\SystemSetting;
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
    }
}
