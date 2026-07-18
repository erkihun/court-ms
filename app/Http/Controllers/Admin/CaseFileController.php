<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SecureUploadService;
use Illuminate\Http\Request;

class CaseFileController extends Controller
{
    public function store(Request $request, \App\Models\CourtCase $case, SecureUploadService $uploads)

    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:2048'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        // Store file
        $path = $uploads->store($request->file('file'), 'case-files', 'private', [
            'related_type' => 'court_case',
            'related_id' => $case->getKey(),
            'user_id' => auth()->id(),
        ]);

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
