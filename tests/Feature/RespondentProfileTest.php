<?php

use App\Models\Applicant;
use App\Models\Respondent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function respondentProfileApplicant(): Applicant
{
    $applicant = Applicant::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'phone' => '0911000100',
        'email' => 'respondent-profile@example.com',
        'address' => 'Addis Ababa',
        'national_id_number' => '1234567890123456',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);

    $applicant->markEmailAsVerified();

    Respondent::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'address' => 'Addis Ababa',
        'national_id' => '1234567890123456',
        'phone' => '0911000100',
        'email' => $applicant->email,
        'password' => Hash::make('password'),
    ]);

    return $applicant;
}

test('respondent profile exposes the same profile security and sessions navigation', function () {
    $applicant = respondentProfileApplicant();

    $this->actingAs($applicant, 'applicant')
        ->get(route('respondent.profile.edit'))
        ->assertRedirect(route('applicant.profile.edit'));
});

test('respondent profile updates linked profile records and password', function () {
    $applicant = respondentProfileApplicant();

    $this->actingAs($applicant, 'applicant')
        ->patch(route('respondent.profile.update'), [
            'first_name' => 'Abel',
            'middle_name' => 'K',
            'last_name' => 'Teka',
            'gender' => 'male',
            'position' => 'Senior Manager',
            'organization_name' => 'ACME',
            'address' => 'Adama',
            'national_id' => '1234567890123456',
            'phone' => '0911000100',
            'email' => $applicant->email,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(Respondent::where('email', $applicant->email)->value('address'))->toBe('Adama')
        ->and($applicant->fresh()->address)->toBe('Adama');

    $this->actingAs($applicant->fresh(), 'applicant')
        ->patch(route('respondent.profile.password'), [
            'current_password' => 'password',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])
        ->assertRedirect(route('applicant.profile.edit').'#security');

    expect(Hash::check('NewPassword1!', $applicant->fresh()->password))->toBeTrue()
        ->and(Hash::check('NewPassword1!', Respondent::where('email', $applicant->email)->firstOrFail()->password))->toBeTrue();
});

test('respondent can view and revoke another respondent session', function () {
    $applicant = respondentProfileApplicant();

    DB::table('sessions')->insert([
        'id' => 'respondent-other-session',
        'user_id' => $applicant->id,
        'ip_address' => '203.0.113.25',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/150.0.0.0',
        'payload' => base64_encode(serialize(['_auth_guard' => 'applicant'])),
        'last_activity' => now()->getTimestamp(),
    ]);

    $this->actingAs($applicant, 'applicant')
        ->get(route('respondent.profile.sessions.index'))
        ->assertRedirect(route('applicant.profile.sessions.index'));

    $this->actingAs($applicant, 'applicant')
        ->get(route('applicant.profile.sessions.index'))
        ->assertOk()
        ->assertSee('Windows')
        ->assertSee('Chrome 150');

    $this->actingAs($applicant, 'applicant')
        ->delete(route('respondent.profile.sessions.destroy', 'respondent-other-session'))
        ->assertRedirect();

    $this->assertDatabaseMissing('sessions', ['id' => 'respondent-other-session']);
});
