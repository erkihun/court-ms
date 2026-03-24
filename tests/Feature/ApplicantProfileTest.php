<?php

use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;

test('applicant profile page is displayed', function () {
    $applicant = Applicant::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'phone' => '0911000000',
        'email' => 'abel@example.com',
        'address' => 'Addis Ababa',
        'national_id_number' => '1234567890123456',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);

    $this
        ->actingAs($applicant, 'applicant')
        ->get(route('applicant.profile.edit'))
        ->assertOk();
});

test('applicant can update profile with an empty middle name', function () {
    $applicant = Applicant::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'phone' => '0911000000',
        'email' => 'abel@example.com',
        'address' => 'Addis Ababa',
        'national_id_number' => '1234567890123456',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);

    $response = $this
        ->actingAs($applicant, 'applicant')
        ->from(route('applicant.profile.edit'))
        ->patch(route('applicant.profile.update'), [
            'first_name' => 'Abel',
            'middle_name' => '',
            'last_name' => 'Teka',
            'gender' => 'male',
            'position' => 'Senior Manager',
            'organization_name' => 'ACME',
            'phone' => '0911000000',
            'email' => 'abel@example.com',
            'address' => 'Addis Ababa',
            'national_id_number' => '1234 5678 9012 3456',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('applicant.profile.edit'));

    $applicant->refresh();

    expect($applicant->middle_name)->toBe('')
        ->and($applicant->gender)->toBe('male')
        ->and($applicant->position)->toBe('Senior Manager')
        ->and($applicant->getRawOriginal('national_id_number'))->toBe('1234567890123456');
});

test('applicant profile update validates duplicate national id after normalization', function () {
    $applicant = Applicant::create([
        'first_name' => 'Abel',
        'middle_name' => 'K',
        'last_name' => 'Teka',
        'gender' => 'male',
        'position' => 'Manager',
        'organization_name' => 'ACME',
        'phone' => '0911000000',
        'email' => 'abel@example.com',
        'address' => 'Addis Ababa',
        'national_id_number' => '1234567890123456',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);

    Applicant::create([
        'first_name' => 'Sara',
        'middle_name' => 'M',
        'last_name' => 'Bekele',
        'gender' => 'female',
        'position' => 'Counsel',
        'organization_name' => 'Beta PLC',
        'phone' => '0911000001',
        'email' => 'sara@example.com',
        'address' => 'Adama',
        'national_id_number' => '9999888877776666',
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => true,
    ]);

    $response = $this
        ->actingAs($applicant, 'applicant')
        ->from(route('applicant.profile.edit'))
        ->patch(route('applicant.profile.update'), [
            'first_name' => 'Abel',
            'middle_name' => 'K',
            'last_name' => 'Teka',
            'gender' => 'male',
            'position' => 'Manager',
            'organization_name' => 'ACME',
            'phone' => '0911000000',
            'email' => 'abel@example.com',
            'address' => 'Addis Ababa',
            'national_id_number' => '9999 8888 7777 6666',
        ]);

    $response
        ->assertSessionHasErrors('national_id_number')
        ->assertRedirect(route('applicant.profile.edit'));

    expect($applicant->fresh()->getRawOriginal('national_id_number'))->toBe('1234567890123456');
});
