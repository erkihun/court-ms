<?php

declare(strict_types=1);

use App\Models\HomeSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function landingManager(): User
{
    $role = Role::query()->create(['name' => 'landing-manager-'.Str::uuid()]);
    $role->permissions()->sync([
        Permission::query()->firstOrCreate(['name' => 'settings.manage'], ['label' => 'Manage settings'])->id,
    ]);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

test('landing manager exposes controls for every home page section', function (): void {
    $admin = landingManager();

    $this->actingAs($admin)
        ->get(route('admin.landing.index'))
        ->assertOk()
        ->assertSee('Hero Slides')
        ->assertSee('Statistics')
        ->assertSee('Timeline')
        ->assertSee('Services')
        ->assertSee('Resources')
        ->assertSee('FAQ')
        ->assertSee('Footer')
        ->assertSee('Sections')
        ->assertSee('Preview home page');
});

test('landing manager uses formal amharic labels in the amharic locale', function (): void {
    $admin = landingManager();

    $this->actingAs($admin)
        ->withSession(['app_locale' => 'am'])
        ->get(route('admin.landing.index'))
        ->assertOk()
        ->assertSee('ዋና ማስታወቂያዎች')
        ->assertSee('አጠቃላይ መረጃ')
        ->assertSee('የጉዳይ ሂደት')
        ->assertSee('አገልግሎቶች')
        ->assertSee('ሰነዶችና መረጃዎች')
        ->assertSee('ተደጋጋሚ ጥያቄዎች')
        ->assertSee('የገጽ ግርጌ')
        ->assertSee('የገጽ ክፍሎች')
        ->assertSee('አጭር መለያ ጽሑፍ')
        ->assertSee('የዋና አዝራር ጽሑፍ')
        ->assertSee('የሚፈጀው ጊዜ')
        ->assertSee('ዋና ባህሪያት')
        ->assertSee('የሚታተምበት ቀን')
        ->assertSee('ጥያቄ')
        ->assertSee('የግንኙነት መረጃ')
        ->assertSee('የክፍሉ ርዕስ');
});

test('administrator can update statistics content displayed on the english home page', function (): void {
    $admin = landingManager();

    $payload = [
        'total_cases' => [
            'label' => 'Registered Tribunal Matters',
            'description' => 'All matters currently registered by the tribunal.',
        ],
    ];

    $this->actingAs($admin)
        ->put(route('admin.landing.metrics.update'), $payload)
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(HomeSetting::getJson('metrics.content')['total_cases'])
        ->toBe($payload['total_cases']);

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('metrics.0.key', 'total_cases')
            ->where('metrics.0.label', 'Registered Tribunal Matters'));
});

test('section settings control metrics and faq headings on the home page', function (): void {
    $admin = landingManager();

    $this->actingAs($admin)
        ->put(route('admin.landing.sections.update'), [
            'metrics' => [
                'visible' => '1',
                'title' => 'Current Tribunal Workload',
                'subtitle' => 'Live operational totals.',
            ],
            'faq' => [
                'visible' => '1',
                'title' => 'Portal Help',
                'subtitle' => 'Answers for applicants and respondents.',
            ],
        ])
        ->assertRedirect();

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sections.metrics.title', 'Current Tribunal Workload')
            ->where('sections.faq.title', 'Portal Help'));
});

test('administrator manages the user manual opened from localized home navigation', function (): void {
    $admin = landingManager();

    $this->actingAs($admin)->put(route('admin.landing.user-manual.update'), [
        'title_en' => 'User Manual',
        'title_am' => 'የተጠቃሚ መመሪያ',
        'content_en' => '<h2 style="color: #123456;">Applicant Guide</h2><ol><li>Register an account</li><li><strong>File a case</strong></li></ol><table><tr><th>Step</th><th>Action</th></tr><tr><td>1</td><td>Sign in</td></tr></table>',
        'content_am' => '<h2 dir="rtl">የአመልካች መመሪያ</h2><ul><li>መለያ ይክፈቱ</li><li>ጉዳይ ያቅርቡ</li></ul>',
        'is_active' => '1',
    ])->assertRedirect();

    $settings = HomeSetting::getJson('user_manual.settings');

    $this->withSession(['app_locale' => 'am'])
        ->get(route('landing.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->where('userManual.label', 'የተጠቃሚ መመሪያ')
            ->where('userManual.url', route('landing.user-manual')));

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.user-manual'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/UserManual')
            ->where('title', 'User Manual')
            ->where('contentHtml', fn (string $html): bool => str_contains($html, '<h2 style="color:#123456;">Applicant Guide</h2>')
                && str_contains($html, '<ol><li>Register an account</li>')
                && str_contains($html, '<table>')
                && ! str_contains($html, '&lt;h2')));
});
