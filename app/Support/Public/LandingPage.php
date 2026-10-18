<?php

declare(strict_types=1);

namespace App\Support\Public;

use App\Models\HomeResource;
use App\Support\EthiopianDate;
use Illuminate\Support\Collection;

/**
 * Props for the public landing page (Public/Home).
 *
 * The CMS fields are not locale-aware, so editorial content is only used for
 * English; Amharic always uses the reviewed translations.
 */
final class LandingPage
{
    private const SLIDE_STYLES = ['blue', 'orange', 'emerald'];

    private const SECTIONS = ['metrics', 'process', 'services', 'resources', 'faq'];

    private const METRICS = ['total_cases', 'resolved_cases', 'pending_cases', 'active_caseload', 'hearings_this_week', 'avg_resolution_time'];

    private const TIMELINE_KEYS = ['case_initiation', 'case_assignment', 'evidence_submission', 'adjudication', 'implementation'];

    /** Default service cards: translation key => icon. */
    private const SERVICE_CARDS = [
        'digital_case_filing' => ['document', 'blue'],
        'hearing_management' => ['calendar', 'orange'],
        'evidence_repository' => ['lock', 'emerald'],
        'case_tracking_portal' => ['chart', 'violet'],
        'decision_database' => ['database', 'amber'],
        'online_dispute_resolution' => ['chat', 'pink'],
    ];

    private const ICONS = ['document', 'calendar', 'lock', 'chart', 'database', 'chat'];

    private const ACCENTS = ['blue', 'orange', 'emerald', 'violet', 'amber', 'pink'];

    private const SOCIAL = ['facebook', 'twitter', 'linkedin', 'youtube', 'telegram', 'instagram'];

    /**
     * @param  array{total: int, pending: int, resolved: int, open: int, upcomingHearings: int, hearingsThisWeek: int}  $stats
     * @param  array<string, mixed>  $cms  cached CMS content (models and decoded settings)
     * @return array<string, mixed>
     */
    public static function props(array $stats, array $cms, bool $amharic): array
    {
        $sections = is_array($cms['sections'] ?? null) ? $cms['sections'] : [];
        $footer = is_array($cms['footer'] ?? null) ? $cms['footer'] : [];
        $metricsText = is_array($cms['metrics'] ?? null) ? $cms['metrics'] : [];
        $manual = is_array($cms['userManual'] ?? null) ? $cms['userManual'] : [];
        $editorial = fn (?string $value): ?string => ! $amharic && filled($value) ? (string) $value : null;

        /** @var Collection<int, object> $slides */
        $slides = $cms['slides'] ?? collect();
        /** @var Collection<int, object> $faqs */
        $faqs = $cms['faqs'] ?? collect();
        /** @var Collection<int, object> $services */
        $services = $cms['services'] ?? collect();
        /** @var Collection<int, object> $timeline */
        $timeline = $cms['timeline'] ?? collect();
        /** @var Collection<int, HomeResource> $resources */
        $resources = $cms['resources'] ?? collect();

        $values = [
            'total_cases' => $stats['total'],
            'resolved_cases' => $stats['resolved'],
            'pending_cases' => $stats['pending'],
            'active_caseload' => $stats['open'],
            'hearings_this_week' => $stats['hearingsThisWeek'],
            'avg_resolution_time' => $stats['upcomingHearings'],
        ];

        $sectionProps = [];
        foreach (self::SECTIONS as $section) {
            $stored = is_array($sections[$section] ?? null) ? $sections[$section] : null;
            $translationGroup = $section === 'process' ? 'timeline' : $section;
            $sectionProps[$section] = [
                'visible' => $stored === null || (bool) ($stored['visible'] ?? true),
                'title' => $editorial($stored['title'] ?? null) ?? __("home.{$translationGroup}.section_title"),
                'subtitle' => $editorial($stored['subtitle'] ?? null) ?? __("home.{$translationGroup}.section_description"),
            ];
        }

        return [
            'userManual' => ! empty($manual['is_active']) && (filled($manual['content_en'] ?? null) || filled($manual['content_am'] ?? null)) ? [
                'url' => route('landing.user-manual'),
                'label' => (string) (($amharic ? ($manual['title_am'] ?? null) : ($manual['title_en'] ?? null)) ?: __('home.nav.user_manual')),
            ] : null,
            'slides' => ! $amharic && $slides->isNotEmpty()
                ? $slides->map(fn (object $slide): array => [
                    'badge' => (string) ($slide->badge ?? ''),
                    'title' => (string) $slide->title,
                    'description' => (string) ($slide->description ?? ''),
                    'primary' => ['label' => (string) $slide->primary_label, 'href' => self::safeUrl($slide->primary_href) ?? '#'],
                    'secondary' => filled($slide->secondary_label) ? ['label' => (string) $slide->secondary_label, 'href' => self::safeUrl($slide->secondary_href) ?? '#'] : null,
                    'style' => in_array($slide->bg_style, self::SLIDE_STYLES, true) ? $slide->bg_style : 'blue',
                    'imageUrl' => $slide->bg_image ? asset('storage/'.$slide->bg_image) : null,
                ])->values()->all()
                : self::defaultSlides(),
            'sections' => $sectionProps,
            'metrics' => array_map(fn (string $key): array => [
                'key' => $key,
                'label' => $editorial($metricsText[$key]['label'] ?? null) ?? __("home.metrics.cards.{$key}.label"),
                'description' => $editorial($metricsText[$key]['description'] ?? null) ?? __("home.metrics.cards.{$key}.description"),
                'value' => (int) $values[$key],
            ], self::METRICS),
            'timeline' => ! $amharic && $timeline->isNotEmpty()
                ? $timeline->map(fn (object $step): array => [
                    'title' => (string) $step->title,
                    'description' => (string) ($step->description ?? ''),
                    'meta' => $step->meta ?: null,
                    'duration' => $step->duration ?: null,
                ])->values()->all()
                : array_map(fn (string $key): array => [
                    'title' => __("home.timeline.steps.{$key}.title"),
                    'description' => __("home.timeline.steps.{$key}.description"),
                    'meta' => __("home.timeline.steps.{$key}.meta") ?: null,
                    'duration' => __("home.timeline.steps.{$key}.duration") ?: null,
                ], self::TIMELINE_KEYS),
            'services' => ! $amharic && $services->isNotEmpty()
                ? $services->map(fn (object $service): array => [
                    'title' => (string) $service->title,
                    'description' => (string) ($service->description ?? ''),
                    'meta' => $service->meta ?: null,
                    'features' => array_values(array_filter(array_map('strval', (array) ($service->features ?? [])))),
                    'icon' => in_array($service->icon_type, self::ICONS, true) ? $service->icon_type : 'document',
                    'accent' => in_array($service->accent, self::ACCENTS, true) ? $service->accent : 'blue',
                ])->values()->all()
                : array_map(fn (string $key, array $look): array => [
                    'title' => __("home.services.cards.{$key}.title"),
                    'description' => __("home.services.cards.{$key}.description"),
                    'meta' => __("home.services.cards.{$key}.meta") ?: null,
                    'features' => array_values((array) __("home.services.cards.{$key}.features")),
                    'icon' => $look[0],
                    'accent' => $look[1],
                ], array_keys(self::SERVICE_CARDS), self::SERVICE_CARDS),
            'resources' => [
                'items' => $resources->take(6)->map(fn (HomeResource $resource): array => self::resource($resource))->values()->all(),
                'hasMore' => $resources->count() > 6,
            ],
            'faqs' => ! $amharic && $faqs->isNotEmpty()
                ? $faqs->map(fn (object $faq): array => ['question' => (string) $faq->question, 'answer' => (string) $faq->answer])->values()->all()
                : array_values(array_filter(
                    array_map(fn ($item): ?array => is_array($item) ? ['question' => (string) ($item['question'] ?? ''), 'answer' => (string) ($item['answer'] ?? '')] : null, (array) __('home.faq.questions')),
                )),
            'footer' => [
                'description' => $editorial($footer['description'] ?? null) ?? __('home.footer.description'),
                'phone' => filled($footer['contact_phone'] ?? null) ? (string) $footer['contact_phone'] : null,
                'email' => filled($footer['contact_email'] ?? null) ? (string) $footer['contact_email'] : null,
                'address' => filled($footer['contact_address'] ?? null) ? (string) $footer['contact_address'] : null,
                'social' => collect(self::SOCIAL)
                    ->map(fn (string $network): ?array => ($url = self::safeUrl($footer["social_{$network}"] ?? null, allowRelative: false)) ? ['network' => $network, 'url' => $url] : null)
                    ->filter()
                    ->values()
                    ->all(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function resource(HomeResource $resource): array
    {
        return [
            'id' => (int) $resource->id,
            'type' => in_array($resource->type, ['post', 'form', 'document', 'link'], true) ? $resource->type : 'post',
            'title' => (string) $resource->title,
            'description' => $resource->description ?: null,
            'imageUrl' => $resource->cover_image ? asset('storage/'.$resource->cover_image) : null,
            'featured' => (bool) $resource->is_featured,
            'published' => $resource->published_at ? EthiopianDate::smartFormat($resource->published_at, false, '') : null,
            'downloadUrl' => $resource->isDownloadable() ? asset('storage/'.$resource->file_path) : null,
            'externalUrl' => self::safeUrl($resource->external_url, allowRelative: false),
        ];
    }

    /**
     * Editor-supplied links: only http(s), in-page anchors and site paths, so a
     * stored "javascript:" link can never reach the page.
     */
    public static function safeUrl(?string $url, bool $allowRelative = true): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if ($allowRelative && (str_starts_with($url, '#') || (str_starts_with($url, '/') && ! str_starts_with($url, '//')))) {
            return $url;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) ? $url : null;
    }

    /** @return list<array<string, mixed>> */
    private static function defaultSlides(): array
    {
        $slide = fn (string $key, string $style, string $primary, string $secondary): array => [
            'badge' => __("home.hero.slides.{$key}.badge"),
            'title' => __("home.hero.slides.{$key}.title"),
            'description' => __("home.hero.slides.{$key}.description"),
            'primary' => ['label' => __("home.hero.slides.{$key}.cta_label"), 'href' => $primary],
            'secondary' => ['label' => __("home.hero.slides.{$key}.secondary_label"), 'href' => $secondary],
            'style' => $style,
            'imageUrl' => null,
        ];

        return [
            $slide('digital', 'blue', route('applicant.register'), route('applicant.login')),
            $slide('hearing', 'orange', route('applicant.login'), route('public.terms')),
            $slide('analytics', 'emerald', '#statistics', route('applicant.login')),
        ];
    }
}
