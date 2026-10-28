<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\HomeFaq;
use App\Models\HomeResource;
use App\Models\HomeService;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use App\Models\HomeTimelineStep;
use App\Support\Ui\PublicSiteChrome;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The public home page (Court/Public/Home) and the resources page
 * (Court/Public/Resources). Their content is the landing-page manager's,
 * read through the `home_*` cache keys LandingPageController flushes on
 * every save. The manager's fields are not locale-aware, so in Amharic the
 * home page shows the reviewed translations (lang/am/home.php) instead of
 * English editorial text, as the Blade page did; resources show in both.
 * A hidden section sends no items.
 */
class PublicHomeController extends Controller
{
    private const SLIDE_STYLES = ['blue', 'orange', 'emerald'];

    private const STEP_COLORS = ['blue', 'orange', 'emerald', 'violet', 'amber'];

    private const SERVICE_ICONS = ['document', 'calendar', 'lock', 'chart', 'database', 'chat'];

    private const SERVICE_ACCENTS = ['blue', 'orange', 'emerald', 'violet', 'amber', 'pink'];

    /** The built-in timeline (home.timeline.steps.*) and the colour of each step. */
    private const DEFAULT_STEPS = [
        'case_initiation' => 'blue',
        'case_assignment' => 'orange',
        'evidence_submission' => 'blue',
        'adjudication' => 'orange',
        'implementation' => 'emerald',
    ];

    /** The 6 built-in service cards (home.services.cards.*): icon and accent. */
    private const DEFAULT_SERVICES = [
        'digital_case_filing' => ['document', 'blue'],
        'hearing_management' => ['calendar', 'orange'],
        'evidence_repository' => ['lock', 'emerald'],
        'case_tracking_portal' => ['chart', 'violet'],
        'decision_database' => ['database', 'amber'],
        'online_dispute_resolution' => ['chat', 'pink'],
    ];

    /** Resources the home page lists before "View all resources". */
    private const HOME_RESOURCE_LIMIT = 6;

    public function home(): Response
    {
        $totalCases = DB::table('court_cases')->count();
        $pendingCases = DB::table('court_cases')->where('status', 'pending')->count();
        $resolvedCases = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();
        $openCases = max($totalCases - $resolvedCases, 0);
        $upcomingHearings = DB::table('case_hearings')
            ->whereBetween('hearing_at', [now(), now()->addDays(30)])
            ->count();
        $hearingsThisWeek = DB::table('case_hearings')
            ->whereBetween('hearing_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $dbSlides = Cache::remember('home_slides', 3600,
            fn () => Schema::hasTable('home_slides') ? HomeSlide::active()->get() : collect()
        );
        $dbFaqs = Cache::remember('home_faqs', 3600,
            fn () => Schema::hasTable('home_faqs') ? HomeFaq::active()->get() : collect()
        );
        $dbServices = Cache::remember('home_services', 3600,
            fn () => Schema::hasTable('home_services') ? HomeService::active()->get() : collect()
        );
        $dbTimeline = Cache::remember('home_timeline', 3600,
            fn () => Schema::hasTable('home_timeline_steps') ? HomeTimelineStep::active()->get() : collect()
        );
        $dbResources = $this->activeResources();
        $dbSections = Cache::remember('home_settings', 3600, function () {
            if (! Schema::hasTable('home_settings')) {
                return [];
            }
            $keys = ['metrics', 'process', 'services', 'cases', 'resources', 'faq', 'cta'];
            $out = [];
            foreach ($keys as $k) {
                $out[$k] = HomeSetting::getJson("section.{$k}");
            }

            return $out;
        });
        $dbMetrics = Cache::remember('home_metrics', 3600,
            fn () => Schema::hasTable('home_settings') ? HomeSetting::getJson('metrics.content') : []
        );
        $dbMetrics = is_array($dbMetrics) ? $dbMetrics : [];
        $isAmharic = app()->getLocale() === 'am';

        // A section never saved is shown; a saved one follows its `visible` flag.
        $visible = fn (string $section): bool => ! isset($dbSections[$section]) || (bool) ($dbSections[$section]['visible'] ?? true);
        $text = function (string $section, string $key, string $fallback) use ($dbSections, $isAmharic): string {
            $value = $dbSections[$section][$key] ?? null;

            return ! $isAmharic && ! empty($value) ? (string) $value : $fallback;
        };
        $section = fn (string $key, string $prefix): array => [
            'visible' => $visible($key),
            'title' => $text($key, 'title', (string) __("home.{$prefix}.section_title")),
            'subtitle' => $text($key, 'subtitle', (string) __("home.{$prefix}.section_description")),
        ];

        // The last card has always counted the hearings of the next 30 days under this key.
        $figures = [
            'total_cases' => $totalCases,
            'resolved_cases' => $resolvedCases,
            'pending_cases' => $pendingCases,
            'active_caseload' => $openCases,
            'hearings_this_week' => $hearingsThisWeek,
            'avg_resolution_time' => $upcomingHearings,
        ];
        $override = fn (string $key, string $field): string => ! $isAmharic && ! empty($dbMetrics[$key][$field])
            ? (string) $dbMetrics[$key][$field]
            : (string) __("home.metrics.cards.{$key}.{$field}");

        $sections = [
            'metrics' => $section('metrics', 'metrics'),
            'process' => $section('process', 'timeline'),
            'services' => $section('services', 'services'),
            'resources' => $section('resources', 'resources'),
            'faq' => $section('faq', 'faq'),
        ];

        return Inertia::render('Court/Public/Home', [
            'slides' => ! $isAmharic && $dbSlides->isNotEmpty()
                ? $dbSlides->map(fn (HomeSlide $slide): array => $this->slide($slide))->values()->all()
                : $this->defaultSlides(),
            'sections' => $sections,
            'metrics' => $sections['metrics']['visible']
                ? collect($figures)->map(fn (int $value, string $key): array => [
                    'key' => $key,
                    'label' => $override($key, 'label'),
                    'description' => $override($key, 'description'),
                    'value' => $value,
                ])->values()->all()
                : [],
            'steps' => $sections['process']['visible'] ? $this->steps($isAmharic ? collect() : $dbTimeline) : [],
            'services' => $sections['services']['visible'] ? $this->services($isAmharic ? collect() : $dbServices) : [],
            'resources' => $sections['resources']['visible']
                ? $dbResources->take(self::HOME_RESOURCE_LIMIT)->map(fn (HomeResource $resource): array => $this->resource($resource))->values()->all()
                : [],
            'hasMoreResources' => $sections['resources']['visible'] && $dbResources->count() > self::HOME_RESOURCE_LIMIT,
            'faqs' => $sections['faq']['visible'] ? $this->faqs($isAmharic ? collect() : $dbFaqs) : [],
            'publicSite' => PublicSiteChrome::props(),
        ]);
    }

    public function resources(): Response
    {
        return Inertia::render('Court/Public/Resources', [
            'resources' => $this->activeResources()->map(fn (HomeResource $resource): array => $this->resource($resource))->values()->all(),
            'publicSite' => PublicSiteChrome::props(),
        ]);
    }

    /** Active, already published resources, shared by both pages under one cache key. */
    private function activeResources(): Collection
    {
        return Cache::remember('home_resources', 3600,
            fn () => Schema::hasTable('home_resources') ? HomeResource::active()->get() : collect()
        );
    }

    /** @return array<string, string|null> */
    private function slide(HomeSlide $slide): array
    {
        return [
            'badge' => (string) ($slide->badge ?? ''),
            'title' => (string) $slide->title,
            'description' => (string) ($slide->description ?? ''),
            'primary_label' => (string) $slide->primary_label,
            'primary_href' => $slide->primary_href ?: '#',
            'secondary_label' => (string) ($slide->secondary_label ?? ''),
            'secondary_href' => $slide->secondary_href ?: '#',
            'style' => in_array($slide->bg_style, self::SLIDE_STYLES, true) ? $slide->bg_style : 'blue',
            'image_url' => $slide->bg_image ? asset('storage/'.$slide->bg_image) : null,
        ];
    }

    /** The three built-in slides (home.hero.slides.*) shown while none is saved, and always in Amharic. */
    private function defaultSlides(): array
    {
        $defaults = [
            'digital' => ['blue', route('applicant.register'), route('applicant.login')],
            'hearing' => ['orange', route('applicant.login'), route('public.terms')],
            'analytics' => ['emerald', '#statistics', route('applicant.login')],
        ];

        return collect($defaults)->map(fn (array $slide, string $key): array => [
            'badge' => (string) __("home.hero.slides.{$key}.badge"),
            'title' => (string) __("home.hero.slides.{$key}.title"),
            'description' => (string) __("home.hero.slides.{$key}.description"),
            'primary_label' => (string) __("home.hero.slides.{$key}.cta_label"),
            'primary_href' => $slide[1],
            'secondary_label' => (string) __("home.hero.slides.{$key}.secondary_label"),
            'secondary_href' => $slide[2],
            'style' => $slide[0],
            'image_url' => null,
        ])->values()->all();
    }

    private function steps(Collection $saved): array
    {
        if ($saved->isNotEmpty()) {
            return $saved->map(fn (HomeTimelineStep $step): array => [
                'title' => (string) $step->title,
                'description' => (string) ($step->description ?? ''),
                'meta' => (string) ($step->meta ?? ''),
                'duration' => (string) ($step->duration ?? ''),
                'color' => in_array($step->color, self::STEP_COLORS, true) ? $step->color : 'blue',
            ])->values()->all();
        }

        return collect(self::DEFAULT_STEPS)->map(fn (string $color, string $key): array => [
            'title' => (string) __("home.timeline.steps.{$key}.title"),
            'description' => (string) __("home.timeline.steps.{$key}.description"),
            'meta' => (string) __("home.timeline.steps.{$key}.meta"),
            'duration' => (string) __("home.timeline.steps.{$key}.duration"),
            'color' => $color,
        ])->values()->all();
    }

    private function services(Collection $saved): array
    {
        if ($saved->isNotEmpty()) {
            return $saved->map(fn (HomeService $service): array => [
                'title' => (string) $service->title,
                'description' => (string) ($service->description ?? ''),
                'meta' => (string) ($service->meta ?? ''),
                'features' => $this->strings($service->features ?? []),
                'icon' => in_array($service->icon_type, self::SERVICE_ICONS, true) ? $service->icon_type : 'document',
                'accent' => in_array($service->accent, self::SERVICE_ACCENTS, true) ? $service->accent : 'blue',
            ])->values()->all();
        }

        return collect(self::DEFAULT_SERVICES)->map(fn (array $card, string $key): array => [
            'title' => (string) __("home.services.cards.{$key}.title"),
            'description' => (string) __("home.services.cards.{$key}.description"),
            'meta' => (string) __("home.services.cards.{$key}.meta"),
            'features' => $this->strings((array) __("home.services.cards.{$key}.features")),
            'icon' => $card[0],
            'accent' => $card[1],
        ])->values()->all();
    }

    /** @return array<int, array{question: string, answer: string}> */
    private function faqs(Collection $saved): array
    {
        $items = $saved->isNotEmpty()
            ? $saved->map(fn (HomeFaq $faq): array => ['question' => $faq->question, 'answer' => $faq->answer])->all()
            : (array) __('home.faq.questions');

        return array_values(array_map(fn ($faq): array => [
            'question' => (string) ($faq['question'] ?? ''),
            'answer' => (string) ($faq['answer'] ?? ''),
        ], $items));
    }

    /** @return array<string, mixed> */
    private function resource(HomeResource $resource): array
    {
        return [
            'id' => $resource->id,
            'title' => (string) $resource->title,
            'type' => $resource->type,
            'description' => $resource->description ?: null,
            'is_featured' => (bool) $resource->is_featured,
            'published_at' => $resource->published_at?->toIso8601String(),
            'download_url' => $resource->isDownloadable() ? asset('storage/'.$resource->file_path) : null,
            'external_url' => $resource->external_url ?: null,
            'cover_image_url' => $resource->cover_image ? asset('storage/'.$resource->cover_image) : null,
        ];
    }

    /** @return list<string> */
    private function strings(array $values): array
    {
        return array_values(array_filter(
            array_map(fn ($value): string => is_scalar($value) ? trim((string) $value) : '', $values),
            fn (string $value): bool => $value !== '',
        ));
    }
}
