<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\LetterTemplate;
use Illuminate\Http\Request;

class LetterComposerController extends Controller
{
    public function create(Request $request)
    {
        $templates = LetterTemplate::orderBy('title')->get();
        $selectedTemplate = null;
        $body = old('body');
        $subject = old('subject');
        $recipientName = old('recipient_name', '');
        $recipientTitle = old('recipient_title', '');
        $recipientCompany = old('recipient_company', '');
        $cc = old('cc', '');
        $sendToApplicant = filter_var(old('send_to_applicant', '1'), FILTER_VALIDATE_BOOLEAN);
        $sendToRespondent = filter_var(old('send_to_respondent', '1'), FILTER_VALIDATE_BOOLEAN);
        $caseNumber = null;

        if ($request->filled('template_id')) {
            $selectedTemplate = $templates->firstWhere('id', (int) $request->input('template_id'));
            if ($selectedTemplate && $body === null) {
                $body = $selectedTemplate->body;
            }
        }

        $body = $body ?? '';

        // Preload case number if coming from case view
        if ($request->filled('case_id')) {
            $caseNumber = \DB::table('court_cases')->where('id', (int) $request->input('case_id'))->value('case_number');
        }

        return view('admin.letters.compose', compact(
            'templates',
            'selectedTemplate',
            'body',
            'subject',
            'recipientName',
            'recipientTitle',
            'recipientCompany',
            'cc',
            'sendToApplicant',
            'sendToRespondent',
            'caseNumber'
        ));
    }
}
