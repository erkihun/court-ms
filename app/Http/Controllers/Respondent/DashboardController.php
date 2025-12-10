<?php

namespace App\Http\Controllers\Respondent;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    public function index()
    {
        Session::put('acting_as_respondent', true);

        $applicant = Auth::guard('applicant')->user();
        abort_unless($applicant, 403);

        $respondent = $this->resolveActingRespondent($applicant);

        $stats = [
            'cases'        => DB::table('respondent_case_views')->where('respondent_id', $respondent->id)->count(),
            'responses'    => DB::table('respondent_responses')->where('respondent_id', $respondent->id)->count(),
            'notifications'=> $this->countUnseenNotifications($respondent->id),
        ];

        $recentCases = DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->select('c.case_number', 'c.title', 'c.status', 'v.viewed_at')
            ->where('v.respondent_id', $respondent->id)
            ->orderByDesc('v.viewed_at')
            ->limit(5)
            ->get();

        $letters = DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->join('letters as l', 'l.case_number', '=', 'c.case_number')
            ->leftJoin('letter_templates as lt', 'lt.id', '=', 'l.letter_template_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->where('v.respondent_id', $respondent->id)
            ->where('l.approval_status', 'approved')
            ->orderByDesc('l.created_at')
            ->limit(5)
            ->select(
                'l.id',
                'l.subject',
                'l.reference_number',
                'l.created_at',
                'c.case_number',
                'c.title as case_title',
                'lt.title as template_title',
                'u.name as author_name'
            )
            ->get();

        return view('applicant.respondent.dashboard', [
            'stats' => $stats,
            'recentCases' => $recentCases,
            'letters' => $letters,
        ]);
    }

    private function countUnseenNotifications(int $respondentId): int
    {
        return DB::table('respondent_case_views as v')
            ->where('v.respondent_id', $respondentId)
            ->whereNotExists(function ($q) use ($respondentId) {
                $q->from('respondent_notification_reads as r')
                    ->whereColumn('r.source_id', 'v.id')
                    ->where('r.type', 'respondent_view')
                    ->where('r.respondent_id', $respondentId);
            })
            ->count();
    }

    private function resolveActingRespondent($applicant): Respondent
    {
        $respondent = Respondent::where('email', $applicant->email)->first();
        if (!$respondent) {
            $phone = $applicant->phone ?? 'resp_' . substr(md5((string) microtime(true)), 0, 12);
            if (Respondent::where('phone', $phone)->where('email', '!=', $applicant->email)->exists()) {
                $phone = 'resp_' . substr(md5(uniqid('', true)), 0, 12);
            }

            $respondent = Respondent::create([
                'first_name'        => $applicant->first_name ?? '',
                'middle_name'       => $applicant->middle_name ?? '',
                'last_name'         => $applicant->last_name ?? '',
                'gender'            => $applicant->gender ?? null,
                'position'          => $applicant->position ?? '',
                'organization_name' => $applicant->organization_name ?? '',
                'address'           => $applicant->address ?? '',
                'national_id'       => $this->applicantNationalId($applicant),
                'phone'             => $phone,
                'email'             => $applicant->email,
                'password'          => $applicant->password,
            ]);
        } else {
            $dirty = false;
            $maybeNationalId = $this->applicantNationalId($applicant);
            if (!$respondent->national_id && $maybeNationalId) {
                $respondent->national_id = $maybeNationalId;
                $dirty = true;
            }
            if ($dirty) {
                $respondent->save();
            }
        }

        return $respondent;
    }

    private function applicantNationalId($applicant): ?string
    {
        $digits = preg_replace('/\D/', '', (string) ($applicant->getRawOriginal('national_id_number') ?? $applicant->national_id_number ?? ''));
        if ($digits === '') {
            return null;
        }
        return substr($digits, 0, 16);
    }
}
