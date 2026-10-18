<?php

declare(strict_types=1);

namespace App\Services\Party;

use App\Models\Applicant;
use App\Models\Respondent;
use Illuminate\Support\Str;

/**
 * Respondent mode runs on the applicant account; the matching Respondent
 * record (same email) carries respondent-side data such as case views and
 * responses. This finds it, creating it on first use.
 */
final class ActingRespondent
{
    public function resolve(Applicant $applicant): Respondent
    {
        $nationalId = $this->nationalId($applicant);
        $respondent = Respondent::query()->where('email', $applicant->email)->first();

        if ($respondent !== null) {
            if (! $respondent->national_id && $nationalId !== null) {
                $respondent->forceFill(['national_id' => $nationalId])->save();
            }

            return $respondent;
        }

        $phone = $applicant->phone ?: 'resp_'.Str::lower(Str::random(12));
        if (Respondent::query()->where('phone', $phone)->where('email', '!=', $applicant->email)->exists()) {
            $phone = 'resp_'.Str::lower(Str::random(12));
        }

        return Respondent::create([
            'first_name' => $applicant->first_name ?? '',
            'middle_name' => $applicant->middle_name ?? '',
            'last_name' => $applicant->last_name ?? '',
            'gender' => $applicant->gender,
            'position' => $applicant->position ?? '',
            'organization_name' => $applicant->organization_name ?? '',
            'address' => $applicant->address ?? '',
            'national_id' => $nationalId,
            'phone' => $phone,
            'email' => $applicant->email,
            // Never used to sign in (respondent mode uses the applicant session);
            // a profile password change copies the applicant's hash over.
            'password' => Str::random(40),
        ]);
    }

    /** Digits only, at most 16 (the column length). */
    private function nationalId(Applicant $applicant): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $applicant->getRawOriginal('national_id_number'));

        return $digits === '' ? null : substr($digits, 0, 16);
    }
}
