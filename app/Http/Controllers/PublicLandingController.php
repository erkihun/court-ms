<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomeFaq;
use App\Models\HomeResource;
use App\Models\HomeService;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use App\Models\HomeTimelineStep;
use App\Support\Public\LandingPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public landing page and resource list. CMS content is cached under the keys
 * Admin\LandingPageController clears when an editor saves.
 */
class PublicLandingController extends Controller
{
    private const CACHE_TTL = 3600;

    public function home(): Response
    {
        $total = DB::table('court_cases')->count();
        $resolved = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();
        $stats = [
            'total' => $total,
            'pending' => DB::table('court_cases')->where('status', 'pending')->count(),
            'resolved' => $resolved,
            'open' => max($total - $resolved, 0),
            'upcomingHearings' => DB::table('case_hearings')->whereBetween('hearing_at', [now(), now()->addDays(30)])->count(),
            'hearingsThisWeek' => DB::table('case_hearings')->whereBetween('hearing_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        $cms = [
            'slides' => $this->cachedModels('home_slides', 'home_slides', fn () => HomeSlide::active()->get()),
            'faqs' => $this->cachedModels('home_faqs', 'home_faqs', fn () => HomeFaq::active()->get()),
            'services' => $this->cachedModels('home_services', 'home_services', fn () => HomeService::active()->get()),
            'timeline' => $this->cachedModels('home_timeline', 'home_timeline_steps', fn () => HomeTimelineStep::active()->get()),
            'resources' => $this->activeResources(),
            'sections' => Cache::remember('home_settings', self::CACHE_TTL, function (): array {
                if (! Schema::hasTable('home_settings')) {
                    return [];
                }

                $out = [];
                foreach (['metrics', 'process', 'services', 'cases', 'resources', 'faq', 'cta'] as $section) {
                    $out[$section] = HomeSetting::getJson("section.{$section}");
                }

                return $out;
            }),
            'footer' => $this->cachedSetting('home_footer', 'footer.settings', null),
            'metrics' => $this->cachedSetting('home_metrics', 'metrics.content', []),
            'userManual' => $this->cachedSetting('home_user_manual', 'user_manual.settings', []),
        ];

        return Inertia::render('Public/Home', LandingPage::props($stats, $cms, app()->getLocale() === 'am'));
    }

    public function resources(): Response
    {
        return Inertia::render('Public/Resources', [
            'resources' => $this->activeResources()->map(fn (HomeResource $resource): array => LandingPage::resource($resource))->values(),
        ]);
    }

    /** @return Collection<int, HomeResource> */
    private function activeResources(): Collection
    {
        return $this->cachedModels('home_resources', 'home_resources', fn () => HomeResource::active()->get());
    }

    /**
     * @param  callable(): Collection<int, Model>  $query
     * @return Collection<int, mixed>
     */
    private function cachedModels(string $key, string $table, callable $query): Collection
    {
        return Cache::remember($key, self::CACHE_TTL, fn () => Schema::hasTable($table) ? $query() : collect());
    }

    private function cachedSetting(string $key, string $setting, ?array $default): ?array
    {
        return Cache::remember($key, self::CACHE_TTL, fn () => Schema::hasTable('home_settings') ? HomeSetting::getJson($setting) : $default);
    }
}
