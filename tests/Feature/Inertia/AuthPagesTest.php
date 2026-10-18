<?php

declare(strict_types=1);

use App\Models\AboutPage;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

function noStore(TestResponse $response): void
{
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
}

test('the staff sign-in screen is an uncached Inertia page with the login banner', function (): void {
    $settings = SystemSetting::query()->first() ?? SystemSetting::create([]);
    $settings->forceFill(['banner_path' => 'banners/court.jpg'])->save();
    Cache::forget('system_settings');

    $response = $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
            ->where('bannerUrl', asset('storage/banners/court.jpg')));

    noStore($response);
});

test('the party sign-in screen remembers the chosen role and sanitises the about page', function (): void {
    AboutPage::query()->create([
        'title' => 'About the tribunal',
        'slug' => 'about',
        'body' => '<p>Welcome</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>',
        'is_published' => true,
    ]);

    $response = $this->get(route('applicant.login', ['login_as' => 'respondent']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Party/Login')
            ->where('loginAs', 'respondent')
            ->where('about.title', 'About the tribunal')
            ->where('about.html', fn (string $html): bool => str_contains($html, '<p>Welcome</p>')
                && ! str_contains($html, '<script')
                && ! str_contains($html, 'javascript:')));

    noStore($response);

    $this->get(route('applicant.login', ['login_as' => 'anything-else']))
        ->assertInertia(fn (Assert $page) => $page->where('loginAs', 'applicant'));
});

test('registration and password reset screens render their React pages', function (string $routeName, string $component, ?string $portal): void {
    $this->get(route($routeName))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($component, $portal): void {
            $page->component($component);
            if ($portal !== null) {
                $page->where('portal', $portal);
            }
        });
})->with([
    'applicant register' => ['applicant.register', 'Auth/Party/Register', null],
    'staff forgot password' => ['password.request', 'Auth/ForgotPassword', 'staff'],
    'party forgot password' => ['applicant.password.request', 'Auth/ForgotPassword', 'party'],
]);

test('later reset steps still require the earlier ones', function (): void {
    $this->get(route('admin.password.otp.show'))->assertRedirect(route('password.request'));
    $this->get(route('password.reset.new'))->assertRedirect(route('password.request'));
    $this->get(route('applicant.password.otp.show'))->assertRedirect(route('applicant.password.request'));

    $this->withSession(['admin_pwd_otp_email' => 'someone@example.test'])
        ->get(route('admin.password.otp.show'))
        ->assertInertia(fn (Assert $page) => $page->component('Auth/PasswordOtp')->where('portal', 'staff'));

    $this->withSession(['applicant_pwd_verified_email' => 'someone@example.test'])
        ->get(route('applicant.password.new.show'))
        ->assertInertia(fn (Assert $page) => $page->component('Auth/ResetPassword')->where('portal', 'party'));
});

test('MFA setup sends the pending secret as an image, never as inline markup', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mfa.setup.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/MfaSetup')
            ->where('confirmed', false)
            ->where('secret', null)
            ->where('qrCode', null));

    $this->post(route('mfa.setup.begin'))->assertRedirect(route('mfa.setup.show'));

    $this->get(route('mfa.setup.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('secret', $user->fresh()->mfa_secret)
            ->where('qrCode', fn (string $qr): bool => str_starts_with($qr, 'data:image/svg+xml;base64,')));
});

test('a confirmed MFA user is not shown the secret again', function (): void {
    $user = User::factory()->create();
    $user->forceFill([
        'mfa_secret' => app(MfaService::class)->generateSecret(),
        'mfa_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['mfa_verified_at' => now()->timestamp])
        ->get(route('mfa.setup.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('confirmed', true)
            ->where('secret', null)
            ->where('qrCode', null));
});

test('a wrong password on the confirm screen says so instead of echoing the field label', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('password.confirm'))
        ->post(route('password.confirm'), ['password' => 'not-the-password'])
        ->assertRedirect(route('password.confirm'))
        ->assertSessionHasErrors(['password' => __('validation.current_password')]);
});
