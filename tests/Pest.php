<?php

use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/** A verified, active applicant; `$tag` keeps email, phone and national ID unique. */
function partyApplicant(string $tag): Applicant
{
    $applicant = Applicant::create([
        'first_name' => 'Hana',
        'middle_name' => 'B',
        'last_name' => 'Party',
        'gender' => 'female',
        'position' => 'Officer',
        'organization_name' => 'Org',
        'phone' => '09'.substr(sprintf('%08d', crc32($tag)), 0, 8),
        'email' => "party-{$tag}@example.test",
        'address' => 'Addis Ababa',
        'national_id_number' => substr(sprintf('%016d', crc32($tag)).'0000000000000000', 0, 16),
        'password' => Hash::make('password'),
        'is_active' => true,
        'is_lawyer' => false,
    ]);
    $applicant->markEmailAsVerified();

    return $applicant;
}

/** A pending, awaiting-review case owned by `$applicant`. */
function partyCase(Applicant $applicant, string $number, array $overrides = []): int
{
    $typeId = DB::table('case_types')->insertGetId(['name' => 'Type '.$number, 'created_at' => now(), 'updated_at' => now()]);

    return DB::table('court_cases')->insertGetId([
        'applicant_id' => $applicant->id,
        'case_number' => $number,
        'title' => 'Case '.$number,
        'case_type_id' => $typeId,
        'filing_date' => now()->toDateString(),
        'status' => 'pending',
        'review_status' => 'awaiting_review',
        'created_at' => now(),
        'updated_at' => now(),
        ...$overrides,
    ]);
}
