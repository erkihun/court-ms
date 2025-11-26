<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Letter;
use Illuminate\Http\Request;

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

        $letter = Letter::create([
            'letter_template_id' => $data['template_id'],
            'user_id'            => $request->user()->id,
            'recipient_name'     => $data['recipient_name'],
            'recipient_title'    => $data['recipient_title'] ?? null,
            'recipient_company'  => $data['recipient_company'] ?? null,
            'subject'            => $data['subject'] ?? null,
            'body'               => $data['body'],
            'cc'                 => $data['cc'] ?? null,
        ]);

        return redirect()->route('letters.show', $letter)->with('success', 'Letter created.');
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
