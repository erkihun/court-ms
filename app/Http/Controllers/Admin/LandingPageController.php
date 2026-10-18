<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFaq;
use App\Models\HomeResource;
use App\Models\HomeService;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use App\Models\HomeTimelineStep;
use App\Support\EthiopianDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    private const METRIC_KEYS = ['total_cases', 'resolved_cases', 'pending_cases', 'active_caseload', 'hearings_this_week', 'avg_resolution_time'];

    private const FOOTER_FIELDS = [
        'description', 'contact_phone', 'contact_email', 'contact_address',
        'social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube', 'social_telegram', 'social_instagram',
    ];

    /** @var array<string, array<string, string>>|null */
    private ?array $storedMetrics = null;

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function flushCache(): void
    {
        foreach (['home_slides', 'home_faqs', 'home_settings', 'home_metrics', 'home_services', 'home_timeline', 'home_resources', 'home_footer', 'home_user_manual'] as $key) {
            Cache::forget($key);
        }
    }

    private function defaultSections(): array
    {
        return [
            'metrics' => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'process' => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'services' => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'cases' => ['visible' => true],
            'resources' => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'faq' => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'cta' => [
                'visible' => true,
                'title' => '',
                'description' => '',
                'primary_label' => '',
                'primary_href' => '',
                'secondary_label' => '',
                'secondary_href' => '',
            ],
        ];
    }

    private function loadSections(): array
    {
        $sections = $this->defaultSections();
        foreach (array_keys($sections) as $key) {
            $stored = HomeSetting::getJson("section.{$key}");
            if ($stored) {
                $sections[$key] = array_merge($sections[$key], $stored);
            }
        }

        return $sections;
    }

    private function storeFile(Request $request, string $field, string $dir): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store("landing/{$dir}", 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(): Response
    {
        $metricCounts = $this->metricCounts();

        return Inertia::render('Admin/Landing/Index', [
            'tab' => (string) session('landing_tab', 'slides'),
            'previewUrl' => route('landing.home'),
            'slides' => HomeSlide::orderBy('sort_order')->get()->map(fn (HomeSlide $slide): array => [
                'id' => $slide->id,
                'badge' => (string) $slide->badge,
                'title' => (string) $slide->title,
                'description' => (string) $slide->description,
                'primaryLabel' => (string) $slide->primary_label,
                'primaryHref' => (string) $slide->primary_href,
                'secondaryLabel' => (string) $slide->secondary_label,
                'secondaryHref' => (string) $slide->secondary_href,
                'bgStyle' => (string) ($slide->bg_style ?: 'blue'),
                'bgImageUrl' => $this->publicUrl($slide->bg_image),
                'isActive' => (bool) $slide->is_active,
            ]),
            'steps' => HomeTimelineStep::orderBy('sort_order')->get()->map(fn (HomeTimelineStep $step): array => [
                'id' => $step->id,
                'title' => (string) $step->title,
                'description' => (string) $step->description,
                'meta' => (string) $step->meta,
                'duration' => (string) $step->duration,
                'color' => (string) ($step->color ?: 'blue'),
                'isActive' => (bool) $step->is_active,
            ]),
            'services' => HomeService::orderBy('sort_order')->get()->map(fn (HomeService $service): array => [
                'id' => $service->id,
                'title' => (string) $service->title,
                'description' => (string) $service->description,
                'meta' => (string) $service->meta,
                'features' => array_values((array) ($service->features ?? [])),
                'iconType' => (string) ($service->icon_type ?: 'document'),
                'accent' => (string) ($service->accent ?: 'blue'),
                'isActive' => (bool) $service->is_active,
            ]),
            'resources' => HomeResource::orderBy('sort_order')->get()->map(fn (HomeResource $resource): array => [
                'id' => $resource->id,
                'title' => (string) $resource->title,
                'type' => (string) $resource->type,
                'description' => (string) $resource->description,
                'externalUrl' => (string) $resource->external_url,
                'isFeatured' => (bool) $resource->is_featured,
                'publishedAt' => $resource->published_at?->format('Y-m-d\TH:i'),
                'scheduledFor' => $resource->published_at?->isFuture()
                    ? EthiopianDate::smartFormat($resource->published_at, false, '', 'h:i A', 'd M')
                    : null,
                'fileUrl' => $this->publicUrl($resource->file_path),
                'fileName' => $resource->file_path ? basename($resource->file_path) : null,
                'coverUrl' => $this->publicUrl($resource->cover_image),
                'isActive' => (bool) $resource->is_active,
            ]),
            'faqs' => HomeFaq::orderBy('sort_order')->get()->map(fn (HomeFaq $faq): array => [
                'id' => $faq->id,
                'question' => (string) $faq->question,
                'answer' => (string) $faq->answer,
                'isActive' => (bool) $faq->is_active,
            ]),
            'manual' => $this->manualValues(),
            'sections' => $this->loadSections(),
            'metrics' => collect(self::METRIC_KEYS)->map(fn (string $key): array => [
                'key' => $key,
                'value' => number_format($metricCounts[$key]),
                'label' => (string) ($this->storedMetrics()[$key]['label'] ?? ''),
                'description' => (string) ($this->storedMetrics()[$key]['description'] ?? ''),
                'placeholderLabel' => __("home.metrics.cards.{$key}.label", [], 'en'),
                'placeholderDescription' => __("home.metrics.cards.{$key}.description", [], 'en'),
            ]),
            'footer' => collect(self::FOOTER_FIELDS)
                ->mapWithKeys(fn (string $field): array => [$field => (string) (HomeSetting::getJson('footer.settings')[$field] ?? '')]),
        ]);
    }

    /** @return array<string, int> */
    private function metricCounts(): array
    {
        $totalCases = DB::table('court_cases')->count();
        $resolvedCases = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();

        return [
            'total_cases' => $totalCases,
            'resolved_cases' => $resolvedCases,
            'pending_cases' => DB::table('court_cases')->where('status', 'pending')->count(),
            'active_caseload' => max($totalCases - $resolvedCases, 0),
            'hearings_this_week' => DB::table('case_hearings')->whereBetween('hearing_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            // The public card shows upcoming hearings under this key.
            'avg_resolution_time' => DB::table('case_hearings')->where('hearing_at', '>=', now())->count(),
        ];
    }

    /** @return array<string, array<string, string>> */
    private function storedMetrics(): array
    {
        return $this->storedMetrics ??= (array) (HomeSetting::getJson('metrics.content') ?? []);
    }

    /** @return array{title_en: string, title_am: string, content_en: string, content_am: string, is_active: bool} */
    private function manualValues(): array
    {
        $manual = (array) (HomeSetting::getJson('user_manual.settings') ?? []);

        return [
            'title_en' => (string) ($manual['title_en'] ?? 'User Manual'),
            'title_am' => (string) ($manual['title_am'] ?? __('home.nav.user_manual', [], 'am')),
            'content_en' => (string) ($manual['content_en'] ?? ''),
            'content_am' => (string) ($manual['content_am'] ?? ''),
            'is_active' => (bool) ($manual['is_active'] ?? true),
        ];
    }

    private function publicUrl(?string $path): ?string
    {
        return $path ? asset('storage/'.$path) : null;
    }

    // ── Slides ────────────────────────────────────────────────────────────────

    public function storeSlide(Request $request)
    {
        $data = $request->validate([
            'badge' => 'nullable|string|max:120',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'primary_label' => 'required|string|max:120',
            'primary_href' => 'required|string|max:500',
            'secondary_label' => 'nullable|string|max:120',
            'secondary_href' => 'nullable|string|max:500',
            'bg_style' => 'required|in:blue,orange,emerald',
            'bg_image' => 'nullable|image|max:3072',
        ]);

        $data['bg_image'] = $this->storeFile($request, 'bg_image', 'slides');
        $data['sort_order'] = HomeSlide::max('sort_order') + 1;
        HomeSlide::create($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.slide_added'));
    }

    public function updateSlide(Request $request, HomeSlide $slide)
    {
        $data = $request->validate([
            'badge' => 'nullable|string|max:120',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'primary_label' => 'required|string|max:120',
            'primary_href' => 'required|string|max:500',
            'secondary_label' => 'nullable|string|max:120',
            'secondary_href' => 'nullable|string|max:500',
            'bg_style' => 'required|in:blue,orange,emerald',
            'bg_image' => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('bg_image')) {
            $this->deleteFile($slide->bg_image);
            $data['bg_image'] = $this->storeFile($request, 'bg_image', 'slides');
        } else {
            unset($data['bg_image']);
        }

        if ($request->boolean('remove_bg_image')) {
            $this->deleteFile($slide->bg_image);
            $data['bg_image'] = null;
        }

        $slide->update($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.slide_updated'));
    }

    public function destroySlide(HomeSlide $slide)
    {
        $this->deleteFile($slide->bg_image);
        $slide->delete();
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.slide_deleted'));
    }

    public function toggleSlide(HomeSlide $slide)
    {
        $slide->update(['is_active' => ! $slide->is_active]);
        $this->flushCache();

        return back();
    }

    public function reorderSlides(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($request->order as $position => $id) {
            HomeSlide::where('id', $id)->update(['sort_order' => $position]);
        }
        $this->flushCache();

        return response()->json(['ok' => true]);
    }

    // ── Timeline steps ────────────────────────────────────────────────────────

    public function storeStep(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:60',
            'color' => 'required|in:blue,orange,emerald,violet,amber',
        ]);

        $data['sort_order'] = HomeTimelineStep::max('sort_order') + 1;
        HomeTimelineStep::create($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.step_added'));
    }

    public function updateStep(Request $request, HomeTimelineStep $step)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:60',
            'color' => 'required|in:blue,orange,emerald,violet,amber',
        ]);

        $step->update($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.step_updated'));
    }

    public function destroyStep(HomeTimelineStep $step)
    {
        $step->delete();
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.step_deleted'));
    }

    public function toggleStep(HomeTimelineStep $step)
    {
        $step->update(['is_active' => ! $step->is_active]);
        $this->flushCache();

        return back();
    }

    // ── FAQs ──────────────────────────────────────────────────────────────────

    public function storeFaq(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:2000',
        ]);
        $data['sort_order'] = HomeFaq::max('sort_order') + 1;
        HomeFaq::create($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.faq_added'));
    }

    public function updateFaq(Request $request, HomeFaq $faq)
    {
        $faq->update($request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:2000',
        ]));
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.faq_updated'));
    }

    public function destroyFaq(HomeFaq $faq)
    {
        $faq->delete();
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.faq_deleted'));
    }

    public function toggleFaq(HomeFaq $faq)
    {
        $faq->update(['is_active' => ! $faq->is_active]);
        $this->flushCache();

        return back();
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta' => 'nullable|string|max:120',
            'features' => 'nullable|string',
            'icon_type' => 'required|in:document,calendar,lock,chart,database,chat',
            'accent' => 'required|in:blue,orange,emerald,violet,amber,pink',
        ]);
        $data['features'] = $this->parseLines($request->input('features', ''));
        $data['sort_order'] = HomeService::max('sort_order') + 1;
        HomeService::create($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.service_added'));
    }

    public function updateService(Request $request, HomeService $service)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta' => 'nullable|string|max:120',
            'features' => 'nullable|string',
            'icon_type' => 'required|in:document,calendar,lock,chart,database,chat',
            'accent' => 'required|in:blue,orange,emerald,violet,amber,pink',
        ]);
        $data['features'] = $this->parseLines($request->input('features', ''));
        $service->update($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.service_updated'));
    }

    public function destroyService(HomeService $service)
    {
        $service->delete();
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.service_deleted'));
    }

    public function toggleService(HomeService $service)
    {
        $service->update(['is_active' => ! $service->is_active]);
        $this->flushCache();

        return back();
    }

    // ── Resources ────────────────────────────────────────────────────────────

    public function storeResource(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:post,form,document,link',
            'description' => 'nullable|string|max:2000',
            'external_url' => 'nullable|url|max:500',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $data['file_path'] = $this->storeFile($request, 'file', 'resources');
        $data['cover_image'] = $this->storeFile($request, 'cover_image', 'covers');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = HomeResource::max('sort_order') + 1;
        HomeResource::create($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.resource_added'));
    }

    public function updateResource(Request $request, HomeResource $resource)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:post,form,document,link',
            'description' => 'nullable|string|max:2000',
            'external_url' => 'nullable|url|max:500',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $this->deleteFile($resource->file_path);
            $data['file_path'] = $this->storeFile($request, 'file', 'resources');
        } else {
            unset($data['file']);
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteFile($resource->cover_image);
            $data['cover_image'] = $this->storeFile($request, 'cover_image', 'covers');
        } else {
            unset($data['cover_image']);
        }

        if ($request->boolean('remove_file')) {
            $this->deleteFile($resource->file_path);
            $data['file_path'] = null;
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $resource->update($data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.resource_updated'));
    }

    public function destroyResource(HomeResource $resource)
    {
        $this->deleteFile($resource->file_path);
        $this->deleteFile($resource->cover_image);
        $resource->delete();
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.resource_deleted'));
    }

    public function toggleResource(HomeResource $resource)
    {
        $resource->update(['is_active' => ! $resource->is_active]);
        $this->flushCache();

        return back();
    }

    public function updateUserManual(Request $request)
    {
        $data = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_am' => 'required|string|max:255',
            'content_en' => 'required|string|max:50000',
            'content_am' => 'required|string|max:50000',
            'is_active' => 'boolean',
        ]);
        $data['content_en'] = $this->sanitizeManualHtml($data['content_en']);
        $data['content_am'] = $this->sanitizeManualHtml($data['content_am']);
        $data['is_active'] = $request->boolean('is_active');
        HomeSetting::setJson('user_manual.settings', $data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.manual.saved'))->with('landing_tab', 'manual');
    }

    // ── Footer settings ───────────────────────────────────────────────────────

    public function updateFooter(Request $request)
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:60',
            'contact_email' => 'nullable|email|max:120',
            'contact_address' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|url|max:300',
            'social_twitter' => 'nullable|url|max:300',
            'social_linkedin' => 'nullable|url|max:300',
            'social_youtube' => 'nullable|url|max:300',
            'social_telegram' => 'nullable|url|max:300',
            'social_instagram' => 'nullable|url|max:300',
        ]);

        HomeSetting::setJson('footer.settings', $data);
        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.footer_saved'));
    }

    // ── Metrics content ──────────────────────────────────────────────────────

    public function updateMetrics(Request $request)
    {
        $rules = [];
        foreach (self::METRIC_KEYS as $key) {
            $rules["{$key}.label"] = 'nullable|string|max:120';
            $rules["{$key}.description"] = 'nullable|string|max:255';
        }

        HomeSetting::setJson('metrics.content', $request->validate($rules));
        $this->flushCache();

        return back()
            ->with('success', __('admin_landing.flash.metrics_saved'))
            ->with('landing_tab', 'metrics');
    }

    // ── Section settings ──────────────────────────────────────────────────────

    public function updateSections(Request $request)
    {
        $validated = $request->validate([
            'metrics.visible' => 'boolean',
            'metrics.title' => 'nullable|string|max:255',
            'metrics.subtitle' => 'nullable|string|max:500',
            'process.visible' => 'boolean',
            'process.title' => 'nullable|string|max:255',
            'process.subtitle' => 'nullable|string|max:500',
            'services.visible' => 'boolean',
            'services.title' => 'nullable|string|max:255',
            'services.subtitle' => 'nullable|string|max:500',
            'cases.visible' => 'boolean',
            'resources.visible' => 'boolean',
            'resources.title' => 'nullable|string|max:255',
            'resources.subtitle' => 'nullable|string|max:500',
            'faq.visible' => 'boolean',
            'faq.title' => 'nullable|string|max:255',
            'faq.subtitle' => 'nullable|string|max:500',
            'cta.visible' => 'boolean',
            'cta.title' => 'nullable|string|max:255',
            'cta.description' => 'nullable|string|max:500',
            'cta.primary_label' => 'nullable|string|max:120',
            'cta.primary_href' => 'nullable|string|max:500',
            'cta.secondary_label' => 'nullable|string|max:120',
            'cta.secondary_href' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            $value['visible'] = (bool) ($value['visible'] ?? false);
            HomeSetting::setJson("section.{$key}", $value);
        }

        $this->flushCache();

        return back()->with('success', __('admin_landing.flash.sections_saved'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function parseLines(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", str_replace("\r", '', $raw)))
        ));
    }

    /** The manual is public HTML; HTMLPurifier's default profile keeps headings, lists, tables and inline styles. */
    private function sanitizeManualHtml(string $html): string
    {
        return trim((string) clean($html));
    }
}
