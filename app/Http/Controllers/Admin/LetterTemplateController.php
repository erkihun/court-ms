<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LetterTemplateController extends Controller
{
    public function index()
    {
        $templates = LetterTemplate::query()
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('admin.letter-templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new LetterTemplate();
        $caseTypes = \App\Models\CaseType::select('name', 'prifix')->orderBy('name')->get();

        return view('admin.letter-templates.create', compact('template', 'caseTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);
        $data = $this->handleUploads($request, new LetterTemplate(), $data);

        LetterTemplate::create($data);

        return redirect()->route('letter-templates.index')->with('success', 'Letter template created.');
    }

    public function edit(LetterTemplate $letterTemplate)
    {
        $caseTypes = \App\Models\CaseType::select('name', 'prifix')->orderBy('name')->get();

        return view('admin.letter-templates.edit', [
            'template' => $letterTemplate,
            'caseTypes' => $caseTypes
        ]);
    }

    public function update(Request $request, LetterTemplate $letterTemplate)
    {
        $data = $this->validateTemplate($request);
        $data = $this->handleUploads($request, $letterTemplate, $data);

        $letterTemplate->update($data);

        return redirect()->route('letter-templates.index')->with('success', 'Letter template updated.');
    }

    public function destroy(LetterTemplate $letterTemplate)
    {
        $letterTemplate->delete();

        return redirect()->route('letter-templates.index')->with('success', 'Letter template deleted.');
    }

    private function validateTemplate(Request $request): array
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'category'            => ['nullable', 'string', 'max:120'],
            'subject_prefix'      => ['nullable', 'string', 'max:80'],
            'placeholders'        => ['nullable', 'string', 'max:2000'],
            'body'                => ['required', 'string'],
            'header_image'        => ['nullable', 'image', 'max:3072'],
            'footer_image'        => ['nullable', 'image', 'max:3072'],
            'remove_header_image' => ['nullable', 'boolean'],
            'remove_footer_image' => ['nullable', 'boolean'],
        ]);

        $data['placeholders'] = $this->normalizePlaceholders($data['placeholders'] ?? null);
        $data['subject_prefix'] = $this->normalizePrefix($data['subject_prefix'] ?? null);

        return $data;
    }

    private function normalizePrefix(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizePlaceholders(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $parts = preg_split('/[\r\n,]+/', $raw);
        $parts = array_filter(array_map('trim', $parts));

        return empty($parts) ? null : array_values(array_unique($parts));
    }

    private function handleUploads(Request $request, LetterTemplate $template, array $data): array
    {
        if ($request->boolean('remove_header_image') && $template->header_image_path) {
            Storage::disk('public')->delete($template->header_image_path);
            $data['header_image_path'] = null;
        }

        if ($request->boolean('remove_footer_image') && $template->footer_image_path) {
            Storage::disk('public')->delete($template->footer_image_path);
            $data['footer_image_path'] = null;
        }

        if ($request->hasFile('header_image')) {
            if ($template->header_image_path) {
                Storage::disk('public')->delete($template->header_image_path);
            }
            $data['header_image_path'] = $request->file('header_image')->store('letter-templates/headers', 'public');
        }

        if ($request->hasFile('footer_image')) {
            if ($template->footer_image_path) {
                Storage::disk('public')->delete($template->footer_image_path);
            }
            $data['footer_image_path'] = $request->file('footer_image')->store('letter-templates/footers', 'public');
        }

        unset($data['header_image'], $data['footer_image'], $data['remove_header_image'], $data['remove_footer_image']);

        return $data;
    }
}
