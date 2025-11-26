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

        if ($request->filled('template_id')) {
            $selectedTemplate = $templates->firstWhere('id', (int) $request->input('template_id'));
            if ($selectedTemplate && $body === null) {
                $body = $selectedTemplate->body;
            }
        }

        $body = $body ?? '';

        return view('admin.letters.compose', compact(
            'templates',
            'selectedTemplate',
            'body',
            'subject',
            'recipientName',
            'recipientTitle',
            'recipientCompany',
            'cc'
        ));
    }
}
