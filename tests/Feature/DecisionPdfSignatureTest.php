<?php

declare(strict_types=1);

use App\Models\CaseType;
use App\Models\CourtCase;
use App\Models\Decision;
use App\Models\User;
use App\Support\DecisionPdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function decisionWithPanelJudge(User $judge, string $status = 'published', bool $approved = true): Decision
{
    $caseType = CaseType::query()->create(['name' => 'PDF signature test']);
    $courtCase = CourtCase::query()->create([
        'case_number' => 'PDF-'.Str::upper(Str::random(10)),
        'title' => 'PDF signature case',
        'case_type_id' => $caseType->id,
        'filing_date' => now()->toDateString(),
    ]);

    return Decision::query()->create([
        'court_case_id' => $courtCase->id,
        'case_number' => $courtCase->case_number,
        'decision_date' => now()->toDateString(),
        'panel_judges' => [[
            'order' => 1,
            'admin_user_id' => $judge->id,
            'admin_user_name' => $judge->name,
            'vote' => null,
        ]],
        'name' => 'PDF signature ruling',
        'decision_content' => '<p>Decision content.</p>',
        'status' => $status,
        'approved_at' => $approved ? now() : null,
        'approved_by' => $approved ? $judge->id : null,
    ]);
}

test('approved published decision embeds an available panel judge signature', function (): void {
    Storage::fake('public');
    $signaturePath = 'signatures/judge.png';
    Storage::disk('public')->put(
        $signaturePath,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
    );
    $judge = User::factory()->create(['signature_path' => $signaturePath]);
    $decision = decisionWithPanelJudge($judge);

    $pdf = DecisionPdf::render($decision)->output();

    expect(substr_count($pdf, '/Subtype /Image'))->toBeGreaterThanOrEqual(1);
});

test('unapproved decision does not embed judge signatures', function (): void {
    Storage::fake('public');
    $signaturePath = 'signatures/judge.png';
    Storage::disk('public')->put(
        $signaturePath,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
    );
    $judge = User::factory()->create(['signature_path' => $signaturePath]);
    $decision = decisionWithPanelJudge($judge, approved: false);

    $pdf = DecisionPdf::render($decision)->output();

    expect(substr_count($pdf, '/Subtype /Image'))->toBe(0);
});

test('missing signature file leaves the signature line empty', function (): void {
    Storage::fake('public');
    $judge = User::factory()->create([
        'signature_path' => 'signatures/missing.png',
    ]);
    $decision = decisionWithPanelJudge($judge);

    $pdf = DecisionPdf::render($decision)->output();

    expect(substr_count($pdf, '/Subtype /Image'))->toBe(0);
});
