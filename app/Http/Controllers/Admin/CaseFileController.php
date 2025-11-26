<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class CaseFileController extends Controller
{
    public function store(Request $request, \App\Models\CourtCase $case)

    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:2048'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        // Store file
        $path = $request->file('file')->store('case-files', 'public');

        $case->files()->create([
            'label' => $validated['label'] ?? null,
            'path' => $path,
            'mime' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
            'user_id' => auth()->id(),
        ]);

        if ($request->ajax()) {
            $files = $case->files()->latest()->get();

            return response()->json([
                'html' => view('admin.cases.partials.files-section', [
                    'case' => $case,
                    'files' => $files,
                ])->render(),
                'message' => __('cases.files.upload_success'), // will show in flash area
            ]);
        }

        return redirect()
            ->route('cases.show', $case->id)
            ->with('success', __('cases.files.upload_success'));
    }
}
