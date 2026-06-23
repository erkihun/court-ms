<?php

namespace App\Support;

use App\Models\Decision;
use App\Models\DecisionTemplate;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DecisionPdf
{
    /**
     * Build the decision PDF using the default template layout. Stamps the
     * official seal when the decision is approved. Returns a dompdf instance.
     */
    public static function render(Decision $decision, ?DecisionTemplate $template = null)
    {
        $decision->loadMissing('courtCase.judge');

        $template = $template ?? DecisionTemplate::default();

        $body = $template
            ? self::fillPlaceholders($template->body, $decision)
            : (string) ($decision->decision_content ?? '');

        $sealPath = null;
        if ($decision->isApproved()) {
            $seal = SystemSetting::current()->seal_path ?? null;
            if ($seal && Storage::disk('public')->exists($seal)) {
                $sealPath = public_path('storage/' . $seal);
            }
        }

        return Pdf::loadView('pdf.decision-output', [
            'decision' => $decision,
            'template' => $template,
            'body'     => $body,
            'sealPath' => $sealPath,
        ])->setPaper('a4');
    }

    public static function filename(Decision $decision): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) ($decision->case_number ?: $decision->id));

        return 'decision-' . trim($safeNumber, '-') . '.pdf';
    }

    /**
     * Replace {{placeholder}} tokens in a template body with decision values.
     */
    public static function fillPlaceholders(string $body, Decision $decision): string
    {
        $panel = is_array($decision->panel_judges) ? array_values($decision->panel_judges) : [];

        $judgeName = static fn (array $panel, int $i): string => (string) ($panel[$i]['admin_user_name'] ?? '');
        $judgeVote = static fn (array $panel, int $i): string => (string) ($panel[$i]['vote'] ?? '');

        $replacements = [
            'applicant_name'     => (string) ($decision->applicant_full_name ?? ''),
            'respondent_name'    => (string) ($decision->respondent_full_name ?? ''),
            'case_number'        => (string) ($decision->case_number ?? ''),
            'case_file_number'   => (string) ($decision->case_file_number ?? ''),
            'judge_name'         => (string) ($decision->courtCase?->judge?->name ?? ''),
            'decision_date'      => EthiopianDate::format($decision->decision_date, fallback: ''),
            'decision_content'   => (string) ($decision->decision_content ?? ''),
            'decision_name'      => (string) ($decision->name ?? ''),
            'judge_one'          => $judgeName($panel, 0),
            'judge_two'          => $judgeName($panel, 1),
            'judge_three'        => $judgeName($panel, 2),
            'judge_one_vote'     => $judgeVote($panel, 0),
            'judge_two_vote'     => $judgeVote($panel, 1),
            'judge_three_vote'   => $judgeVote($panel, 2),
        ];

        foreach ($replacements as $key => $value) {
            $body = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/', $value, $body);
        }

        return $body;
    }
}
