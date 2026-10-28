<?php

declare(strict_types=1);

use App\Models\AboutPage;
use App\Models\Announcement;
use App\Models\Applicant;
use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\HomeResource;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

/*
 * Site content: the links landing editors type reach an href only as http(s),
 * # anchors or site paths (checked on save, dropped on output); the user
 * manual, announcements and About pages are purified when saved and when
 * shown; section settings store only validated keys; validation messages
 * name the fields in the request's language; and the public signage board
 * shows applicants' initials, not their names.
 */

function contentHardeningStaff(array $permissions): User
{
    $role = Role::query()->create(['name' => 'content-'.Str::lower(Str::random(8))]);
    $role->permissions()->sync(collect($permissions)->map(
        fn (string $permission): int => Permission::query()->firstOrCreate(['name' => $permission], ['label' => Str::headline($permission)])->id,
    )->all());
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $user->roles()->sync([$role->id]);

    return $user;
}

/** Fields that must never survive into stored or shown rich text. */
const CONTENT_HARDENING_PAYLOAD = '<p>Hi</p><script>alert(1)</script><img src="x" onerror="alert(1)"><a href="&#106;avascript:alert(1)">x</a><p onclick="alert(1)">Tap</p>';

function contentHardeningIsClean(string $html): bool
{
    $lower = strtolower($html);

    return ! str_contains($lower, '<script')
        && ! str_contains($lower, 'onerror')
        && ! str_contains($lower, 'onclick')
        && ! str_contains($lower, 'javascript')
        && ! str_contains($lower, '&#106;');
}

beforeEach(fn () => Cache::flush());

// ---------------------------------------------------------------- links on save

test('slide and banner links accept only http(s), # anchors and site paths', function (): void {
    $editor = contentHardeningStaff(['settings.manage']);
    $slide = fn (array $links): array => [
        'title' => 'File online', 'primary_label' => 'Start', 'bg_style' => 'blue', ...$links,
    ];

    foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'data:text/html,<b>x</b>', '//evil.test/x', 'vbscript:x'] as $bad) {
        $this->actingAs($editor)->from(route('admin.landing.index'))
            ->post(route('admin.landing.slides.store'), $slide(['primary_href' => $bad, 'secondary_label' => 'More', 'secondary_href' => $bad]))
            ->assertSessionHasErrors(['primary_href', 'secondary_href']);
    }
    expect(HomeSlide::query()->count())->toBe(0);

    foreach (['/applicant/register', '#statistics', 'https://court.test/file', 'http://court.test'] as $good) {
        $this->actingAs($editor)->post(route('admin.landing.slides.store'), $slide(['primary_href' => $good]))->assertSessionHasNoErrors();
    }
    expect(HomeSlide::query()->count())->toBe(4);

    $this->actingAs($editor)->from(route('admin.landing.index'))
        ->put(route('admin.landing.sections.update'), ['cta' => ['visible' => true, 'primary_href' => 'javascript:alert(1)', 'secondary_href' => '/terms']])
        ->assertSessionHasErrors(['cta.primary_href'])
        ->assertSessionDoesntHaveErrors(['cta.secondary_href']);
    expect(HomeSetting::getJson('section.cta'))->toBeEmpty();
});

test('social links and resource URLs accept only http(s) addresses', function (): void {
    $editor = contentHardeningStaff(['settings.manage']);

    // Laravel's own `url` rule lets "javascript://…" through; these must not.
    $this->actingAs($editor)->from(route('admin.landing.index'))
        ->put(route('admin.landing.footer.update'), ['social_facebook' => 'javascript://%0Aalert(1)', 'social_telegram' => '/relative', 'social_youtube' => 'https://youtube.com/@court'])
        ->assertSessionHasErrors(['social_facebook', 'social_telegram'])
        ->assertSessionDoesntHaveErrors(['social_youtube']);

    $this->actingAs($editor)->from(route('admin.landing.index'))
        ->post(route('admin.landing.resources.store'), ['title' => 'Form', 'type' => 'link', 'external_url' => 'javascript://%0Aalert(1)'])
        ->assertSessionHasErrors(['external_url']);
    expect(HomeResource::query()->count())->toBe(0);

    $this->actingAs($editor)->post(route('admin.landing.resources.store'), ['title' => 'Form', 'type' => 'link', 'external_url' => 'https://court.test/form'])
        ->assertSessionHasNoErrors();
    expect(HomeResource::query()->value('external_url'))->toBe('https://court.test/form');
});

test('landing validation messages name the field in the request language', function (): void {
    $editor = contentHardeningStaff(['settings.manage']);

    $this->actingAs($editor)->withSession(['app_locale' => 'en'])->from(route('admin.landing.index'))
        ->post(route('admin.landing.slides.store'), ['title' => 'x', 'primary_label' => 'Go', 'bg_style' => 'blue', 'primary_href' => 'javascript:alert(1)'])
        ->assertSessionHasErrors(['primary_href' => __('admin_landing.validation.safe_link', ['attribute' => __('admin_landing.common.primary_button_url', [], 'en')], 'en')]);

    $this->actingAs($editor)->withSession(['app_locale' => 'am'])->from(route('admin.landing.index'))
        ->post(route('admin.landing.slides.store'), ['title' => '', 'primary_label' => 'Go', 'bg_style' => 'blue', 'primary_href' => 'javascript:alert(1)']);
    $errors = session('errors')->getBag('default');
    expect($errors->first('primary_href'))->toContain(__('admin_landing.common.primary_button_url', [], 'am'))
        ->and($errors->first('title'))->toContain(__('admin_landing.common.title', [], 'am'))
        ->and($errors->first('title'))->not->toContain('title');
});

// ---------------------------------------------------------------- links on output

test('a stored unsafe link never reaches the public pages', function (): void {
    HomeSlide::query()->create([
        'sort_order' => 1, 'is_active' => true, 'title' => 'Old slide', 'primary_label' => 'Go', 'bg_style' => 'blue',
        'primary_href' => 'javascript:alert(1)', 'secondary_label' => 'More', 'secondary_href' => 'https://court.test/more',
    ]);
    HomeResource::query()->create(['sort_order' => 1, 'is_active' => true, 'is_featured' => false, 'type' => 'link', 'title' => 'Old link', 'external_url' => 'javascript:alert(1)']);
    HomeSetting::setJson('footer.settings', ['social_facebook' => 'javascript:alert(1)', 'social_telegram' => 'https://t.me/court', 'contact_email' => 'registry@court.test']);

    $this->withSession(['app_locale' => 'en'])->get(route('landing.home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('slides.0.primary_href', '#')
            ->where('slides.0.secondary_href', 'https://court.test/more')
            ->where('resources.0.external_url', null)
            ->where('publicSite.footer.social_facebook', null)
            ->where('publicSite.footer.social_telegram', 'https://t.me/court')
            ->where('publicSite.footer.contact_email', 'registry@court.test'));

    $this->get(route('public.resources'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('resources.0.external_url', null));
});

// ---------------------------------------------------------------- rich text

test('the user manual is purified with HTMLPurifier when saved and when shown', function (): void {
    $editor = contentHardeningStaff(['settings.manage']);

    $this->actingAs($editor)->put(route('admin.landing.user-manual.update'), [
        'title_en' => 'Guide', 'title_am' => 'መመሪያ', 'content_en' => CONTENT_HARDENING_PAYLOAD, 'content_am' => '<p>ሰላም</p>', 'is_active' => true,
    ])->assertSessionHasNoErrors();

    $stored = (string) HomeSetting::getJson('user_manual.settings')['content_en'];
    expect($stored)->toContain('<p>Hi</p>')->and(contentHardeningIsClean($stored))->toBeTrue();

    // A manual saved before the save-side cleaning is cleaned on its way out.
    HomeSetting::setJson('user_manual.settings', ['title_en' => 'Guide', 'content_en' => CONTENT_HARDENING_PAYLOAD, 'is_active' => true]);
    $this->withSession(['app_locale' => 'en'])->get(route('landing.user-manual'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content', fn (string $html): bool => str_contains($html, '<p>Hi</p>') && contentHardeningIsClean($html)));
});

test('announcement content is purified when saved and when shown', function (): void {
    $editor = contentHardeningStaff(['announcements.manage']);

    $this->actingAs($editor)->post(route('announcements.store'), ['title' => 'Notice', 'content' => CONTENT_HARDENING_PAYLOAD, 'status' => 'active'])
        ->assertRedirect(route('announcements.index'));
    $saved = Announcement::query()->firstWhere('title', 'Notice');
    expect($saved->content)->toContain('<p>Hi</p>')->and(contentHardeningIsClean($saved->content))->toBeTrue();

    $this->actingAs($editor)->put(route('announcements.update', $saved), ['title' => 'Notice', 'content' => CONTENT_HARDENING_PAYLOAD, 'status' => 'active'])
        ->assertRedirect(route('announcements.index'));
    expect(contentHardeningIsClean($saved->fresh()->content))->toBeTrue();

    $legacy = Announcement::query()->create(['title' => 'Legacy', 'content' => CONTENT_HARDENING_PAYLOAD, 'status' => 'inactive']);
    $this->actingAs($editor)->get(route('announcements.show', $legacy))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('announcement.content', fn (string $html): bool => contentHardeningIsClean($html)));
});

test('About page bodies are purified when saved', function (): void {
    $editor = contentHardeningStaff(['about.manage']);

    $this->actingAs($editor)->post(route('about.store'), ['title' => 'About us', 'body' => CONTENT_HARDENING_PAYLOAD, 'is_published' => 1])
        ->assertRedirect(route('about.index'));
    $page = AboutPage::query()->firstWhere('title', 'About us');
    expect($page->body)->toContain('Hi')->and(contentHardeningIsClean($page->body))->toBeTrue();

    $this->actingAs($editor)->put(route('about.update', $page), ['title' => 'About us', 'body' => CONTENT_HARDENING_PAYLOAD, 'is_published' => 1])
        ->assertRedirect(route('about.index'));
    expect(contentHardeningIsClean($page->fresh()->body))->toBeTrue();
});

// ---------------------------------------------------------------- section settings

test('section settings store only the validated keys', function (): void {
    $editor = contentHardeningStaff(['settings.manage']);

    $this->actingAs($editor)->put(route('admin.landing.sections.update'), [
        'metrics' => ['visible' => true, 'title' => 'Figures', 'subtitle' => 'Live', 'injected' => '<script>x</script>'],
        'bogus' => ['visible' => true, 'title' => 'Not a section'],
    ])->assertSessionHasNoErrors();

    expect(HomeSetting::getJson('section.metrics'))->toBe(['visible' => true, 'title' => 'Figures', 'subtitle' => 'Live'])
        ->and(HomeSetting::getJson('section.bogus'))->toBeEmpty();
});

// ---------------------------------------------------------------- signage

test('the public signage board shows applicant initials, never full names', function (): void {
    $applicant = Applicant::create([
        'first_name' => 'Helina', 'middle_name' => 'Birtukan', 'last_name' => 'Paulos', 'gender' => 'female',
        'phone' => '+251911'.random_int(100000, 999999), 'email' => 'signage-'.uniqid().'@example.com', 'address' => 'Addis Ababa',
        'national_id_number' => (string) random_int(1000000000000000, 9999999999999999), 'password' => Hash::make('password'),
        'is_active' => true, 'is_lawyer' => false,
    ]);
    CourtCase::query()->create([
        'case_number' => 'SG/00001/26', 'title' => 'Private dispute', 'case_type_id' => CaseType::query()->create(['name' => 'Labour'])->id,
        'filing_date' => now()->toDateString(), 'status' => 'pending', 'applicant_id' => $applicant->id,
    ]);

    $this->get(route('public.signage'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('todayCases.0.case_number', 'SG/00001/26')
            ->where('todayCases.0.applicant', 'H. B. P.'))
        ->assertDontSee('Helina', false)
        ->assertDontSee('Birtukan', false)
        ->assertDontSee('Paulos', false)
        ->assertDontSee('Private dispute', false);
});
