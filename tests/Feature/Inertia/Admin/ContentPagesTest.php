<?php

declare(strict_types=1);

use App\Models\AboutPage;
use App\Models\Announcement;
use App\Models\TermsAndCondition;
use Inertia\Testing\AssertableInertia as Assert;

test('announcements are listed, created, shown, updated and deleted through Inertia', function (): void {
    $staff = inertiaStaff(['announcements.manage']);

    $this->actingAs($staff)->get(route('announcements.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Announcements/Index')->has('announcements.data', 0));

    $this->actingAs($staff)->get(route('announcements.create'))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Announcements/Form')->where('announcement', null));

    $this->actingAs($staff)->post(route('announcements.store'), [
        'title' => 'Court closed',
        'content' => '<p>Closed on Friday</p><script>alert(1)</script>',
        'status' => 'active',
    ])->assertRedirect(route('announcements.index'));

    $announcement = Announcement::query()->sole();
    expect($announcement->content)->toContain('Closed on Friday')->not->toContain('<script');

    $this->actingAs($staff)->get(route('announcements.show', $announcement))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Announcements/Show')
            ->where('announcement.title', 'Court closed')
            ->where('announcement.status', 'active'));

    $this->actingAs($staff)->get(route('announcements.edit', $announcement))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Announcements/Form')->where('announcement.id', $announcement->id));

    $this->actingAs($staff)->patch(route('announcements.update', $announcement), [
        'title' => 'Court open',
        'content' => '<p>Open</p>',
        'status' => 'inactive',
    ])->assertRedirect(route('announcements.index'));
    expect($announcement->fresh()->status)->toBe('inactive');

    $this->actingAs($staff)->delete(route('announcements.destroy', $announcement))->assertRedirect(route('announcements.index'));
    expect(Announcement::query()->count())->toBe(0);
});

test('announcement validation errors come back to the form', function (): void {
    $this->actingAs(inertiaStaff(['announcements.manage']))
        ->from(route('announcements.create'))
        ->post(route('announcements.store'), ['title' => '', 'content' => '', 'status' => 'archived'])
        ->assertRedirect(route('announcements.create'))
        ->assertSessionHasErrors(['title', 'content', 'status']);
});

test('about pages are managed through Inertia with sanitised bodies and unique slugs', function (): void {
    $staff = inertiaStaff(['about.manage']);

    $this->actingAs($staff)->get(route('about.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/About/Index')->has('pages.data', 0));

    $payload = ['title' => 'Our Court', 'body' => '<p>History</p><img src=x onerror=alert(1)>', 'is_published' => true];
    $this->actingAs($staff)->post(route('about.store'), $payload)->assertRedirect(route('about.index'));
    $this->actingAs($staff)->post(route('about.store'), $payload)->assertRedirect(route('about.index'));

    $pages = AboutPage::query()->orderBy('id')->get();
    expect($pages->pluck('slug')->all())->toBe(['our-court', 'our-court-2'])
        ->and($pages->first()->body)->not->toContain('onerror')
        ->and($pages->first()->is_published)->toBeTrue();

    $this->actingAs($staff)->get(route('about.show', $pages->first()))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/About/Show')
            ->where('about.slug', 'our-court')
            ->where('about.bodyHtml', fn (string $html) => str_contains($html, 'History') && ! str_contains($html, 'onerror')));

    $this->actingAs($staff)->patch(route('about.update', $pages->first()), ['title' => 'Our Court', 'slug' => 'history', 'body' => '<p>New</p>'])
        ->assertRedirect(route('about.index'));
    expect($pages->first()->fresh())->slug->toBe('history')->is_published->toBeFalse();
});

test('terms set and clear their publish date', function (): void {
    $staff = inertiaStaff(['settings.manage']);

    $this->actingAs($staff)->post(route('terms.store'), ['title' => 'Filing terms', 'body' => 'Be truthful.', 'is_published' => true])
        ->assertRedirect(route('terms.index'));
    $term = TermsAndCondition::query()->sole();
    expect($term->published_at)->not->toBeNull();

    $this->actingAs($staff)->get(route('terms.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Terms/Index')
            ->where('terms.data.0.title', 'Filing terms')
            ->where('terms.data.0.isPublished', true)
            ->where('publicUrl', route('public.terms')));

    $this->actingAs($staff)->get(route('terms.edit', $term))
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Terms/Form')->where('term.body', 'Be truthful.'));

    $this->actingAs($staff)->patch(route('terms.update', $term), ['title' => 'Filing terms', 'body' => 'Be truthful.'])
        ->assertRedirect(route('terms.index'));
    expect($term->fresh()->published_at)->toBeNull();
});

test('content screens require their permission', function (string $routeName, string $permission): void {
    // The perm: middleware sends staff without the permission back to the dashboard.
    $this->actingAs(inertiaStaff(['cases.view']))->get(route($routeName))->assertRedirect(route('dashboard'));
    $this->actingAs(inertiaStaff([$permission]))->get(route($routeName))->assertOk();
})->with([
    'announcements' => ['announcements.index', 'announcements.manage'],
    'about' => ['about.index', 'about.manage'],
    'terms' => ['terms.index', 'settings.manage'],
]);
