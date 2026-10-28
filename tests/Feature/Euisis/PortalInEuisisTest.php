<?php

declare(strict_types=1);

use App\Models\Applicant;
use App\Models\IntegrationClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

/*
| Flow 6: with EUISIS_PORTAL_IN_EUISIS on, employees work on their cases in
| the EUISIS employee portal; the applicant and respondent portal here, with
| its sign-in, registration and password pages, says where to go instead.
*/

function closePortal(?string $url = 'https://euisis.example.test/my-portal/court-cases'): void
{
    config(['services.euisis.portal_in_euisis' => true, 'services.euisis.portal_url' => $url]);
}

test('every applicant and respondent page shows where cases are now, with no form behind it', function (): void {
    closePortal();

    foreach (['/applicant/login', '/applicant/register', '/applicant/forgot-password', '/applicant/cases', '/respondent/login', '/respondent/case-search', '/applicant/respondent/responses'] as $path) {
        // A signed-out visitor to a members' page is sent to the sign-in first, which is the same notice.
        $this->followingRedirects()->get($path)->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Court/PortalMoved')
            ->where('portalUrl', 'https://euisis.example.test/my-portal/court-cases'));
    }

    // Nothing is submitted to it.
    $this->post('/applicant/login', ['email' => 'someone@example.com', 'password' => 'secret'])->assertStatus(410);
    $this->post('/applicant/register', [])->assertStatus(410);
    expect($this->postJson('/applicant/cases/1/messages', ['body' => 'Hello'])->status())->toBeIn([401, 410]);
});

test('a court account still signed in is signed out, and the home page is the public site', function (): void {
    closePortal(null);
    $applicant = Applicant::create([
        'first_name' => 'Meron', 'middle_name' => 'A', 'last_name' => 'Haile', 'gender' => 'female', 'position' => 'Officer',
        'organization_name' => 'Org', 'phone' => '09'.random_int(10000000, 99999999), 'email' => 'moved-'.Str::lower(Str::random(6)).'@example.com',
        'address' => 'Addis Ababa', 'national_id_number' => (string) random_int(1000000000000000, 9999999999999999),
        'password' => Hash::make('password'), 'is_active' => true, 'is_lawyer' => false,
    ]);
    $applicant->markEmailAsVerified();

    $this->actingAs($applicant, 'applicant')->get('/applicant/dashboard')
        ->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->component('Court/PortalMoved')->where('portalUrl', null));
    $this->assertGuest('applicant');
    // A form posted from a page still open in that session is refused.
    $this->actingAs($applicant, 'applicant')->postJson('/applicant/cases/1/messages', ['body' => 'Hello'])
        ->assertStatus(410)->assertJsonPath('error_code', 'portal_in_euisis');

    $this->get('/')->assertRedirect(route('landing.home'));
    $this->get(route('landing.home'))->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->where('courtPortal.closed', true));
});

test('EUISIS gets no sign-in link to the closed portal', function (): void {
    closePortal();
    $client = new IntegrationClient(['name' => 'EUISIS-'.Str::random(6), 'abilities' => IntegrationClient::DEFAULT_ABILITIES, 'allowed_ips' => []]);

    $this->withToken($client->issueToken())->postJson('/api/v1/integration/people/sign-in-tickets', ['employee' => ['uuid' => (string) Str::uuid()], 'target' => 'dashboard'])
        ->assertStatus(410)->assertJsonPath('error_code', 'portal_in_euisis');
});

test('with the setting off the portal works as before', function (): void {
    config(['services.euisis.portal_in_euisis' => false]);

    $this->get('/applicant/login')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Applicant/Login'));
    $this->get('/')->assertRedirect(route('applicant.login'));
});
