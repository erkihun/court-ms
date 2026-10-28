<?php

declare(strict_types=1);

use App\Models\AboutPage;
use App\Models\Announcement;
use App\Models\CaseType;
use App\Models\Role;
use App\Models\TermsAndCondition;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

function cmsFormsAdmin(): User
{
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $user->roles()->syncWithoutDetaching([Role::query()->firstOrCreate(['name' => 'admin'])->id]);

    return $user;
}

test('announcements are created, shown and edited on the React pages', function (): void {
    $admin = cmsFormsAdmin();

    $this->actingAs($admin)->get(route('announcements.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Announcements/Form')->where('announcement', null));
    $this->actingAs($admin)->post(route('announcements.store'), ['title' => 'Holiday', 'content' => '<p>Closed Friday.</p>', 'status' => 'active'])
        ->assertRedirect(route('announcements.index'));
    $announcement = Announcement::query()->firstWhere('title', 'Holiday');

    $this->actingAs($admin)->get(route('announcements.show', $announcement))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Announcements/Show')->where('announcement.content', '<p>Closed Friday.</p>'));
    $this->actingAs($admin)->get(route('announcements.edit', $announcement))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Announcements/Form')->where('announcement.title', 'Holiday'));
});

test('terms are created and edited on the React form', function (): void {
    $admin = cmsFormsAdmin();

    $this->actingAs($admin)->get(route('terms.create'))->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Terms/Form')->where('term', null));
    $this->actingAs($admin)->post(route('terms.store'), ['title' => 'Filing terms', 'body' => 'Read first.', 'is_published' => 1])->assertRedirect(route('terms.index'));
    $term = TermsAndCondition::query()->firstWhere('title', 'Filing terms');
    expect($term->is_published)->toBeTrue();

    $this->actingAs($admin)->get(route('terms.edit', $term))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Terms/Form')->where('term.is_published', true));
});

test('About pages are created, shown and edited on the React pages', function (): void {
    $admin = cmsFormsAdmin();

    $this->actingAs($admin)->get(route('about.create'))->assertInertia(fn (AssertableInertia $page) => $page->component('Court/About/Form')->where('page', null));
    $this->actingAs($admin)->post(route('about.store'), ['title' => 'About us', 'slug' => null, 'body' => 'Who we are.', 'is_published' => 0])->assertRedirect(route('about.index'));
    $about = AboutPage::query()->firstWhere('title', 'About us');
    expect($about->slug)->toBe('about-us');

    $this->actingAs($admin)->get(route('about.show', $about))->assertInertia(fn (AssertableInertia $page) => $page->component('Court/About/Show')->where('page.slug', 'about-us'));
    $this->actingAs($admin)->get(route('about.edit', $about))->assertInertia(fn (AssertableInertia $page) => $page->component('Court/About/Form')->where('page.title', 'About us'));
});

test('case types are created and edited on the React form', function (): void {
    $admin = cmsFormsAdmin();

    $this->actingAs($admin)->get(route('case-types.create'))->assertInertia(fn (AssertableInertia $page) => $page->component('Court/CaseTypes/Form')->where('type', null));
    $this->actingAs($admin)->post(route('case-types.store'), ['name' => 'Tax', 'prefix' => 'TAX'])->assertRedirect(route('case-types.index'));
    $type = CaseType::query()->firstWhere('name', 'Tax');

    $this->actingAs($admin)->get(route('case-types.edit', $type->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Court/CaseTypes/Form')->where('type.name', 'Tax'));
});
