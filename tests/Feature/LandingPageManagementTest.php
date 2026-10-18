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
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Landing/Index')
            ->where('tab', 'slides')
            ->where('previewUrl', route('landing.home'))
            ->hasAll(['slides', 'steps', 'services', 'resources', 'faqs', 'manual', 'sections', 'footer'])
            ->has('metrics', 6)
            ->has('sections.cta.primary_label'));

    foreach (['slides', 'metrics', 'timeline', 'services', 'resources', 'manual', 'faqs', 'footer', 'sections'] as $tab) {
        expect(__("admin_landing.tabs.{$tab}", [], 'en'))->not->toBe("admin_landing.tabs.{$tab}");
    }
    expect(__('admin_landing.preview', [], 'en'))->toBe('Preview home page');
});

test('landing manager uses formal amharic labels in the amharic locale', function (): void {
    $expected = [
        'admin_landing.tabs.slides' => 'ዋና ማስታወቂያዎች',
        'admin_landing.tabs.metrics' => 'አጠቃላይ መረጃ',
        'admin_landing.tabs.timeline' => 'የጉዳይ ሂደት',
        'admin_landing.tabs.services' => 'አገልግሎቶች',
        'admin_landing.tabs.resources' => 'ሰነዶችና መረጃዎች',
        'admin_landing.tabs.faqs' => 'ተደጋጋሚ ጥያቄዎች',
        'admin_landing.tabs.footer' => 'የገጽ ግርጌ',
        'admin_landing.tabs.sections' => 'የገጽ ክፍሎች',
        'admin_landing.common.badge_text' => 'አጭር መለያ ጽሑፍ',
        'admin_landing.common.primary_button_label' => 'የዋና አዝራር ጽሑፍ',
        'admin_landing.common.duration' => 'የሚፈጀው ጊዜ',
        'admin_landing.common.features' => 'ዋና ባህሪያት',
        'admin_landing.common.publish_date' => 'የሚታተምበት ቀን',
        'admin_landing.common.question' => 'ጥያቄ',
        'admin_landing.footer.contact' => 'የግንኙነት መረጃ',
        'admin_landing.sections.section_title' => 'የክፍሉ ርዕስ',
    ];

    // The React page reads these from the translations endpoint for the active locale.
    $messages = $this->getJson(route('i18n.messages', ['locale' => 'am']))->assertOk()->json();

    foreach ($expected as $key => $label) {
        expect($messages[$key] ?? null)->toBe($label);
    }
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
        ->assertSee('Registered Tribunal Matters');
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
        ->assertSee('Current Tribunal Workload')
        ->assertSee('Portal Help');
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
        ->assertSee('የተጠቃሚ መመሪያ')
        ->assertSee(route('landing.user-manual'));

    $this->withSession(['app_locale' => 'en'])
        ->get(route('landing.user-manual'))
        ->assertOk()
        ->assertSee('<h2 style="color:#123456;">Applicant Guide</h2>', false)
        ->assertSee('<ol><li>Register an account</li>', false)
        ->assertSee('<table>', false)
        ->assertDontSee('&lt;h2');
});

test('the user manual is sanitised with HTMLPurifier before it is stored', function (): void {
    $this->actingAs(landingManager())->put(route('admin.landing.user-manual.update'), [
        'title_en' => 'User Manual',
        'title_am' => 'የተጠቃሚ መመሪያ',
        'content_en' => '<p onclick="steal()">Hi</p><a href="&#106;avascript:alert(1)">x</a><script>alert(2)</script>',
        'content_am' => '<p>ሰላም</p>',
        'is_active' => '1',
    ])->assertRedirect();

    $content = HomeSetting::getJson('user_manual.settings')['content_en'];

    expect($content)->toContain('<p>Hi</p>')
        ->not->toContain('onclick')
        ->not->toContain('javascript')
        ->not->toContain('&#106;')
        ->not->toContain('<script');
});
