<?php

declare(strict_types=1);

use App\Models\HomeFaq;
use App\Models\HomeResource;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use App\Models\TermsAndCondition;
use Inertia\Testing\AssertableInertia as Assert;

test('guests get the landing page with live statistics and the default content', function (): void {
    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->has('slides', 3)
            ->where('slides.0.primary.href', route('applicant.register'))
            ->has('metrics', 6)
            ->where('metrics.0.key', 'total_cases')
            ->where('metrics.0.value', 0)
            ->where('sections.faq.visible', true)
            ->has('faqs')
            ->where('userManual', null)
            ->where('auth.user', null)
            ->where('footer.description', __('home.footer.description', [], 'en')));
});

test('editorial content is shown in English and the reviewed translations in Amharic', function (): void {
    HomeSlide::query()->create([
        'sort_order' => 1,
        'is_active' => true,
        'title' => 'Editor slide',
        'primary_label' => 'Start',
        'primary_href' => '/applicant/login',
        'bg_style' => 'emerald',
    ]);
    HomeFaq::query()->create(['sort_order' => 1, 'is_active' => true, 'question' => 'Editor question?', 'answer' => 'Editor answer.']);

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('slides', 1)
            ->where('slides.0.title', 'Editor slide')
            ->where('slides.0.style', 'emerald')
            ->where('slides.0.secondary', null)
            ->has('faqs', 1)
            ->where('faqs.0.question', 'Editor question?'));

    $this->withSession(['app_locale' => 'am'])
        ->get(route('landing.home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('slides', 3)
            ->where('slides.0.title', __('home.hero.slides.digital.title', [], 'am'))
            ->where('faqs.0.question', __('home.faq.questions', [], 'am')[0]['question']));
});

test('editor links that are not http(s), anchors or site paths never reach the page', function (): void {
    HomeSlide::query()->create([
        'sort_order' => 1,
        'is_active' => true,
        'title' => 'Slide',
        'primary_label' => 'Go',
        'primary_href' => 'javascript:alert(1)',
        'secondary_label' => 'More',
        'secondary_href' => '#faq',
        'bg_style' => 'unknown',
    ]);
    HomeSetting::setJson('footer.settings', [
        'social_facebook' => 'https://facebook.com/tribunal',
        'social_twitter' => 'javascript:alert(1)',
        'social_youtube' => '//evil.example/video',
    ]);
    HomeResource::query()->create(['sort_order' => 1, 'is_active' => true, 'type' => 'link', 'title' => 'Bad link', 'external_url' => 'javascript:alert(1)']);

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('slides.0.primary.href', '#')
            ->where('slides.0.secondary.href', '#faq')
            ->where('slides.0.style', 'blue')
            ->where('footer.social', [['network' => 'facebook', 'url' => 'https://facebook.com/tribunal']])
            ->where('resources.items.0.externalUrl', null));
});

test('the resources page lists the published resources', function (): void {
    HomeResource::query()->create(['sort_order' => 1, 'is_active' => true, 'type' => 'form', 'title' => 'Appeal form', 'file_path' => 'resources/appeal.pdf']);
    HomeResource::query()->create(['sort_order' => 2, 'is_active' => false, 'type' => 'post', 'title' => 'Hidden post']);
    HomeResource::query()->create(['sort_order' => 3, 'is_active' => true, 'type' => 'post', 'title' => 'Scheduled post', 'published_at' => now()->addWeek()]);

    $this->get(route('public.resources'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Resources')
            ->has('resources', 1)
            ->where('resources.0.title', 'Appeal form')
            ->where('resources.0.downloadUrl', asset('storage/resources/appeal.pdf')));
});

test('the terms page shows the latest published terms, purified', function (): void {
    $this->get(route('public.terms'))->assertNotFound();

    TermsAndCondition::query()->create([
        'title' => 'Portal terms',
        'slug' => 'portal-terms',
        'body' => '<p>Be accurate.</p><script>alert(1)</script>',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->get(route('public.terms'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Terms')
            ->where('title', 'Portal terms')
            ->where('bodyHtml', fn (string $html): bool => str_contains($html, 'Be accurate.') && ! str_contains($html, '<script')));
});

test('the user manual is only available while it is published', function (): void {
    $this->get(route('landing.user-manual'))->assertNotFound();

    HomeSetting::setJson('user_manual.settings', [
        'is_active' => true,
        'title_en' => 'Guide',
        'title_am' => 'መመሪያ',
        'content_en' => "First paragraph.\n\nSecond & third.",
        'content_am' => '<p>ሰላም</p>',
    ]);

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.user-manual'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/UserManual')
            ->where('title', 'Guide')
            ->where('contentHtml', fn (string $html): bool => str_contains($html, '<p>First paragraph.</p>') && str_contains($html, '<p>Second &amp; third.</p>')));
});

test('the signage display renders for guests and stays rate limited', function (): void {
    $this->get(route('public.signage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Signage')
            ->where('totalCases', 0)
            ->has('statuses')
            ->has('staff'));

    for ($i = 1; $i < 30; $i++) {
        $this->get(route('public.signage'))->assertOk();
    }

    $this->get(route('public.signage'))->assertTooManyRequests();
});

test('the signage display shows applicant initials, never full names', function (): void {
    partyCase(partyApplicant('signage'), 'SG/00001/26');

    $this->get(route('public.signage'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('todayCases.0.caseNumber', 'SG/00001/26')
            ->where('todayCases.0.applicant', 'H. B. P.'));
});
