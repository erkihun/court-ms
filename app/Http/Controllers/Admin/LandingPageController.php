<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFaq;
use App\Models\HomeResource;
use App\Models\HomeService;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use App\Models\HomeTimelineStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LandingPageController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function flushCache(): void
    {
        foreach (['home_slides','home_faqs','home_settings','home_metrics','home_services','home_timeline','home_resources','home_footer','home_user_manual'] as $key) {
            Cache::forget($key);
        }
    }

    private function defaultSections(): array
    {
        return [
            'metrics'  => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'process'  => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'services' => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'cases'    => ['visible' => true],
            'resources'=> ['visible' => true, 'title' => '', 'subtitle' => ''],
            'faq'      => ['visible' => true, 'title' => '', 'subtitle' => ''],
            'cta'      => [
                'visible'         => true,
                'title'           => '',
                'description'     => '',
                'primary_label'   => '',
                'primary_href'    => '',
                'secondary_label' => '',
                'secondary_href'  => '',
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
        if (!$request->hasFile($field)) return null;
        return $request->file($field)->store("landing/{$dir}", 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path) Storage::disk('public')->delete($path);
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $slides    = HomeSlide::orderBy('sort_order')->get();
        $faqs      = HomeFaq::orderBy('sort_order')->get();
        $services  = HomeService::orderBy('sort_order')->get();
        $steps     = HomeTimelineStep::orderBy('sort_order')->get();
        $resources = HomeResource::orderBy('sort_order')->get();
        $userManual = HomeSetting::getJson('user_manual.settings');
        $sections  = $this->loadSections();
        $metrics   = HomeSetting::getJson('metrics.content');
        $footer    = HomeSetting::getJson('footer.settings');
        $totalCases = DB::table('court_cases')->count();
        $resolvedCases = DB::table('court_cases')->whereIn('status', ['closed', 'dismissed'])->count();
        $pendingCases = DB::table('court_cases')->where('status', 'pending')->count();
        $openCases = max($totalCases - $resolvedCases, 0);
        $upcomingHearings = DB::table('case_hearings')->where('hearing_at', '>=', now())->count();
        $hearingsThisWeek = DB::table('case_hearings')
            ->whereBetween('hearing_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $metricKeys = ['total_cases', 'resolved_cases', 'pending_cases', 'active_caseload', 'hearings_this_week', 'avg_resolution_time'];
        $footerFields = ['description', 'contact_phone', 'contact_email', 'contact_address', 'social_facebook', 'social_twitter', 'social_linkedin', 'social_youtube', 'social_telegram', 'social_instagram'];
        $tab = (string) session('landing_tab', 'slides');

        return Inertia::render('Court/Landing/Index', [
            'initialTab' => in_array($tab, ['slides', 'metrics', 'timeline', 'services', 'resources', 'manual', 'faqs', 'footer', 'sections'], true) ? $tab : 'slides',
            'homeUrl' => route('landing.home'),
            'slides' => $slides->map(fn (HomeSlide $slide): array => [
                'id' => $slide->id,
                'badge' => $slide->badge,
                'title' => (string) $slide->title,
                'description' => $slide->description,
                'primary_label' => (string) $slide->primary_label,
                'primary_href' => (string) $slide->primary_href,
                'secondary_label' => $slide->secondary_label,
                'secondary_href' => $slide->secondary_href,
                'bg_style' => $slide->bg_style ?: 'blue',
                'bg_image' => $slide->bg_image,
                'bg_image_url' => $slide->bg_image ? asset('storage/'.$slide->bg_image) : null,
                'is_active' => (bool) $slide->is_active,
            ])->values(),
            'steps' => $steps->map(fn (HomeTimelineStep $step): array => [
                'id' => $step->id,
                'title' => (string) $step->title,
                'description' => $step->description,
                'meta' => $step->meta,
                'duration' => $step->duration,
                'color' => $step->color ?: 'blue',
                'is_active' => (bool) $step->is_active,
            ])->values(),
            'services' => $services->map(fn (HomeService $service): array => [
                'id' => $service->id,
                'title' => (string) $service->title,
                'description' => $service->description,
                'meta' => $service->meta,
                'features' => array_values($service->features ?? []),
                'icon_type' => $service->icon_type ?: 'document',
                'accent' => $service->accent ?: 'blue',
                'is_active' => (bool) $service->is_active,
            ])->values(),
            'resources' => $resources->map(fn (HomeResource $resource): array => [
                'id' => $resource->id,
                'title' => (string) $resource->title,
                'type' => $resource->type,
                'description' => $resource->description,
                'external_url' => $resource->external_url,
                'is_featured' => (bool) $resource->is_featured,
                'is_active' => (bool) $resource->is_active,
                'published_at' => $resource->published_at?->toIso8601String(),
                'published_at_local' => $resource->published_at?->format('Y-m-d\TH:i'),
                'is_scheduled' => (bool) $resource->published_at?->isFuture(),
                'file_path' => $resource->file_path,
                'cover_image' => $resource->cover_image,
                'cover_image_url' => $resource->cover_image ? asset('storage/'.$resource->cover_image) : null,
            ])->values(),
            'faqs' => $faqs->map(fn (HomeFaq $faq): array => [
                'id' => $faq->id,
                'question' => (string) $faq->question,
                'answer' => (string) $faq->answer,
                'is_active' => (bool) $faq->is_active,
            ])->values(),
            'stats' => [
                'total_cases' => $totalCases,
                'resolved_cases' => $resolvedCases,
                'pending_cases' => $pendingCases,
                'active_caseload' => $openCases,
                'hearings_this_week' => $hearingsThisWeek,
                // The Blade card showed upcoming hearings under this key, as the home page does.
                'avg_resolution_time' => $upcomingHearings,
            ],
            'metrics' => collect($metricKeys)->mapWithKeys(fn (string $key): array => [$key => [
                'label' => (string) ($metrics[$key]['label'] ?? ''),
                'description' => (string) ($metrics[$key]['description'] ?? ''),
            ]])->all(),
            'metricDefaults' => collect($metricKeys)->mapWithKeys(fn (string $key): array => [$key => [
                'label' => __("home.metrics.cards.{$key}.label", [], 'en'),
                'description' => __("home.metrics.cards.{$key}.description", [], 'en'),
            ]])->all(),
            'sections' => $sections,
            'footer' => collect($footerFields)->mapWithKeys(fn (string $field): array => [$field => (string) ($footer[$field] ?? '')])->all(),
            'userManual' => [
                'title_en' => (string) ($userManual['title_en'] ?? 'User Manual'),
                'title_am' => (string) ($userManual['title_am'] ?? __('home.nav.user_manual', [], 'am')),
                'content_en' => (string) ($userManual['content_en'] ?? ''),
                'content_am' => (string) ($userManual['content_am'] ?? ''),
                'is_active' => (bool) ($userManual['is_active'] ?? true),
            ],
        ]);
    }

    // ── Slides ────────────────────────────────────────────────────────────────

    public function storeSlide(Request $request)
    {
        $data = $request->validate([
            'badge'           => 'nullable|string|max:120',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'primary_label'   => 'required|string|max:120',
            'primary_href'    => 'required|string|max:500',
            'secondary_label' => 'nullable|string|max:120',
            'secondary_href'  => 'nullable|string|max:500',
            'bg_style'        => 'required|in:blue,orange,emerald',
            'bg_image'        => 'nullable|image|max:3072',
        ]);

        $data['bg_image']    = $this->storeFile($request, 'bg_image', 'slides');
        $data['sort_order']  = HomeSlide::max('sort_order') + 1;
        HomeSlide::create($data);
        $this->flushCache();

        return back()->with('success', 'Slide added.');
    }

    public function updateSlide(Request $request, HomeSlide $slide)
    {
        $data = $request->validate([
            'badge'           => 'nullable|string|max:120',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'primary_label'   => 'required|string|max:120',
            'primary_href'    => 'required|string|max:500',
            'secondary_label' => 'nullable|string|max:120',
            'secondary_href'  => 'nullable|string|max:500',
            'bg_style'        => 'required|in:blue,orange,emerald',
            'bg_image'        => 'nullable|image|max:3072',
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

        return back()->with('success', 'Slide updated.');
    }

    public function destroySlide(HomeSlide $slide)
    {
        $this->deleteFile($slide->bg_image);
        $slide->delete();
        $this->flushCache();

        return back()->with('success', 'Slide deleted.');
    }

    public function toggleSlide(HomeSlide $slide)
    {
        $slide->update(['is_active' => !$slide->is_active]);
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
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta'        => 'nullable|string|max:120',
            'duration'    => 'nullable|string|max:60',
            'color'       => 'required|in:blue,orange,emerald,violet,amber',
        ]);

        $data['sort_order'] = HomeTimelineStep::max('sort_order') + 1;
        HomeTimelineStep::create($data);
        $this->flushCache();

        return back()->with('success', 'Step added.');
    }

    public function updateStep(Request $request, HomeTimelineStep $step)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta'        => 'nullable|string|max:120',
            'duration'    => 'nullable|string|max:60',
            'color'       => 'required|in:blue,orange,emerald,violet,amber',
        ]);

        $step->update($data);
        $this->flushCache();

        return back()->with('success', 'Step updated.');
    }

    public function destroyStep(HomeTimelineStep $step)
    {
        $step->delete();
        $this->flushCache();
        return back()->with('success', 'Step deleted.');
    }

    public function toggleStep(HomeTimelineStep $step)
    {
        $step->update(['is_active' => !$step->is_active]);
        $this->flushCache();
        return back();
    }

    // ── FAQs ──────────────────────────────────────────────────────────────────

    public function storeFaq(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string|max:2000',
        ]);
        $data['sort_order'] = HomeFaq::max('sort_order') + 1;
        HomeFaq::create($data);
        $this->flushCache();
        return back()->with('success', 'FAQ added.');
    }

    public function updateFaq(Request $request, HomeFaq $faq)
    {
        $faq->update($request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string|max:2000',
        ]));
        $this->flushCache();
        return back()->with('success', 'FAQ updated.');
    }

    public function destroyFaq(HomeFaq $faq)
    {
        $faq->delete();
        $this->flushCache();
        return back()->with('success', 'FAQ deleted.');
    }

    public function toggleFaq(HomeFaq $faq)
    {
        $faq->update(['is_active' => !$faq->is_active]);
        $this->flushCache();
        return back();
    }

    // ── Services ──────────────────────────────────────────────────────────────

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta'        => 'nullable|string|max:120',
            'features'    => 'nullable|string',
            'icon_type'   => 'required|in:document,calendar,lock,chart,database,chat',
            'accent'      => 'required|in:blue,orange,emerald,violet,amber,pink',
        ]);
        $data['features']   = $this->parseLines($request->input('features', ''));
        $data['sort_order'] = HomeService::max('sort_order') + 1;
        HomeService::create($data);
        $this->flushCache();
        return back()->with('success', 'Service card added.');
    }

    public function updateService(Request $request, HomeService $service)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'meta'        => 'nullable|string|max:120',
            'features'    => 'nullable|string',
            'icon_type'   => 'required|in:document,calendar,lock,chart,database,chat',
            'accent'      => 'required|in:blue,orange,emerald,violet,amber,pink',
        ]);
        $data['features'] = $this->parseLines($request->input('features', ''));
        $service->update($data);
        $this->flushCache();
        return back()->with('success', 'Service card updated.');
    }

    public function destroyService(HomeService $service)
    {
        $service->delete();
        $this->flushCache();
        return back()->with('success', 'Service deleted.');
    }

    public function toggleService(HomeService $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        $this->flushCache();
        return back();
    }

    // ── Resources ────────────────────────────────────────────────────────────

    public function storeResource(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:post,form,document,link',
            'description'  => 'nullable|string|max:2000',
            'external_url' => 'nullable|url|max:500',
            'is_featured'  => 'boolean',
            'published_at' => 'nullable|date',
            'file'         => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
            'cover_image'  => 'nullable|image|max:2048',
        ]);

        $data['file_path']    = $this->storeFile($request, 'file', 'resources');
        $data['cover_image']  = $this->storeFile($request, 'cover_image', 'covers');
        $data['is_featured']  = $request->boolean('is_featured');
        $data['sort_order']   = HomeResource::max('sort_order') + 1;
        HomeResource::create($data);
        $this->flushCache();

        return back()->with('success', 'Resource added.');
    }

    public function updateResource(Request $request, HomeResource $resource)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:post,form,document,link',
            'description'  => 'nullable|string|max:2000',
            'external_url' => 'nullable|url|max:500',
            'is_featured'  => 'boolean',
            'published_at' => 'nullable|date',
            'file'         => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
            'cover_image'  => 'nullable|image|max:2048',
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

        return back()->with('success', 'Resource updated.');
    }

    public function destroyResource(HomeResource $resource)
    {
        $this->deleteFile($resource->file_path);
        $this->deleteFile($resource->cover_image);
        $resource->delete();
        $this->flushCache();
        return back()->with('success', 'Resource deleted.');
    }

    public function toggleResource(HomeResource $resource)
    {
        $resource->update(['is_active' => !$resource->is_active]);
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

        return back()->with('success', __('admin_landing.manual.saved'))->with('landing_tab', 'resources');
    }

    // ── Footer settings ───────────────────────────────────────────────────────

    public function updateFooter(Request $request)
    {
        $data = $request->validate([
            'description'         => 'nullable|string|max:500',
            'contact_phone'       => 'nullable|string|max:60',
            'contact_email'       => 'nullable|email|max:120',
            'contact_address'     => 'nullable|string|max:255',
            'social_facebook'     => 'nullable|url|max:300',
            'social_twitter'      => 'nullable|url|max:300',
            'social_linkedin'     => 'nullable|url|max:300',
            'social_youtube'      => 'nullable|url|max:300',
            'social_telegram'     => 'nullable|url|max:300',
            'social_instagram'    => 'nullable|url|max:300',
        ]);

        HomeSetting::setJson('footer.settings', $data);
        $this->flushCache();

        return back()->with('success', 'Footer settings saved.');
    }

    // ── Metrics content ──────────────────────────────────────────────────────

    public function updateMetrics(Request $request)
    {
        $metricKeys = [
            'total_cases',
            'resolved_cases',
            'pending_cases',
            'active_caseload',
            'hearings_this_week',
            'avg_resolution_time',
        ];

        $rules = [];
        foreach ($metricKeys as $key) {
            $rules["{$key}.label"] = 'nullable|string|max:120';
            $rules["{$key}.description"] = 'nullable|string|max:255';
        }

        HomeSetting::setJson('metrics.content', $request->validate($rules));
        $this->flushCache();

        return back()
            ->with('success', 'Metrics content saved.')
            ->with('landing_tab', 'metrics');
    }

    // ── Section settings ──────────────────────────────────────────────────────

    public function updateSections(Request $request)
    {
        $request->validate([
            'metrics.visible'          => 'boolean',
            'metrics.title'            => 'nullable|string|max:255',
            'metrics.subtitle'         => 'nullable|string|max:500',
            'process.visible'          => 'boolean',
            'process.title'            => 'nullable|string|max:255',
            'process.subtitle'         => 'nullable|string|max:500',
            'services.visible'         => 'boolean',
            'services.title'           => 'nullable|string|max:255',
            'services.subtitle'        => 'nullable|string|max:500',
            'cases.visible'            => 'boolean',
            'resources.visible'        => 'boolean',
            'resources.title'          => 'nullable|string|max:255',
            'resources.subtitle'       => 'nullable|string|max:500',
            'faq.visible'              => 'boolean',
            'faq.title'                => 'nullable|string|max:255',
            'faq.subtitle'             => 'nullable|string|max:500',
            'cta.visible'              => 'boolean',
            'cta.title'                => 'nullable|string|max:255',
            'cta.description'          => 'nullable|string|max:500',
            'cta.primary_label'        => 'nullable|string|max:120',
            'cta.primary_href'         => 'nullable|string|max:500',
            'cta.secondary_label'      => 'nullable|string|max:120',
            'cta.secondary_href'       => 'nullable|string|max:500',
        ]);

        foreach ($request->only(['metrics','process','services','cases','resources','faq','cta']) as $key => $value) {
            $value['visible'] = (bool) ($value['visible'] ?? false);
            HomeSetting::setJson("section.{$key}", $value);
        }

        $this->flushCache();
        return back()->with('success', 'Settings saved.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function parseLines(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", str_replace("\r", '', $raw)))
        ));
    }

    private function sanitizeManualHtml(string $html): string
    {
        $clean = strip_tags($html, '<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><sub><sup><pre><code><ul><ol><li><blockquote><a><table><caption><colgroup><col><thead><tbody><tfoot><tr><th><td><hr><div><span><figure><figcaption>');
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $clean) ?? '';
        $clean = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/iu', '$1="#"', $clean) ?? '';

        return trim($clean);
    }
}
