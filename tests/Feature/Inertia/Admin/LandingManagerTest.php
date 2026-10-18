<?php

declare(strict_types=1);

use App\Models\HomeFaq;
use App\Models\HomeResource;
use App\Models\HomeService;
use App\Models\HomeSetting;
use App\Models\HomeSlide;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Storage::fake('public');
    $this->staff = inertiaStaff(['settings.manage']);
});

test('slides are created with an image, updated through method spoofing, toggled and deleted', function (): void {
    $this->actingAs($this->staff)
        ->from(route('admin.landing.index'))
        ->post(route('admin.landing.slides.store'), [
            'title' => 'Welcome',
            'primary_label' => 'Register',
            'primary_href' => '/applicant/register',
            'bg_style' => 'orange',
            'bg_image' => UploadedFile::fake()->image('hero.jpg', 1200, 400),
        ])
        ->assertRedirect(route('admin.landing.index'))
        ->assertSessionHas('success', __('admin_landing.flash.slide_added'));

    $slide = HomeSlide::query()->sole();
    Storage::disk('public')->assertExists($slide->bg_image);

    $this->actingAs($this->staff)->get(route('admin.landing.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('slides.0.title', 'Welcome')
            ->where('slides.0.bgStyle', 'orange')
            ->where('slides.0.bgImageUrl', asset('storage/'.$slide->bg_image)));

    $this->actingAs($this->staff)->post(route('admin.landing.slides.update', $slide), [
        '_method' => 'put',
        'title' => 'Welcome back',
        'primary_label' => 'Register',
        'primary_href' => '/applicant/register',
        'bg_style' => 'blue',
        'remove_bg_image' => '1',
    ])->assertRedirect();

    expect($slide->fresh())->title->toBe('Welcome back')->bg_image->toBeNull();

    $wasActive = (bool) $slide->fresh()->is_active;
    $this->actingAs($this->staff)->patch(route('admin.landing.slides.toggle', $slide))->assertRedirect();
    expect((bool) $slide->fresh()->is_active)->toBe(! $wasActive);

    $this->actingAs($this->staff)->delete(route('admin.landing.slides.destroy', $slide))->assertRedirect();
    expect(HomeSlide::query()->count())->toBe(0);
});

test('service features are split into lines and shown back as a list', function (): void {
    $this->actingAs($this->staff)->post(route('admin.landing.services.store'), [
        'title' => 'E-filing',
        'features' => "Upload evidence\r\n\r\n Track status ",
        'icon_type' => 'document',
        'accent' => 'pink',
    ])->assertRedirect();

    expect(HomeService::query()->sole()->features)->toBe(['Upload evidence', 'Track status']);

    $this->actingAs($this->staff)->get(route('admin.landing.index'))
        ->assertInertia(fn (Assert $page) => $page->where('services.0.features', ['Upload evidence', 'Track status']));
});

test('resources store files and scheduled publish dates', function (): void {
    $this->actingAs($this->staff)->post(route('admin.landing.resources.store'), [
        'title' => 'Filing form',
        'type' => 'form',
        'published_at' => now()->addDays(3)->format('Y-m-d\TH:i'),
        'file' => UploadedFile::fake()->create('form.pdf', 100, 'application/pdf'),
        'is_featured' => '1',
    ])->assertRedirect();

    $resource = HomeResource::query()->sole();
    expect($resource->is_featured)->toBeTrue();
    Storage::disk('public')->assertExists($resource->file_path);

    $this->actingAs($this->staff)->get(route('admin.landing.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('resources.0.type', 'form')
            ->where('resources.0.fileName', basename($resource->file_path))
            ->where('resources.0.scheduledFor', fn ($value) => is_string($value) && $value !== ''));
});

test('faq validation errors and json section settings round-trip', function (): void {
    $this->actingAs($this->staff)
        ->from(route('admin.landing.index'))
        ->post(route('admin.landing.faqs.store'), ['question' => '', 'answer' => ''])
        ->assertSessionHasErrors(['question', 'answer']);
    expect(HomeFaq::query()->count())->toBe(0);

    $this->actingAs($this->staff)->putJson(route('admin.landing.sections.update'), [
        'cases' => ['visible' => false],
        'cta' => ['visible' => true, 'title' => 'Ready?', 'primary_label' => 'Start', 'primary_href' => '/applicant/register'],
        'unknown' => ['visible' => true],
    ])->assertRedirect();

    expect(HomeSetting::getJson('section.cases'))->toBe(['visible' => false])
        ->and(HomeSetting::getJson('section.cta')['title'])->toBe('Ready?')
        ->and(HomeSetting::getJson('section.unknown'))->toBe([]);
});

test('the landing manager needs settings.manage', function (): void {
    $this->actingAs(inertiaStaff(['cases.view']))
        ->get(route('admin.landing.index'))
        ->assertRedirect(route('dashboard'));
});
