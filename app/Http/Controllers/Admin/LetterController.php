<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Letter;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LetterController extends Controller
{
    public function index()
    {
        $letters = Letter::with(['template','author'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.letters.index', compact('letters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id'       => ['required', 'exists:letter_templates,id'],
            'recipient_name'    => ['required', 'string', 'max:255'],
            'recipient_title'   => ['nullable', 'string', 'max:255'],
            'recipient_company' => ['nullable', 'string', 'max:255'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'body'              => ['required', 'string'],
            'cc'                => ['nullable', 'string', 'max:255'],
        ]);

        $letter          = null;
        $template        = LetterTemplate::findOrFail($data['template_id']);
        DB::transaction(function () use ($request, $data, $template, &$letter) {
            $lockedTemplate = LetterTemplate::where('id', $template->id)->lockForUpdate()->first();
            $lockedTemplate->reference_sequence = ($lockedTemplate->reference_sequence ?? 0) + 1;
            $lockedTemplate->save();

            $baseSubject = trim($data['subject'] ?? $lockedTemplate->title);
            $subjectValue = $baseSubject === '' ? null : $baseSubject;

            $referenceParts = array_filter([
                $lockedTemplate->subject_prefix,
                str_pad($lockedTemplate->reference_sequence, 4, '0', STR_PAD_LEFT),
            ]);
            $referenceNumber = implode('/', $referenceParts);

            $letter = Letter::create([
                'letter_template_id' => $lockedTemplate->id,
                'user_id'            => $request->user()->id,
                'recipient_name'     => $data['recipient_name'],
                'recipient_title'    => $data['recipient_title'] ?? null,
                'recipient_company'  => $data['recipient_company'] ?? null,
                'subject'            => $subjectValue,
                'reference_number'   => $referenceNumber,
                'body'               => $data['body'],
                'cc'                 => $data['cc'] ?? null,
            ]);
        });

        return redirect()->route('letters.show', $letter)->with('success', 'Letter created.');
    }

    public function edit(Letter $letter)
    {
        $letter->load('template');

        return view('admin.letters.edit', compact('letter'));
    }

    public function update(Request $request, Letter $letter)
    {
        $data = $request->validate([
            'recipient_name'    => ['required', 'string', 'max:255'],
            'recipient_title'   => ['nullable', 'string', 'max:255'],
            'recipient_company' => ['nullable', 'string', 'max:255'],
            'subject'           => ['nullable', 'string', 'max:255'],
            'body'              => ['required', 'string'],
            'cc'                => ['nullable', 'string', 'max:255'],
        ]);

        $letter->load('template');

            $baseSubject = trim($data['subject'] ?? $letter->template->title);
            $subjectValue = $baseSubject === '' ? null : $baseSubject;

        $letter->update([
            'recipient_name'    => $data['recipient_name'],
            'recipient_title'   => $data['recipient_title'] ?? null,
            'recipient_company' => $data['recipient_company'] ?? null,
            'subject'           => $subjectValue,
            'body'              => $data['body'],
            'cc'                => $data['cc'] ?? null,
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter updated.');
    }

    public function destroy(Letter $letter)
    {
        $letter->delete();

        return redirect()->route('letters.index')->with('success', 'Letter deleted.');
    }

    public function show(Letter $letter)
    {
        $letter->load(['template','author']);

        return view('admin.letters.preview', [
            'letter'   => $letter,
            'template' => $letter->template,
        ]);
    }
}
