<?php

declare(strict_types=1);

use App\Models\Applicant;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ApplicantResetPassword;
use App\Notifications\LawyerApplicationApproved;
use App\Notifications\LawyerApplicationRefused;
use App\Services\Notifications\AdminNotificationFeed;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

/*
| Lawyers apply to be registered at /lawyer/register (EUISIS ADR 0013 #13 as
| amended on 2026-10-29). The account stays closed until the registry verifies
| the licence on its Lawyers screen, which emails the link to choose a
| password; a refusal is emailed with its reason.
*/

function applicationStaff(array $permissions): User
{
    $user = User::factory()->create(['status' => 'active', 'email_verified_at' => now()]);
    $role = Role::query()->create(['name' => 'Lawyers '.Str::random(6)]);
    $role->permissions()->sync(collect($permissions)->map(fn (string $name): int => Permission::query()->firstOrCreate(['name' => $name], ['label' => Str::headline($name)])->id));
    $user->roles()->sync([$role->id]);

    return $user;
}

/** @return array<string, mixed> the fields of a lawyer's application */
function applicationFields(array $overrides = []): array
{
    return [
        'first_name' => 'Selam', 'middle_name' => 'T', 'last_name' => 'Bekele', 'gender' => 'female',
        'email' => 'selam-'.Str::lower(Str::random(6)).'@example.com', 'phone' => '09'.random_int(10000000, 99999999),
        'national_id_number' => (string) random_int(1000000000000000, 9999999999999999), 'address' => 'Addis Ababa',
        'license_number' => 'LIC-'.Str::upper(Str::random(6)),
        'license_document' => UploadedFile::fake()->createWithContent('licence.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n"),
        ...$overrides,
    ];
}

/** @return list<string> the lawyer applications in a staff member's bell */
function applicationNotices(User $user): array
{
    return collect((new AdminNotificationFeed($user))->feed(1)['items'])->pluck('id')
        ->filter(fn (string $id): bool => str_starts_with($id, 'lawyer_application:'))->values()->all();
}

beforeEach(function (): void {
    Storage::fake('private');
    Mail::fake();
    Notification::fake();
});

test('a lawyer applies and waits: no sign-in or password link until the registry verifies the licence', function (): void {
    $this->get(route('lawyer.register'))->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->component('Court/Lawyer/Register'));

    $fields = applicationFields();
    $this->post(route('lawyer.register.submit'), $fields)->assertRedirect(route('lawyer.login'))->assertSessionHas('status');

    $lawyer = Applicant::query()->where('email', $fields['email'])->sole();
    expect((bool) $lawyer->is_lawyer)->toBeTrue()
        ->and($lawyer->lawyer_license_number)->toBe($fields['license_number'])
        ->and($lawyer->lawyer_applied_at)->not->toBeNull()
        ->and($lawyer->lawyer_verified_at)->toBeNull()
        ->and($lawyer->email_verified_at)->toBeNull()
        ->and($lawyer->lawyer_document_path)->not->toBeEmpty();
    Storage::disk('private')->assertExists($lawyer->lawyer_document_path);
    $this->assertGuest('applicant');

    // Not verified: the sign-in refuses it as any other, and no password link is sent.
    $this->post(route('lawyer.login.submit'), ['email' => $lawyer->email, 'password' => 'anything'])->assertSessionHasErrors('email');
    $this->post(route('lawyer.password.email'), ['email' => $lawyer->email])->assertSessionHas('status');
    Notification::assertNothingSentTo($lawyer);
});

test('an application needs the licence PDF and a licence number nobody else holds', function (): void {
    $taken = applicationFields();
    $this->post(route('lawyer.register.submit'), $taken)->assertSessionHasNoErrors();

    $this->post(route('lawyer.register.submit'), applicationFields(['license_document' => null]))->assertSessionHasErrors('license_document');
    $this->post(route('lawyer.register.submit'), applicationFields(['license_number' => $taken['license_number']]))->assertSessionHasErrors('license_number');
    expect(Applicant::query()->where('is_lawyer', true)->count())->toBe(1);
});

test('the registry is alerted, sees the licence and verifies: the lawyer chooses a password and signs in', function (): void {
    $fields = applicationFields();
    $this->post(route('lawyer.register.submit'), $fields)->assertSessionHasNoErrors();
    $lawyer = Applicant::query()->where('email', $fields['email'])->sole();
    $manager = applicationStaff(['applicants.view', 'applicants.manage']);
    $viewer = applicationStaff(['applicants.view']);

    expect(applicationNotices($manager))->toBe(['lawyer_application:'.$lawyer->id])
        ->and(applicationNotices($viewer))->toBe([]);

    $this->actingAs($viewer)->get(route('lawyers.index'))->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('lawyers.data.0.id', $lawyer->id)
            ->where('lawyers.data.0.verified', false)->where('lawyers.data.0.refused', false)
            ->where('lawyers.data.0.has_document', true)->where('lawyers.data.0.applied_at', fn ($at) => $at !== null));
    $this->actingAs($viewer)->get(route('lawyers.license', $lawyer->id))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->actingAs($viewer)->patch(route('lawyers.verify', $lawyer->id), ['verified' => true, 'license_number' => $fields['license_number']])->assertRedirect(route('dashboard'));

    $this->actingAs($manager)->patch(route('lawyers.verify', $lawyer->id), ['verified' => true, 'license_number' => $fields['license_number']])
        ->assertRedirect()->assertSessionHasNoErrors();
    $lawyer->refresh();
    expect($lawyer->lawyer_verified_at)->not->toBeNull()->and($lawyer->email_verified_at)->not->toBeNull()
        ->and(applicationNotices($manager))->toBe([]);

    $token = null;
    Notification::assertSentTo($lawyer, LawyerApplicationApproved::class, function (LawyerApplicationApproved $notice) use ($lawyer, &$token): bool {
        $url = (string) $notice->toMail($lawyer)->actionUrl;
        preg_match('#/lawyer/reset-password/([^?]+)#', $url, $match);
        $token = $match[1] ?? null;

        return $token !== null;
    });
    Notification::assertNotSentTo($lawyer, ApplicantResetPassword::class);

    // The lawyer, in their own browser: nobody signed in.
    $this->app['auth']->forgetGuards();
    $this->post(route('lawyer.password.update'), ['token' => $token, 'email' => $lawyer->email, 'password' => 'Lawyer#Pass2026!', 'password_confirmation' => 'Lawyer#Pass2026!'])
        ->assertRedirect(route('lawyer.login'));
    $this->post(route('lawyer.login.submit'), ['email' => $lawyer->email, 'password' => 'Lawyer#Pass2026!'])->assertRedirect(route('applicant.cases.index'));
    $this->assertAuthenticatedAs($lawyer, 'applicant');

    // Verifying again later (after a withdrawal) does not send a second approval.
    $this->actingAs($manager, 'web')->patch(route('lawyers.verify', $lawyer->id), ['verified' => false])->assertRedirect();
    $this->actingAs($manager, 'web')->patch(route('lawyers.verify', $lawyer->id), ['verified' => true, 'license_number' => $fields['license_number']])->assertRedirect();
    Notification::assertSentToTimes($lawyer, LawyerApplicationApproved::class, 1);
});

test('the registry refuses an application with a reason the lawyer is emailed; the account stays closed', function (): void {
    $fields = applicationFields();
    $this->post(route('lawyer.register.submit'), $fields)->assertSessionHasNoErrors();
    $lawyer = Applicant::query()->where('email', $fields['email'])->sole();
    $manager = applicationStaff(['applicants.view', 'applicants.manage']);

    $this->actingAs($manager)->patch(route('lawyers.refuse', $lawyer->id), ['reason' => ''])->assertSessionHasErrors('reason');
    $this->actingAs($manager)->patch(route('lawyers.refuse', $lawyer->id), ['reason' => 'The licence has expired.'])->assertRedirect()->assertSessionHasNoErrors();

    $lawyer->refresh();
    expect($lawyer->lawyer_refused_at)->not->toBeNull()->and($lawyer->lawyer_refusal_reason)->toBe('The licence has expired.')
        ->and((bool) $lawyer->is_active)->toBeFalse()->and($lawyer->lawyer_verified_at)->toBeNull()
        ->and(applicationNotices($manager))->toBe([]);
    Notification::assertSentTo($lawyer, LawyerApplicationRefused::class,
        fn (LawyerApplicationRefused $notice) => collect($notice->toMail($lawyer)->introLines)->contains(fn (string $line) => str_contains($line, 'The licence has expired.')));

    $this->actingAs($manager)->get(route('lawyers.index'))->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('lawyers.data.0.refused', true)->where('lawyers.data.0.refusal_reason', 'The licence has expired.'));

    // A refusal is only for an application still waiting.
    $this->actingAs($manager)->patch(route('lawyers.refuse', $lawyer->id), ['reason' => 'Again.'])->assertRedirect();
    $this->actingAs($manager)->patch(route('lawyers.verify', $lawyer->id), ['verified' => true, 'license_number' => $fields['license_number']])->assertRedirect();
    $this->actingAs($manager)->patch(route('lawyers.refuse', $lawyer->id), ['reason' => 'Too late.'])->assertNotFound();
    expect($lawyer->fresh()->lawyer_refused_at)->toBeNull()->and((bool) $lawyer->fresh()->is_active)->toBeTrue();
});
