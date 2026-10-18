<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LetterComposerController extends Controller
{
    public function create(Request $request): Response
    {
        $templates = LetterTemplate::query()->orderBy('title')->get();
        $selectedId = $request->integer('template_id');

        // Preload case number if coming from case view
        $caseNumber = $request->filled('case_id')
            ? DB::table('court_cases')->where('id', $request->integer('case_id'))->value('case_number')
            : null;

        return Inertia::render('Admin/Letters/Compose', [
            'templates' => $templates->map(fn (LetterTemplate $template): array => [
                'id' => (int) $template->id,
                'title' => (string) $template->title,
                'category' => $template->category ?: null,
                'placeholders' => array_values($template->placeholders ?? []),
                'body' => (string) $template->body,
                'excerpt' => Str::limit(trim(strip_tags((string) $template->body)), 100, '...'),
            ])->values(),
            'selectedTemplateId' => $templates->contains('id', $selectedId) ? $selectedId : null,
            'caseNumber' => $caseNumber,
            'nextReference' => $caseNumber ? $this->nextReference($caseNumber) : null,
        ]);
    }

    /** The reference the next letter for this case will get; the final value is assigned on save. */
    private function nextReference(string $caseNumber): string
    {
        $last = DB::table('letters')
            ->where('case_number', $caseNumber)
            ->orderByDesc('id')
            ->value('reference_number');

        $nextSeq = $last && preg_match('/\/(\d{2})$/', $last, $m) ? (int) $m[1] + 1 : 1;

        return $caseNumber.'/'.str_pad((string) $nextSeq, 2, '0', STR_PAD_LEFT);
    }
}
